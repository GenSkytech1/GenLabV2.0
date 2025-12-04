<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingPersonDeviceToken extends Model
{
    use HasFactory;

    protected $table = 'marketing_person_device_token';

    protected $fillable = [
        'marketing_person_id',
        'device_token',
    ];

    public function marketingPerson()
    {
        return $this->belongsTo(User::class, 'marketing_person_id');
    } 
    
}
