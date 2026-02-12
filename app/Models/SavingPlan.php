<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingPlan extends Model
{
    protected $fillable = [
        'user_id',
        'description',
        'target',
        'balance',
        'expire_date',
        'owner_type',
        'owner_id',
    ];

    protected $hidden = [
        'owner_type',
        'owner_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function owner()
    {
        return $this->morphTo();
    }
}
