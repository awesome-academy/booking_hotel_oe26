<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'data',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('status');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
