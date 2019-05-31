<?php

namespace App\Services\CashMachine;

use App\Repositories\AccountDepositRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Services\CashMachine\Exceptions\ServiceException;
use App\AccountDeposit;

use App\Repositories\Dictionaries\{
    AccountDepositState,
    SourceTypeDeposit,
    AccountType
};


class AccountDepositService
{
    private $repository;

    public function __construct(AccountDepositRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getByAccount(int $accountId, int $state = AccountDepositState::PENDING): Collection
    {
        return $this->repository->findByAccount($accountId)->where('state', $state);
    }

    public function setState(int $id, string $state): AccountDeposit
    {
        if ($state == 'approved') {
            return $this->repository->approve($id);
        } elseif ($state == 'rejected') {
            return $this->repository->rejected($id);
        }

        throw new ServiceException("Undefined deposit state");
    }

    public function add(int $accountId,
                        string $source,
                        string $operationDate,
                        string $liquidationDate,
                        string $description,
                        float $amount): AccountDeposit
    {
        $accountService = app()->make('App\Services\CashMachine\UserAccountService');
        $account = $accountService->get($accountId);

        if ($account->account_type == AccountType::DEBIT && $source == SourceTypeDeposit::CREDIT_PAYMENT) {
            throw new ServiceException("Invalid source for debit account.", 400);
        } elseif ($account->account_type == AccountType::CREDIT && $source != SourceTypeDeposit::CREDIT_PAYMENT) {
            throw new ServiceException("Invalid source for credit account.", 400);
        } elseif ($account->account_type == AccountType::CREDIT && $source == SourceTypeDeposit::CREDIT_PAYMENT) {
            $creditBalance = $accountService->getBalance($account->id);

            if ($creditBalance <= 0) {
                throw new ServiceException("No payment is required at this time.", 400);
            }
        }

        return $this->repository->create([
            'account_id' => $accountId,
            'source' => $source,
            'operation_date' => $operationDate,
            'liquidation_date' => $liquidationDate,
            'description' => $description,
            'amount' => $amount
        ]);
    }
}
