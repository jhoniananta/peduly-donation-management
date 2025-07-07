<?php

namespace App\Models;

use App\Models\Donor;
use App\Models\Fundraising;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    //
    public function fundraising(): BelongsTo
    {
        return $this->belongsTo(Fundraising::class, 'fundraising_id', 'id');
    }

    /**
     * Get the donor that owns the Donation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function donor(): BelongsTo
    {
        return $this->belongsTo(Donor::class, 'donor_id', 'id');
    }

}
