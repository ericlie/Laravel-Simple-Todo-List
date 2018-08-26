<?php

namespace App\Helpers;

use App\Helpers\HasEnumMutator;

class LeaveTypeEnum
{
    use HasEnumMutator;

    /**
     * Get Repeat Enum
     *
     * @return array
     */
    protected function getRepeatEnum(): array
    {
        return array_merge(
            $this->getCommonEnum(),
            [
                'monthly',
                'quarter',
                'semester',
                'yearly',
            ]
        );
    }

    /**
     * Get Starting Enum When Creation Employee Leave
     * This will effect Employee leave effective date
     *
     * @return array
     */
    protected function getStartingEnum(): array
    {
        return array_merge(
            $this->getCommonEnum(),
            [
                'specific_date',
                'join_date',
                'period', //when repeat set as monthly, then start of month
            ]
        );
    }

    /**
     * Get Ending Enum
     *
     * @return array
     */
    protected function getEndingEnum(): array
    {
        return array_merge(
            $this->getCommonEnum(),
            [
                'specific_date',
                'times',
            ]
        );
    }

    /**
     * Leave Limit Type
     *
     * @return array
     */
    protected function getLimitTypeEnum(): array
    {
        return array_merge(
            $this->getCommonEnum(),
            [
                'times',
                'times_per_week',
                'times_per_month',
                'times_per_quarter',
                'times_per_semester',
                'times_per_year',
            ]
        );
    }

    /**
     * Leave Paid Type
     *
     * @return array
     */
    protected function getPaidTypeEnum(): array
    {
        return array_merge(
            $this->getCommonEnum(),
            [
                'schedule',
                'mon_fri',
                'mon_sat',
                'exclude_holidays',
                'calendar_days',
            ]
        );
    }
    /**
     * Leave Max Type
     *
     * @return array
     */
    protected function getMaxTypeEnum(): array
    {
        return array_merge(
            $this->getCommonEnum(),
            [
                'day_per_leave_per_month',
                'day_per_leave_per_quarter',
                'day_per_leave_per_semester',
                'day_per_leave_per_year',
                'day_per_month',
                'day_per_quarter',
                'day_per_semester',
                'day_per_year',
            ]
        );
    }

    protected function getStatusEnum(): array
    {
        return [
            'submit',
            'approve',
            'reject',
            'cancel',
        ];
    }
}
