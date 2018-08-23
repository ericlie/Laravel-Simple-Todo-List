<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
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
    public function comments()
    {
        return $this->morphMany(Comment::class, 'komentarkan');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
