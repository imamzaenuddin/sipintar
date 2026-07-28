<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Family extends Model
{
    protected $fillable = [
        'user_id',
        'head_of_family_nik',
        'no_kk',
        'head_of_family_name',
        'province_code',
        'city_code',
        'district_code',
        'village_code',
        'address_rt',
        'address_rw',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function province()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\Province::class, 'province_code', 'code');
    }

    public function city()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\City::class, 'city_code', 'code');
    }

    public function district()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\District::class, 'district_code', 'code');
    }

    public function village()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\Village::class, 'village_code', 'code');
    }
}
