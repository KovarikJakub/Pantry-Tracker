<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shelf extends Model
{
    use HasUuids;

    protected $table = 'shelves';

    protected $fillable = [
        'label',
        'shelf_order',
        'max_load_capacity_g',
    ];

    protected function casts(): array
    {
        return [
            'shelf_order' => 'integer',
            'max_load_capacity_g' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Appliance, $this>
     */
    public function appliance(): BelongsTo
    {
        return $this->belongsTo(Appliance::class);
    }
}
