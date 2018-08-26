<?php

namespace Tests;

use App\EmployeeLeave;
use App\Helpers\LeaveTypeEnum;
use App\LeaveType;
use App\User;
use Carbon\Carbon;

trait LeaveHelperTrait
{

    private function enum()
    {
        static $enum;
        if (! $enum) {
            $enum = new LeaveTypeEnum;
        }
        return $enum;
    }

    /**
     * Creating Leave
     *
     * @param array $options
     *
     * @return array
     */
    private function leaveFactory(array $options = []): array
    {
        $attributes = [
            'name' => 'Some Leave',
            'has_balance' => false,
            'repeat_type' => $this->enum()->pick('repeat', 'none'),
            'starting_type' => $this->enum()->pick('starting', 'none'),
            'awarded_days' => 10,
            'ended_type' => $this->enum()->pick('ending', 'none'),
            'can_accumulate' => false,
            'is_paid_leave' => false,
            'paid_type' => $this->enum()->pick('paid_type', 'none'),
            'limit' => 0,
            'limit_type' => $this->enum()->pick('limit_type', 'none'),
            'max_days' => 0,
            'max_type' => $this->enum()->pick('max_type', 'none'),
            'is_compulsory' => true,
        ];
        $leaveType = new LeaveType(array_merge($attributes, $options));
        $leaveType->save();
        $user = factory(User::class)->create();

        return [$leaveType, $user];
    }

    private function createEmployeeLeave(
        LeaveType $leaveType,
        User $user,
        Carbon $effectiveDate,
        $stopValue = null
    ) {

        $attributes = [
            'effective_date' => $effectiveDate,
            'stop_value' => $stopValue,
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
        ];
        $empLeave = new EmployeeLeave($attributes);
         $empLeave->save();

         return $empLeave;
    }
}
