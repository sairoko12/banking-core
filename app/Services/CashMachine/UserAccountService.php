<?php

namespace App\Services\CashMachine;

use App\Repositories\UserAccountRepository;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Services\CashMachine\Exceptions\ServiceException;

use App\Repositories\Dictionaries\{
    AccountChargeState,
    AccountDepositState,
    AccountType,
    ChargeType
};

use Illuminate\Database\Eloquent\{
    ModelNotFoundException,
    Collection
};

use App\UserAccount;

class UserAccountService
{
    private $repository;
    private $depositsService;
    private $chargesService;
    private $creditService;

    public function __construct(UserAccountRepository $repository,
                                AccountDepositService $depositService,
                                AccountChargeService $chargeService,
                                AccountCreditService $creditService)
    {
        $this->repository = $repository;
        $this->depositsService = $depositService;
        $this->chargesService = $chargeService;
        $this->creditService = $creditService;
    }

    public function getAccounts(int $userId): Collection
    {
        return $this->repository->findByUserId($userId);
    }

    public function get(int $accountId): UserAccount
    {
        return $this->repository->find($accountId);
    }

    public function getAccount(int $userId, int $accountId): UserAccount
    {
        if ($this->isOwner($accountId, $userId)) {
            return $this->get($accountId);
        }

        throw new ModelNotFoundException("Account not found for this user.");
    }

    public function update(int $userId, int $accountId, array $data): UserAccount
    {
        if (!$this->isOwner($accountId, $userId)) {
            throw new ModelNotFoundException("Account not found for this user.");
        }

        if ($data['account_type'] == AccountType::CREDIT) {
            $validateExtraData = \Validator::make($data, [
                'credit_line' => 'numeric'
            ]);

            if ($validateExtraData->fails()) {
                throw new ValidationException($validateExtraData);
            }

            if (!empty($data['credit_line'])) {
                $creditLine = (float) $data['credit_line'];
                unset($data['credit_line']);
                $this->creditService->updateCreditLine($accountId, $creditLine);
            }
        }

        if (is_null($data['account_type'])) {
            unset($data['account_type']);
        }

        if (is_null($data['alias'])) {
            unset($data['alias']);
        }

        return $this->repository->update($data, $accountId);
    }

    public function add(array $data): UserAccount
    {
        if ($data['account_type'] == AccountType::CREDIT) {
            $validateExtraData = \Validator::make($data, [
                'credit_line' => 'required|numeric'
            ], [
                'credit_line.required' => "For credit accounts credit_line is required."
            ]);

            if ($validateExtraData->fails()) {
                throw new ValidationException($validateExtraData);
            }

            $creditLine = (float) $data['credit_line'];
            unset($data['credit_line']);
            $account = $this->repository->create($data);

            $this->creditService->add($account->id, $creditLine);

            return $account;
        }

        return $this->repository->create($data);
    }

    public function getBalance(int $accountId): float
    {
        $deposits = $this->depositsService
            ->getByAccount($accountId, AccountDepositState::APPROVED)
            ->sum('amount');

        $charges = $this->chargesService
            ->getByAccount($accountId, AccountDepositState::APPROVED)
            ->sum('amount');

        $account = $this->get($accountId);

        if ($account->account_type == AccountType::DEBIT) {
            return floatval($deposits) - floatval($charges);
        } elseif ($account->account_type == AccountType::CREDIT) {
            return floatval($charges) - floatval($deposits);
        }
    }

    public function creditPay(int $userId, int $accountId, float $amount, string $description)
    {

    }

    public function withdraw(int $userId,
                             int $accountId,
                             float $amount,
                             string $description = null): array
    {
        if (!$this->isOwner($accountId, $userId)) {
            throw new ModelNotFoundException("Account not found for this user.");
        }

        $account = $this->repository->find($accountId);
        $balance = $this->getBalance($account->id);
        $now = Carbon::now()->format("Y-m-d");

        if ($account->account_type == AccountType::DEBIT) {
            if (!$this->chargesService->canAdd($account->id, $amount)) {
                throw new ServiceException("Your current balance is less that amount requested.", 400);
            }

            $charge = $this->chargesService->add($account->id,
                ChargeType::WITHDRAW['id'],
                $now,
                $now,
                (is_null($description) ? "Withdraw of date: {$now}" : $description),
                $amount);

            $this->chargesService->approve($charge->id);

            return [
                'operation_id' => $charge->id,
                'balance' => $balance - $amount,
                'operation_date' => $charge->operation_date
            ];
        } elseif ($account->account_type == AccountType::CREDIT) {
            if ($this->creditService->isDelayed($account->id)) {
                throw new ServiceException("Your credit is overdue with the payments.", 400);
            }

            $fee = (float) env('FEE_WITHDRAWAL', 10);
            $feePercentage = ($fee / 100) * $amount;
            $totalAmountRequested = $amount + $feePercentage;
            $newBalance = $balance + $totalAmountRequested;

            if (!$this->chargesService->canAdd($account->id, $newBalance)) {
                throw new ServiceException("Your line of credit is overcharged.", 400);
            }

            $charge = $this->chargesService->add($account->id,
                ChargeType::WITHDRAW['id'],
                $now,
                $now,
                (is_null($description) ? "Withdraw of date: {$now}" : $description),
                $amount);

            $feeCharge = $this->chargesService->add($account->id,
                ChargeType::FEE['id'],
                $now,
                $now,
                "fee withdrawal with ID: {$charge->id} of {$fee}%",
                $feePercentage);

            $this->chargesService->approve($charge->id);
            $this->chargesService->approve($feeCharge->id);

            return [
                'operation_id' => $charge->id,
                'operation_date' => $charge->operation_date,
                'fee' => $fee,
                'total_of_fee' => $feePercentage,
                'balance_due' => $newBalance
            ];
        }
    }

    public function remove(int $userId, int $accountId): bool
    {
        if (!$this->isOwner($accountId, $userId)) {
            throw new ModelNotFoundException("Account not found for this user.");
        }

        return $this->repository->delete($accountId);
    }

    public function isOwner(int $accountId, int $userId): bool
    {
        $accounts = $this->getAccounts($userId);
        return $accounts->contains($accountId);
    }
}
