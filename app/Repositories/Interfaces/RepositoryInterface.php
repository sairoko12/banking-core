<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;


interface RepositoryInterface
{
    public function all(): Collection;

    public function create(array $data): Model;

    public function update(array $data, int $id): Model;

    public function delete(int $id): bool;

    public function find(int $id): Model;
}
