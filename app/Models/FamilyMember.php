<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['family_id', 'nik', 'name', 'birth_date', 'gender', 'relation'])]
class FamilyMember extends Model
{
    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function kmsRecords()
    {
        return $this->hasMany(KmsRecord::class);
    }
}
