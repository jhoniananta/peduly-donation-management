<?php

namespace App\Models;

use App\Models\Fundraising;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $guarded = [];
    /**
     * Get all of the fundraisings for the Company
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fundraisings(): HasMany
    {
        return $this->hasMany(Fundraising::class, 'company_id', 'id');
    }
}
