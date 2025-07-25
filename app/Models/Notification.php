<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'content',
        'company_id',
        'fundraising_id'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    // Relasi ke Company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Relasi ke Fundraising
    public function fundraising()
    {
        return $this->belongsTo(Fundraising::class);
    }
}
