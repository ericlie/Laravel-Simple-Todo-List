<?php

namespace App\Helpers;

use App\EmployeeLeave;
use App\Helpers\LeaveTypeEnum;
use App\Leave;
use Carbon\Carbon;

trait HandleExpiryDate
{

    public function getLatestExpiryDate(EmployeeLeave $empLeave, LeaveTypeEnum $enum):? Carbon
    {
        $effectiveDate = Carbon::parse($empLeave->effective_date);

        if ($effectiveDate->gte($this->today)) {
            return null;
        }

        $leave = Leave::where('user_id', $empLeave->user_id)
            ->where('leave_type_id', $empLeave->leave_type_id)
            ->where('status', $enum->pick('status', 'approve'))
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

    public function getNextExpiryDate(int $repeatType, Carbon $date, LeaveTypeEnum $enum): ?Carbon
    {
        return $this->getDateByRepeatType($repeatType, $date, $enum, 'add');
    }

    public function getPreviousExpiryDate(int $repeatType, Carbon $date, LeaveTypeEnum $enum): ?Carbon
    {
        return $this->getDateByRepeatType($repeatType, $date, $enum, 'sub');
    }

    /**
     * @param  int           $repeatType Repeat Type
     * @param  Carbon        $date       given date
     * @param  LeaveTypeEnum $enum       the Enum helper
     * @param  string        $type       add or sub
     * @return Carbon|null
     */
    private function getDateByRepeatType(
        int $repeatType,
        Carbon $date,
        LeaveTypeEnum $enum,
        string $type = 'add'
    ): ?Carbon {
        if ($repeatType === $enum->pick('repeat', 'monthly')) {
            return $date->copy()->{$type.'Month'}();
        }

        if ($repeatType === $enum->pick('repeat', 'quarter')) {
            return $date->copy()->{$type.'Quarter'}();
        }

        if ($repeatType === $enum->pick('repeat', 'semester')) {
            return $date->copy()->{$type.'Months'}(6);
        }

        if ($repeatType === $enum->pick('repeat', 'yearly')) {
            return $date->copy()->{$type.'Year'}();
        }

        return null;
    }
}
