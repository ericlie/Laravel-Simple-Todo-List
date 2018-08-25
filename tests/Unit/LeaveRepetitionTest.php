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
use Tests\TestCase;

class LeaveRepetitionTest extends TestCase
{
    use RefreshDatabase;

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
}
