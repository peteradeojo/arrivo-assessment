<?php

namespace App\Models;

use Database\Factories\SavingPlanFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// #[UseFactory(SavingPlanFactory::class)]
class SavingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'description',
        'target',
        'balance',
        'expire_date',
        'owner_type',
        'owner_id',
    ];

    protected $with = ['owner'];

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
