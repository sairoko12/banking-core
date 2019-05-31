<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateAccountCreditTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_credit', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id')->unsigned();
            $table->decimal('credit_line', 30, 2);
            $table->tinyInteger('as_delayed')->default(0);
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
        Schema::dropIfExists('account_credit');
    }
}
