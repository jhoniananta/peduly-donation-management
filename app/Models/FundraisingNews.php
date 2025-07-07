<?php

namespace App\Models;

use App\Models\Fundraising;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundraisingNews extends Model
{
    protected $guarded = ['id'];
    /**
     * Get the fundraising that owns the FundraisingNews
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fundraising(): BelongsTo
    {
        return $this->belongsTo(Fundraising::class, 'fundraising_id', 'id');
    }
}
