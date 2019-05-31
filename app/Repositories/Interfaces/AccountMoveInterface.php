<?php


namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;


interface AccountMoveInterface extends RepositoryInterface
{
    public function approve(int $id): Model;

    public function rejected(int $id): Model;
}
