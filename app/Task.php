<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'komentarkan');
    }
}
