<?php


namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;


interface UserAccountRepositoryInterface extends RepositoryInterface
{
    public function deactivate(int $id): Model;
}
