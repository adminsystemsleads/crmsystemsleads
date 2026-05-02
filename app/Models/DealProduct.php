<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealProduct extends Model
{
    protected $fillable = [
        'deal_id', 'product_id', 'name', 'unit', 'quantity', 'unit_price', 'discount', 'total', 'notes',
    ];

    protected $casts = [
        'quantity'   => 'float',
        'unit_price' => 'float',
        'discount'   => 'float',
        'total'      => 'float',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $item) {
            $item->total = round(
                $item->quantity * $item->unit_price * (1 - $item->discount / 100),
                2
            );
        });

        static::saved(function (self $item) {
            $item->deal?->recalculateAmount();
        });

        static::deleted(function (self $item) {
            $item->deal?->recalculateAmount();
        });
    }

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
