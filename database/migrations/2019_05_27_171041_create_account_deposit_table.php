<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateAccountDepositTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_deposit', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id')->unsigned();
            $table->string('source', 150);
            $table->date('operation_date');
            $table->date('liquidation_date');
            $table->uuid('tracking_id');
            $table->string('description', 150)->nullable();
            $table->decimal('amount', 30,2);
            $table->tinyInteger('state')->default(1);
            $table->timestamp('created_date')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->foreign('account_id')
                ->references('id')
                ->on('user_account')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_deposit');
    }
}
