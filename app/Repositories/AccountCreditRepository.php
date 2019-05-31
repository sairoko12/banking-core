<?php

namespace App\Repositories;

use App\Repositories\Interfaces\AccountCreditRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use App\AccountCredit;


class AccountCreditRepository implements AccountCreditRepositoryInterface
{
    protected $model;

    public function __construct(AccountCredit $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        $this->model->all();
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(array $data, int $id): Model
    {
        $this->model->where('id', $id)->update($data);

        return $this->find($id);
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }

    public function find(int $id): Model
    {
        if (null == $accountCredit = $this->model->find($id)) {
            throw new ModelNotFoundException("Account credit not found.");
        }

        return $accountCredit;
    }

    public function findByAccount(int $accountId): Collection
    {
        return $this->model->where('account_id', $accountId)->get();
    }
}
