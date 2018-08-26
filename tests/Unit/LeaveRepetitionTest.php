<?php

namespace Tests\Unit;

use App\EmployeeLeave;
use App\Helpers\LeaveHandler;
use App\Helpers\LeaveTypeEnum;
use App\Leave;
use App\LeaveType;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\LeaveHelperTrait;
use Tests\TestCase;

class LeaveRepetitionTest extends TestCase
{
    use RefreshDatabase, LeaveHelperTrait;


    public function testWhenCreatingUserAndLeaveType()
    {
        list($leaveType, $user) = $this->leaveFactory();
        $data = [
            'name' => 'Some Leave',
            'has_balance' => false,
            'repeat_type' => $this->enum()->pick('repeat', 'none'),
            'starting_type' => $this->enum()->pick('starting', 'none'),
            'awarded_days' => 10,
            'ended_type' => $this->enum()->pick('ending', 'none'),
        ];

        $this->assertDatabaseHas($leaveType->getTable(), $data);
    }

    public function testWhenCreatingEmployeeLeave()
    {
        list($leaveType, $user) = $this->leaveFactory();
        $empLeave = $this->createEmployeeLeave($leaveType, $user, today());
        $data = [
            'leave_type_id' => $leaveType->id,
            'user_id' => $user->id,
            'effective_date' => today(),
            'stop_value' => null,
        ];

        $this->assertDatabaseHas($empLeave->getTable(), $data);
    }

    public function testWhenNoRepeat()
    {
        list($leaveType, $user) = $this->leaveFactory();
        $empLeave = $this->createEmployeeLeave($leaveType, $user, today());
        $leaveHandler = new LeaveHandler();
        $leaveHandler->handleRepetition();
        $leave = new Leave;
        $data = [
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'status' => 1,
            'add_days' => 10,
            'less_days' => 0,
            'expiry_date' => null,
            'start_date' => today(),
            'end_date' => today(),
            'total_days' => 1,
            'total_working_days' => 1,
            'deductible_salary' => 0,
        ];
        $this->assertDatabaseMissing($leave->getTable(), $data);
    }

    public function testWhenRepeatMonthlyOnNewRecord()
    {
        $data = [
            'repeat_type' => $this->enum()->pick('repeat', 'monthly'),
            'starting_type' => $this->enum()->pick('starting', 'specific_date'),
            'awarded_days' => 10,
        ];
        $effectiveDate = today()->subDays(10)->subMonth();
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
        $leave = new Leave;

        $leaveHandler = new LeaveHandler();
        $leaveHandler->handleRepetition();

        $this->assertDatabaseHas($empLeave->getTable(), [ 'effective_date' => $effectiveDate]);
        $this->assertDatabaseHas($leaveType->getTable(), $data);
        $this->assertDatabaseHas($leave->getTable(), $leaveData);
    }

    public function testWhenRepeatMonthlyOnExistingRecord()
    {
        $data = [
            'repeat_type' => $this->enum()->pick('repeat', 'monthly'),
            'starting_type' => $this->enum()->pick('starting', 'specific_date'),
            'awarded_days' => 10,
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

        $expectedData = array_merge($leaveData, [
            'expiry_date' => $effectiveDate->copy()->addMonths(2),
        ]);
        $leaveHandler = new LeaveHandler();
        $leaveHandler->handleRepetition();

        $this->assertDatabaseHas($empLeave->getTable(), [ 'effective_date' => $effectiveDate]);
        $this->assertDatabaseHas($leaveType->getTable(), $data);
        $this->assertDatabaseHas($leave->getTable(), $leaveData);
        $this->assertDatabaseHas($leave->getTable(), $expectedData);
    }

    public function testWhenRepeatYearlyOnNewRecord()
    {
        $data = [
            'repeat_type' => $this->enum()->pick('repeat', 'yearly'),
            'starting_type' => $this->enum()->pick('starting', 'specific_date'),
            'awarded_days' => 10,
        ];
        $effectiveDate = today()->subDays(10)->subYear();
        list($leaveType, $user) = $this->leaveFactory($data);
        $empLeave = $this->createEmployeeLeave($leaveType, $user, $effectiveDate);
        $leaveData = [
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'status' => $this->enum()->pick('status', 'approve'),
            'add_days' => 10,
            'less_days' => 0,
            'expiry_date' => $effectiveDate->copy()->addYear(),
            'start_date' => null,
            'end_date' => null,
            'total_days' => 0,
            'total_working_days' => 0,
            'deductible_salary' => 0,
        ];
        $leave = new Leave;

        $leaveHandler = new LeaveHandler();
        $leaveHandler->handleRepetition();

        $this->assertDatabaseHas($empLeave->getTable(), [ 'effective_date' => $effectiveDate]);
        $this->assertDatabaseHas($leaveType->getTable(), $data);
        $this->assertDatabaseHas($leave->getTable(), $leaveData);
    }


    public function testWhenRepeatYearlyOnExistingRecord()
    {
        $data = [
            'repeat_type' => $this->enum()->pick('repeat', 'yearly'),
            'starting_type' => $this->enum()->pick('starting', 'specific_date'),
            'awarded_days' => 10,
        ];
        $effectiveDate = today()->subDays(10)->subYears(2);
        list($leaveType, $user) = $this->leaveFactory($data);
        $empLeave = $this->createEmployeeLeave($leaveType, $user, $effectiveDate);
        $leaveData = [
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'status' => $this->enum()->pick('status', 'approve'),
            'add_days' => 10,
            'less_days' => 0,
            'expiry_date' => $effectiveDate->copy()->addYear(),
            'start_date' => null,
            'end_date' => null,
            'total_days' => 0,
            'total_working_days' => 0,
            'deductible_salary' => 0,
        ];
        $leave = new Leave($leaveData);
        $leave->save();

        $expectedData = array_merge($leaveData, [
            'expiry_date' => $effectiveDate->copy()->addYears(2),
        ]);
        $leaveHandler = new LeaveHandler();
        $leaveHandler->handleRepetition();

        $this->assertDatabaseHas($empLeave->getTable(), [ 'effective_date' => $effectiveDate]);
        $this->assertDatabaseHas($leaveType->getTable(), $data);
        $this->assertDatabaseHas($leave->getTable(), $leaveData);
        $this->assertDatabaseHas($leave->getTable(), $expectedData);
    }

    public function testWhenAfter2Recurrence()
    {
        $data = [
            'repeat_type' => $this->enum()->pick('repeat', 'monthly'),
            'starting_type' => $this->enum()->pick('starting', 'specific_date'),
            'awarded_days' => 10,
            'ended_type' => $this->enum()->pick('ending', 'times'),
        ];
        $effectiveDate = today()->subDays(10)->subMonths(3);
        list($leaveType, $user) = $this->leaveFactory($data);
        $stopValue = 2;
        $empLeave = $this->createEmployeeLeave($leaveType, $user, $effectiveDate, $stopValue);

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
        $secondRecurrence = array_merge($leaveData, ['expiry_date' => $effectiveDate->copy()->addMonths(2)]);
        $thirdRecurrence = array_merge($leaveData, ['expiry_date' => $effectiveDate->copy()->addMonths(3)]);
        Leave::create($secondRecurrence);

        $leaveHandler = new LeaveHandler();
        $leaveHandler->handleRepetition();
        $total = $leaveHandler->countTotalRepeating($empLeave);

        $this->assertEquals(2, $total);
        $this->assertDatabaseHas($empLeave->getTable(), [ 'effective_date' => $effectiveDate]);
        $this->assertDatabaseHas($leaveType->getTable(), $data);
        $this->assertDatabaseHas($leave->getTable(), $leaveData);
        $this->assertDatabaseHas($leave->getTable(), $secondRecurrence);
        $this->assertDatabaseMissing($leave->getTable(), $thirdRecurrence);
    }

    public function testWhenAfterSpecificDate()
    {
        $data = [
            'repeat_type' => $this->enum()->pick('repeat', 'monthly'),
            'starting_type' => $this->enum()->pick('starting', 'specific_date'),
            'awarded_days' => 10,
            'ended_type' => $this->enum()->pick('ending', 'specific_date'),
        ];
        $effectiveDate = today()->subDays(10)->subMonths(3);
        list($leaveType, $user) = $this->leaveFactory($data);
        // Depending on the repeat type,
        // when end type is specific date,
        // one must specify stop value greater or equal to first occurrence
        $stopValue = $effectiveDate->copy()->addMonth()->addDays(12);
        $empLeave = $this->createEmployeeLeave($leaveType, $user, $effectiveDate, $stopValue->toDateString());

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
        $secondRecurrence = array_merge($leaveData, ['expiry_date' => $effectiveDate->copy()->addMonths(2)]);
        $empLeave->ended_type = $this->enum()->pick('ending', 'specific_date');
        $leaveHandler = new LeaveHandler();
        $leaveHandler->setToday($stopValue);
        $leaveHandler->handleRepetition();
        $shouldStop = $leaveHandler->shouldStopRecurrence($empLeave);

        $this->assertTrue($shouldStop);
        $this->assertDatabaseHas($empLeave->getTable(), [ 'effective_date' => $effectiveDate]);
        $this->assertDatabaseHas($leaveType->getTable(), $data);
        $this->assertDatabaseHas($leave->getTable(), $leaveData);
        $this->assertDatabaseMissing($leave->getTable(), $secondRecurrence);
    }

    public function testWhenRepeatMonthlyOnExistingRecordAndCanAccumalate()
    {
        $data = [
            'repeat_type' => $this->enum()->pick('repeat', 'monthly'),
            'starting_type' => $this->enum()->pick('starting', 'specific_date'),
            'awarded_days' => 10,
            'can_accumulate' => true,
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
            'add_days' => 20,
        ]);
        $leaveHandler = new LeaveHandler();
        $leaveHandler->handleRepetition();

        $this->assertDatabaseHas($empLeave->getTable(), [ 'effective_date' => $effectiveDate]);
        $this->assertDatabaseHas($leaveType->getTable(), $data);
        $this->assertDatabaseHas($leave->getTable(), $expectedData);
    }

    public function testWhenRepeatMonthlyOnExistingRecordAndCanAccumalateWithALeaveRecord()
    {
        $data = [
            'repeat_type' => $this->enum()->pick('repeat', 'monthly'),
            'starting_type' => $this->enum()->pick('starting', 'specific_date'),
            'awarded_days' => 10,
            'can_accumulate' => true,
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

        $leaveStartDate = $effectiveDate->copy()->addMonth()->addDays(8);
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

        $expectedData = array_merge($leaveData, [
            'expiry_date' => $effectiveDate->copy()->addMonths(2),
            'add_days' => (10 + 10 - 8),
        ]);
        $leaveHandler = new LeaveHandler();
        $leaveHandler->handleRepetition();

        $this->assertDatabaseHas($empLeave->getTable(), [ 'effective_date' => $effectiveDate]);
        $this->assertDatabaseHas($leaveType->getTable(), $data);
        $this->assertDatabaseHas($leave->getTable(), $expectedData);
    }
}
