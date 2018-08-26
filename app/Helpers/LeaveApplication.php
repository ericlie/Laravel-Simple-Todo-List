<?php

namespace App\Helpers;

use App\EmployeeLeave;
use App\Helpers\HandleExpiryDate;
use App\Helpers\LeaveTypeEnum;
use App\Leave;
use App\LeaveType;
use App\User;
use Carbon\Carbon;

/**
 * Leave Application
 */
class LeaveApplication
{
    use HandleExpiryDate;

    protected $startDate;
    protected $endDate;
    protected $enum;
    protected $user;
    protected $leaveType;

    public function __construct(User $user, LeaveType $leaveType)
    {
        $this->user = $user;
        $this->leaveType = $leaveType;
        $this->enum = new LeaveTypeEnum;
    }

    public function setStartDate(Carbon $date)
    {
        $this->startDate = $date;
        return $this;
    }

    public function setEndDate(Carbon $date)
    {
        $this->endDate = $date;
        return $this;
    }

    public function canApply(EmployeeLeave $empLeave): bool
    {
        if ($this->hasExceedLimit($empLeave)) {
            return false;
        }

        // if ($this->hasExceedMaxDay($empLeave)) {
        //     return false;
        // }
        return true;
    }

    public function hasExceedLimit(EmployeeLeave $empLeave): bool
    {
        if ($empLeave->limit_type === $this->enum->pick('limit_type', 'none')) {
            return false;
        }

        $startDate = $this->startDate->copy();
        $limitDate = false;

        if ($empLeave->limit_type === $this->enum->pick('limit_type', 'times_per_week')) {
            $startDate = $startDate->startOfWeek();
            $endDate = $startDate->copy()->endOfWeek();
            $limitDate = true;
        }

        if ($empLeave->limit_type === $this->enum->pick('limit_type', 'times_per_month')) {
            $startDate = $startDate->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $limitDate = true;
        }

        if ($empLeave->limit_type === $this->enum->pick('limit_type', 'times_per_quarter')) {
            $startDate = $startDate->firstOfQuarter();
            $endDate = $startDate->copy()->endOfQuarter();
            $limitDate = true;
        }

        if ($empLeave->limit_type === $this->enum->pick('limit_type', 'times_per_semester')) {
            $startDate = $startDate->firstOfQuarter();
            if ($startDate->month !== 1 || $startDate->month !== 7) {
                $startDate->subQuarter();
            }
            $endDate = $startDate->copy()->addQuarter()->endOfQuarter();
            $limitDate = true;
        }

        if ($empLeave->limit_type === $this->enum->pick('limit_type', 'times_per_year')) {
            $startDate = $startDate->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
            $limitDate = true;
        }

        if ($empLeave->limit_type === $this->enum->pick('limit_type', 'times')) {
            $limitDate = false;
        }

        $leave = Leave::where('user_id', $empLeave->user_id)
            ->where('leave_type_id', $empLeave->leave_type_id)
            ->where('status', $this->enum->pick('status', 'approve'))
            ->when($limitDate, function ($query) {
                return $query->whereDate('start_date', '<=', $endDate)
                ->whereDate('end_date', '>=', $startDate);
            })
            ->whereNull('expiry_date')
            ->where('add_days', 0)
            ->count('id');

        if (!$leave) {
            return false;
        }

        return $empLeave->limit >= $leave;
    }

    public function process()
    {
        //
    }
}
