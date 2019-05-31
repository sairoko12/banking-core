<?php

namespace App\Services\CashMachine;


use App\Repositories\AccountChargeRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Services\CashMachine\Exceptions\ServiceException;
use App\AccountCharge;

use App\Repositories\Dictionaries\{AccountChargeState, AccountType, ChargeType};

class AccountChargeService
{
    private $repository;

    public function __construct(AccountChargeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getByAccount(int $accountId, int $state = AccountChargeState::PENDING): Collection
    {
        return $this->repository->findByAccount($accountId)->where('state', $state);
    }

    public function approve(int $id): AccountCharge
    {
        return $this->repository->approve($id);
    }

    public function setState(int $id, string $state): AccountCharge
    {
        if ($state == 'approved') {
            return $this->repository->approve($id);
        } elseif ($state == 'rejected') {
            return $this->repository->rejected($id);
        } elseif ($state == 'cancel') {
            return $this->repository->cancel($id);
        }

        throw new ServiceException("Undefined deposit state");
    }

    public function addPayment(int $accountId, float $amount, string $description = null): AccountCharge
    {
        $accountService = app()->make('App\Services\CashMachine\UserAccountService');
        $account = $accountService->get($accountId);

        if ($account->account_type != AccountType::CREDIT) {
            throw new ServiceException("Account is not of credit type.", 400);
        } elseif (!$this->canAdd($account->id, $amount)) {
            throw new ServiceException("Your line of credit is overcharged.", 400);
        }

        $now = Carbon::now()->format("Y-m-d");

        return $this->add($account->id,
            ChargeType::CHARGE['id'],
            $now,
            $now,
            (is_null($description) ? "Payment of date: {$now}" : $description),
            $amount);
    }

    public function canAdd(int $accountId, float $amount): bool
    {
        $accountService = app()->make('App\Services\CashMachine\UserAccountService');
        $account = $accountService->get($accountId);
        $balance = $accountService->getBalance($account->id);

        if ($account->account_type == AccountType::DEBIT) {
            if ($balance < $amount) {
                return false;
            }
        } elseif ($account->account_type == AccountType::CREDIT) {
            $creditService = app()->make('App\Services\CashMachine\AccountCreditService');

            if ($creditService->isDelayed($account->id)) {
                return false;
            }

            $creditLine = $creditService->getCreditLine($account->id);
            $newBalance = $balance + $amount;

            if ($newBalance > $creditLine) {
                return false;
            }
        }

        return true;
    }

    public function add(int $accountId,
                        int $type,
                        string $operationDate,
                        string $liquidationDate,
                        string $description,
                        float $amount): AccountCharge
    {
        return $this->repository->create([
            'source_account_id' => $accountId,
            'type_id' => $type,
            'operation_date' => $operationDate,
            'liquidation_date' => $liquidationDate,
            'description' => $description,
            'amount' => $amount
        ]);
    }
}
