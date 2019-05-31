<?php


namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;


interface AccountChargeRepositoryInterface extends AccountMoveInterface
{
    public function cancel(int $id): Model;
}
