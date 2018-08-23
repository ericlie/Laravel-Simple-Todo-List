<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeavesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
            $table->integer('leave_type_id')->unsigned();
            $table->foreign('leave_type_id')
                ->references('id')
                ->on('leave_types');
            $table->integer('status')->unsigned()->default(0);
            $table->integer('add_days')->unsigned()->default(0);
            $table->integer('less_days')->unsigned()->default(0);
            $table->date('expiry_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('total_days')->unsigned()->default(0);
            $table->integer('total_working_days')->unsigned()->default(0);
            $table->decimal('deductible_salary', 24, 2)->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leaves');
    }
}
