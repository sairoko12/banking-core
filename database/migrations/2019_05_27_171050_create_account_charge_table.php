<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateAccountChargeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_charge', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('source_account_id')->unsigned();
            $table->integer('type_id')->unsigned();
            $table->date('operation_date');
            $table->date('liquidation_date');
            $table->string('description', 150)->nullable();
            $table->decimal('amount', 30,2);
            $table->tinyInteger('state')->default(1);
            $table->timestamp('created_date')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->foreign('source_account_id')
                ->references('id')
                ->on('user_account')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('type_id')
                ->references('id')
                ->on('lu_charge');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_charge');
    }
}
