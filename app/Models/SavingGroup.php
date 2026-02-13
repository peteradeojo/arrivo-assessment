<?php

namespace App\Models;

use Database\Factories\SavingGroupFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// #[UseFactory((SavingGroupFactory::class))]
class SavingGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'user_id',
        'status'
    ];

    public function plans() {
        return $this->morphMany(SavingPlan::class, 'owner');
    }

    public function members() {
        return $this->hasMany(SavingGroupMember::class, 'group_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
