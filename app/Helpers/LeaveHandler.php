<?php

namespace App\Helpers;

use App\EmployeeLeave;
use App\Helpers\HandleExpiryDate;
use App\Helpers\LeaveTypeEnum;
use App\Leave;
use Carbon\Carbon;

class LeaveHandler
{
    use HandleExpiryDate;

    protected $today;
    protected $enum;

    public function __construct()
    {
        $this->today = today();
        $this->enum = new LeaveTypeEnum;
    }

    public function setToday(Carbon $date)
    {
        $this->today = $date;
        return $this;
    }

    public function handleRepetition()
    {
        $empLeaves = EmployeeLeave::buildHandler()
        ->where('leave_types.repeat_type', '<>', $this->enum->pick('repeat', 'none'))
        ->get();

        foreach ($empLeaves as $empLeave) {
            $leaveData = [
                'user_id' => $empLeave->user_id,
                'leave_type_id' => $empLeave->leave_type_id,
                'status' => $this->enum->pick('status', 'approve'),
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

            $latestExpiryDate = $this->getLatestExpiryDate($empLeave, $this->enum);

            if (!$latestExpiryDate) {
                continue;
            }

            $nextExpirydate = $this->getNextExpiryDate($empLeave->repeat_type, $latestExpiryDate, $this->enum);

            if (! $nextExpirydate) {
                continue;
            }

            $leaveData['expiry_date'] = $nextExpirydate;

            if ($empLeave->can_accumulate) {
                $leaveData['add_days'] = $this->getAccumulatedDays($empLeave, $latestExpiryDate, $nextExpirydate);
            }

            $leave = new Leave($leaveData);
            $leave->save();
        }
    }
    public function shouldStopRecurrence(EmployeeLeave $empLeave): bool
    {
        if ($empLeave->ended_type === $this->enum->pick('ending', 'none')) {
            return false;
        }

        return $empLeave->stop_value === $this->getStopValue($empLeave);
    }

    public function getStopValue(EmployeeLeave $empLeave): string
    {
        if ($empLeave->ended_type === $this->enum->pick('ending', 'specific_date')) {
            return $this->today->toDateString();
        }

        if ($empLeave->ended_type === $this->enum->pick('ending', 'times')) {
            return (string) $this->countTotalRepeating($empLeave);
        }

        return '';
    }

    public function countTotalRepeating(EmployeeLeave $empLeave): int
    {
        return Leave::where('user_id', $empLeave->user_id)
            ->where('leave_type_id', $empLeave->leave_type_id)
            ->where('status', $this->enum->pick('status', 'approve'))
            ->whereNull('start_date')
            ->whereNull('end_date')
            ->whereNotNull('expiry_date')
            ->where('add_days', '>', 0)
            ->select('expiry_date')
            ->count('id');
    }

    public function getAccumulatedDays(EmployeeLeave $empLeave, Carbon $previousExpiryDate, Carbon $nextExpirydate): int
    {
        $leave = Leave::where('user_id', $empLeave->user_id)
            ->where('leave_type_id', $empLeave->leave_type_id)
            ->where('status', $this->enum->pick('status', 'approve'))
            ->whereNull('start_date')
            ->whereNull('end_date')
            ->whereDate('expiry_date', $previousExpiryDate)
            ->where('add_days', '>', 0)
            ->select('add_days')
            ->latest()
            ->first();

        if (! $leave) {
            return $empLeave->awarded_days;
        }

        $totalDays = Leave::where('user_id', $empLeave->user_id)
            ->where('leave_type_id', $empLeave->leave_type_id)
            ->where('status', $this->enum->pick('status', 'approve'))
            ->whereNull('expiry_date')
            ->whereDate('start_date', '<=', $nextExpirydate)
            ->whereDate('end_date', '>=', $previousExpiryDate)
            ->where('add_days', 0)
            ->where('total_days', '>', 0)
            ->sum('total_days');

        return $empLeave->awarded_days + ($leave->add_days - $totalDays);
    }
}
