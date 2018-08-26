<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeLeave extends Model
{
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $fillable = [
        'effective_date', 'stop_value',
        'user_id', 'leave_type_id',
    ];
    protected $hidden = [

    ];
    protected $guarded = [
        'id',
    ];
    protected $casts = [
        'id' => 'integer',
    ];
    public $timestamps = true;
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeBuildHandler($query)
    {
        return $query->join(
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
                'leave_types.is_paid_leave', 'leave_types.paid_type',
                'leave_types.limit', 'leave_types.limit_type',
                'leave_types.max_days', 'leave_types.max_type',
            ]
        );
    }
}
