<?php

use App\Helpers\LeaveTypeEnum;
use App\LeaveType;
use Faker\Generator as Faker;

if (! function_exists('initEnum')) {
    function initEnum()
    {
        static $enum;
        if (! $enum) {
            $enum = new LeaveTypeEnum;
        }
        return $enum;
    }

    function getEnum($key)
    {
        return initEnum()->get($key);
    }

    function pickEnum($key, $attribute)
    {
        return initEnum()->pick($key, $attribute);
    }
}

$factory->define(LeaveType::class, function (Faker $faker) {
    return [
        'name',
        'has_balance' => false,
        'repeat_type' => pickEnum('repeat', 'none'),
        'starting_type' => pickEnum('starting', 'none'),
        'awarded_days' => 0,
        'ended_type' => pickEnum('ending', 'none'),
        'can_accumulate' => false,
        'is_paid_leave' => false,
        'paid_type' => pickEnum('paid_type', 'none'),
        'limit' => 0,
        'limit_type' => pickEnum('limit_type', 'none'),
        'max_days' => 0,
        'max_type' => pickEnum('max_type', 'none'),
        'is_compulsory' => true,
    ];
});
