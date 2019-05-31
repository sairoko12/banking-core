<?php


namespace App\Services\CashMachine;

use App\Repositories\AccountCreditRepository;
use App\AccountCredit;


class AccountCreditService
{
    private $repository;

    public function __construct(AccountCreditRepository $repository)
    {
        $this->repository = $repository;
    }

    public function add(int $accountId, float $creditLine): AccountCredit
    {
        return $this->repository->create([
            'account_id' => $accountId,
            'credit_line' => $creditLine
        ]);
    }

    public function updateCreditLine(int $accountId, float $creditLine): AccountCredit
    {
        return $this->repository->update([
            'account_id' => $accountId,
            'credit_line' => $creditLine
        ]);
    }

    public function get(int $accountId): AccountCredit
    {
        return $this->repository->find($accountId);
    }

    public function getCreditLine(int $accountId): float
    {
        return (float) $this->get($accountId)->only('credit_line')['credit_line'];
    }

    public function isDelayed(int $accountId): bool
    {
        return boolval($this->get($accountId)->only('as_delayed')['as_delayed']);
    }
}
