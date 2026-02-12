<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingGroup extends Model
{
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
}
