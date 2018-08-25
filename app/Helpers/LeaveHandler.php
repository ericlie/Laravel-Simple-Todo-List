<?php

namespace App\Helpers;

use App\EmployeeLeave;
use App\Helpers\LeaveTypeEnum;
use App\Leave;
use Carbon\Carbon;

class LeaveHandler
{
    protected $today;

    public function __construct()
    {
        $this->today = today();
    }

    private function enum()
    {
        static $enum;
        if (! $enum) {
            $enum = new LeaveTypeEnum;
        }
        return $enum;
    }

    public function setToday(Carbon $date)
    {
        $this->today = $date;
        return $this;
    }

    public function handleRepetition()
    {
        $empLeaves = EmployeeLeave::join(
            'leave_types',
            'leave_types.id', '=', 'employee_leaves.leave_type_id'
        )
        ->select(
            [
                'employee_leaves.user_id',
                'employee_leaves.leave_type_id',
                'employee_leaves.effective_date',
                'employee_leaves.stop_value',
                'leave_types.name','leave_types.has_balance',
                'leave_types.repeat_type', 'leave_types.repeat_value',
                'leave_types.starting_type', 'leave_types.awarded_days',
                'leave_types.ended_type', 'leave_types.can_accumulate',
                'leave_types.is_compulsory',
            ]
        )
        ->where('leave_types.repeat_type', '<>', $this->enum()->pick('repeat', 'none'))
        ->get();

        foreach ($empLeaves as $empLeave) {
            $leaveData = [
                'user_id' => $empLeave->user_id,
                'leave_type_id' => $empLeave->leave_type_id,
                'status' => $this->enum()->pick('status', 'approve'),
                'add_days' => $empLeave->awarded_days,
                'less_days' => 0,
                'expiry_date' => null,
                'start_date' => null,
                'end_date' => null,
                'total_days' => 0,
                'total_working_days' => 0,
                'deductible_salary' => 0,
            ];

            if ($this->shouldStopRecurrence($empLeave)) {
                continue;
            }

            $date = $this->getNextExpiryDate($empLeave);
            if (! $date) {
                continue;
            }
            $leaveData['expiry_date'] = $date;

            $leave = new Leave($leaveData);
            $leave->save();
        }
    }

    public function getLatestExpiryDate(EmployeeLeave $empLeave):? Carbon
    {
        $effectiveDate = Carbon::parse($empLeave->effective_date);

        if (! $effectiveDate->lte($this->today)) {
            return null;
        }

        $leave = Leave::where('user_id', $empLeave->user_id)
            ->where('leave_type_id', $empLeave->leave_type_id)
            ->where('status', $this->enum()->pick('status', 'approve'))
            ->whereNull('start_date')
            ->whereNull('end_date')
            ->whereNotNull('expiry_date')
            ->where('add_days', '>', 0)
            ->select('expiry_date')
            ->latest()
            ->first();

        if (! $leave) {
            return $effectiveDate;
        }

        $expiryDate = Carbon::parse($leave->expiry_date);

        if (! $expiryDate->lte($this->today)) {
            return null;
        }

        return $expiryDate;
    }

    public function getNextExpiryDate(EmployeeLeave $empLeave)
    {
        $date = $this->getLatestExpiryDate($empLeave);

        if (! $date) {
            return null;
        }

        if ($empLeave->repeat_type === $this->enum()->pick('repeat', 'monthly')) {
            return $date->addMonth();
        }

        if ($empLeave->repeat_type === $this->enum()->pick('repeat', 'quarter')) {
            return $date->addMonths(3);
        }

        if ($empLeave->repeat_type === $this->enum()->pick('repeat', 'semester')) {
            return $date->addMonths(6);
        }

        if ($empLeave->repeat_type === $this->enum()->pick('repeat', 'yearly')) {
            return $date->addYear();
        }

        return null;
    }

    public function shouldStopRecurrence(EmployeeLeave $empLeave): bool
    {
        if ($empLeave->ended_type === $this->enum()->pick('ending', 'none')) {
            return false;
        }

        return $empLeave->stop_value === $this->getStopValue($empLeave);
    }


    public function getStopValue(EmployeeLeave $empLeave): ?string
    {
        if ($empLeave->ended_type === $this->enum()->pick('ending', 'specific_date')) {
            return $this->today->toDateString();
        }

        if ($empLeave->ended_type === $this->enum()->pick('ending', 'times')) {
            return (string) $this->countTotalRepeating($empLeave);
        }

        return null;
    }

    public function countTotalRepeating(EmployeeLeave $empLeave): int
    {
        return Leave::where('user_id', $empLeave->user_id)
            ->where('leave_type_id', $empLeave->leave_type_id)
            ->where('status', $this->enum()->pick('status', 'approve'))
            ->whereNull('start_date')
            ->whereNull('end_date')
            ->whereNotNull('expiry_date')
            ->where('add_days', '>', 0)
            ->select('expiry_date')
            ->count('id');
    }
}
