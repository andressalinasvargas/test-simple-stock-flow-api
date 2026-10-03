<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductModel extends Model
{
    protected $table = 'product';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'price',
        'stock',
        'category_id',
        'image_key',
        'deleted_at',
        'version',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'version' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryModel::class, 'category_id', 'id');
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItemModel::class, 'product_id', 'id');
    }
}

