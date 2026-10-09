<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryModel extends Model
{
    protected $table = 'category';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(ProductModel::class, 'category_id', 'id');
    }
}

