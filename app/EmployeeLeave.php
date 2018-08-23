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
}
