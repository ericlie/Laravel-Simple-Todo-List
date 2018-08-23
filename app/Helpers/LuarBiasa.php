<?php

namespace App\Helpers;

/**
 * Luar Biasa
 */
class LuarBiasa
{
    private $type = 'SuKSES';

    // public function __get($key)
    // {
    //     return $this->{$key};
    // }

    // public function __set($key, $value)
    // {
    //     return $this->{$key} = $value;
    // }

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $fillable = [

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
}
