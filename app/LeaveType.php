<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $fillable = [
        'name', 'has_balance', 'repeat_type', 'repeat_value',
        'starting_type', 'awarded_days',
        'ended_type', 'can_accumulate',
        'is_paid_leave', 'paid_type',
        'limit', 'limit_type', 'max_days',
        'max_type', 'is_compulsory',
    ];
    protected $hidden = [

    ];
    protected $guarded = [
        'id',
    ];
    protected $casts = [
        'id' => 'integer',
        'has_balance' => 'boolean',
        'can_accumulate' => 'boolean',
        'is_paid_leave' => 'boolean',
        'is_compulsory' => 'boolean',
    ];
    public $timestamps = true;
    protected $dates = [
        'created_at',
        'updated_at',

    ];
}
