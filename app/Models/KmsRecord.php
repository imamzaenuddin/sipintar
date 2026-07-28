<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'family_member_id',
    'recorded_date',
    'weight',
    'height',
    'head_circumference',
    'lila',
    'z_score',
    'status_gizi',
    'recorder_id',
    'blood_pressure',
    'belly_circumference',
    'blood_sugar',
    'uric_acid',
    'cholesterol',
    'examination_notes'
])]
class KmsRecord extends Model
{
    public function familyMember()
    {
        return $this->belongsTo(FamilyMember::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorder_id');
    }
}
