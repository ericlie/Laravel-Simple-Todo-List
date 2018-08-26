<?php

namespace Tests\Unit;

use App\EmployeeLeave;
use App\Helpers\LeaveApplication;
use App\Helpers\LeaveHandler;
use App\Leave;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\LeaveHelperTrait;
use Tests\TestCase;

class LeaveApplicationTest extends TestCase
{
    use RefreshDatabase, LeaveHelperTrait;


    public function testWhenCreatingLeave()
    {
        $data = [
            'repeat_type' => $this->enum()->pick('repeat', 'monthly'),
            'starting_type' => $this->enum()->pick('starting', 'specific_date'),
            'awarded_days' => 10,
            'limit' => 1,
            'limit_type' => $this->enum()->pick('limit_type', 'times'),
        ];
        $effectiveDate = today()->subDays(10)->subMonths(2);
        list($leaveType, $user) = $this->leaveFactory($data);
        $empLeave = $this->createEmployeeLeave($leaveType, $user, $effectiveDate);
        $leaveData = [
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'status' => $this->enum()->pick('status', 'approve'),
            'add_days' => 10,
            'less_days' => 0,
            'expiry_date' => $effectiveDate->copy()->addMonth(),
            'start_date' => null,
            'end_date' => null,
            'total_days' => 0,
            'total_working_days' => 0,
            'deductible_salary' => 0,
        ];
        $leave = new Leave($leaveData);
        $leave->save();
        $this->assertDatabaseHas($leave->getTable(), $leaveData);

        $expectedData = array_merge($leaveData, [
            'expiry_date' => $effectiveDate->copy()->addMonths(2),
            'add_days' => 10,
        ]);
        $leaveHandler = new LeaveHandler();
        $leaveHandler->handleRepetition();

        $leaveStartDate = $effectiveDate->copy()->addMonth(2)->addDays(8);
        $leaveEndDate = $leaveStartDate->copy()->addDays(8);
        $totalWorkingDays = $leaveStartDate->diffInDaysFiltered(
            function (Carbon $date) {
                return ! $date->isWeekend();
            },
            $leaveEndDate
        );
        $applyLeave = array_merge($leaveData, [
            'expiry_date' => null,
            'add_days' => 0,
            'start_date' => $leaveStartDate,
            'end_date' => $leaveEndDate,
            'less_days' => 8,
            'total_days' => $leaveStartDate->diffInDays($leaveEndDate),
            'total_working_days' => $totalWorkingDays,
            'deductible_salary' => 0,
        ]);
        Leave::create($applyLeave);
        $this->assertDatabaseHas($leave->getTable(), $applyLeave);

        $leaveApplication = new LeaveApplication($user, $leaveType);
        $employeeLeave = EmployeeLeave::buildHandler()->where('employee_leaves.user_id', $user->id)
            ->where('employee_leaves.leave_type_id', $leaveType->id)
            ->first();
        $canApply = $leaveApplication
            ->setStartDate($leaveEndDate->copy()->addDays(10))
            ->setEndDate($leaveEndDate->copy()->addDays(15))
            ->canApply($employeeLeave);

        $this->assertFalse($canApply);
        $this->assertDatabaseHas($empLeave->getTable(), [ 'effective_date' => $effectiveDate]);
        $this->assertDatabaseHas($leaveType->getTable(), $data);
        $this->assertDatabaseHas($leave->getTable(), $expectedData);
    }
}
