<?php

namespace App\Repositories;

use App\Repositories\Interfaces\UserAccountRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use App\UserAccount;

class UserAccountRepository implements UserAccountRepositoryInterface
{
    protected $model;

    public function __construct(UserAccount $model)
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
        if (null == $account = $this->model->find($id)) {
            throw new ModelNotFoundException("User account not found.");
        }

        return $account;
    }

    public function deactivate(int $id): Model
    {
        return $this->update(['status' => 0], $id);
    }

    public function findByUserId(int $id): Collection
    {
        return $this->model->where('user_id', $id)->get();
    }

    public function with($relations)
    {
        return $this->model->with($relations);
    }
}
