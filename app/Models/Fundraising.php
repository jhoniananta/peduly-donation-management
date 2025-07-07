<?php

namespace App\Models;

use App\Models\Company;
use App\Models\Donation;
use App\Models\FundraisingNews;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fundraising extends Model
{
    protected $guarded = ['id'];
    /**
     * Get all of the fundraising_news for the Fundraising
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    protected $appends = ['total'];

    public function fundraising_news(): HasMany
    {
        return $this->hasMany(FundraisingNews::class, 'fundraising_id', 'id');
    }

    /**
     * Get the company that owns the Fundraising
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
    /**
     * Get all of the donations for the Fundraising
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class, 'fundraising_id', 'id');
    }
    public function getTotalAttribute(): int
    {
        return $this->donations()->where('status','settlement')->sum('total');
    }
}
