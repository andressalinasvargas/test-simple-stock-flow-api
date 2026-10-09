<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItemModel extends Model
{
    protected $table = 'sale_item';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'sale_id',
        'product_id',
        'product_name',
        'category_name',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(SaleModel::class, 'sale_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_id', 'id');
    }
}

