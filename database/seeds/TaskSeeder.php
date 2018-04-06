<?php

use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Task::class, 30)
            ->make()
            ->each(function (\App\Task $task) {
                $task->user()->associate(\App\User::inRandomOrder()->first());
                $task->save();
            });
    }
}
