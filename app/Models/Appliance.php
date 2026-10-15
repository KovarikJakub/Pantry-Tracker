<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appliance extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'appliance_type',
    ];

    /**
     * @return HasMany<Shelf, $this>
     */
    public function shelves(): HasMany
    {
        return $this->hasMany(Shelf::class)->orderBy('shelf_order');
    }
}
