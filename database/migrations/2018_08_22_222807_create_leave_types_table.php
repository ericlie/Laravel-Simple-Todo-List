<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
            $table->boolean('has_balance')->default(false);
            $table->integer('repeat_type')->unsigned()->default(0);
            $table->integer('repeat_value')->unsigned()->default(0);
            $table->integer('starting_type')->unsigned()->default(0);
            $table->integer('awarded_days')->unsigned()->default(0);
            $table->integer('ended_type')->unsigned()->default(0);
            $table->boolean('can_accumulate')->default(false);
            $table->boolean('is_paid_leave')->default(false);
            $table->boolean('is_compulsory')->default(false);
            $table->integer('paid_type')->unsigned()->default(0);
            $table->integer('limit')->unsigned()->default(0);
            $table->integer('limit_type')->unsigned()->default(0);
            $table->integer('max_days')->unsigned()->default(0);
            $table->integer('max_type')->unsinged()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leave_types');
    }
}
