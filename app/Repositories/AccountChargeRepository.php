<?php

namespace App\Repositories;

use App\AccountCharge;
use App\Repositories\Interfaces\AccountChargeRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Dictionaries\AccountChargeState;


class AccountChargeRepository implements AccountChargeRepositoryInterface
{
    protected $model;

    public function __construct(AccountCharge $model)
    {
        $this->model = $model;
    }

    public function approve(int $id): Model
    {
        return $this->update([
            'state' => AccountChargeState::APPROVED
        ], $id);
    }

    public function rejected(int $id): Model
    {
        return $this->update([
            'state' => AccountChargeState::REJECTED
        ], $id);
    }

    public function cancel(int $id): Model
    {
        return $this->update([
            'state' => AccountChargeState::CANCEL
        ], $id);
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
        if (null == $deposit = $this->model->find($id)) {
            throw new ModelNotFoundException("Deposit not found.");
        }

        return $deposit;
    }

    public function findByAccount(int $accountId): Collection
    {
        return $this->model->where('source_account_id', $accountId)->get();
    }
}
