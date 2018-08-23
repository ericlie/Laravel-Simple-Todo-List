<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $fillable = [
        'user_id',
        'leave_type_id',
        'status', 'add_days',
        'less_days', 'expiry_date',
        'start_date', 'end_date',
        'total_days', 'total_working_days',
        'deductible_salary',
    ];
    protected $hidden = [

    ];
    protected $guarded = [
        'id',
    ];
    protected $casts = [
        'id' => 'integer',
        'deductible_salary' => 'float',
    ];
    public $timestamps = true;
    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
