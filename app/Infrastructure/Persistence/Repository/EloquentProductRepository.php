<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Application\Ports\Outbound\ProductRepository;
use App\Domain\Exception\ConcurrencyConflictException;
use App\Domain\Model\Product;
use App\Domain\ValueObject\ProductId;
use App\Infrastructure\Persistence\Mapper\ProductMapper;
use App\Infrastructure\Persistence\Model\ProductModel;
use Illuminate\Support\Facades\DB;

final class EloquentProductRepository implements ProductRepository
{
    /**
     * Cache of tracked versions loaded during the current lifecycle/transaction.
     * Maps product UUID => version integer.
     *
     * @var array<string, int>
     */
    private array $trackedVersions = [];

    public function findById(ProductId $id): ?Product
    {
        $model = ProductModel::where('id', $id->getValue())->first();
        if ($model === null) {
            return null;
        }

        $this->trackedVersions[$model->id] = (int) $model->version;

        return ProductMapper::toDomain($model);
    }

    public function findActiveById(ProductId $id): ?Product
    {
        $model = ProductModel::where('id', $id->getValue())
            ->whereNull('deleted_at')
            ->first();

        if ($model === null) {
            return null;
        }

        $this->trackedVersions[$model->id] = (int) $model->version;

        return ProductMapper::toDomain($model);
    }

    /**
     * @param array<ProductId> $ids
     * @return array<Product>
     */
    public function findActiveByIds(array $ids): array
    {
        $stringIds = array_map(fn(ProductId $id) => $id->getValue(), $ids);

        $models = ProductModel::whereIn('id', $stringIds)
            ->whereNull('deleted_at')
            ->get();

        $products = [];
        foreach ($models as $model) {
            $this->trackedVersions[$model->id] = (int) $model->version;
            $products[] = ProductMapper::toDomain($model);
        }

        return $products;
    }

    public function save(Product $product): void
    {
        $id = $product->getId()->getValue();
        $existing = ProductModel::where('id', $id)->first();

        if ($existing === null) {
            // Inserción de nuevo producto con version = 1
            $model = ProductMapper::toModel($product);
            $model->version = 1;
            $model->deleted_at = $product->isActive() ? null : now();
            $model->save();
            $this->trackedVersions[$id] = 1;
            return;
        }

        // Actualización atómica con verificación de version (ADR-002)
        $currentVersion = $this->trackedVersions[$id] ?? (int) $existing->version;

        $updateData = [
            'name' => $product->getName(),
            'price' => $product->getPrice()->getAmount()->toFloat(),
            'stock' => $product->getStock(),
            'category_id' => $product->getCategoryId()->getValue(),
            'image_key' => $product->getImageKey(),
            'version' => DB::raw('version + 1'),
        ];

        if (!$product->isActive() && $existing->deleted_at === null) {
            $updateData['deleted_at'] = now();
        } elseif ($product->isActive() && $existing->deleted_at !== null) {
            $updateData['deleted_at'] = null;
        }

        $affected = DB::table('product')
            ->where('id', $id)
            ->where('version', $currentVersion)
            ->update($updateData);

        if ($affected === 0) {
            throw new ConcurrencyConflictException(
                "Conflicto de concurrencia al actualizar el producto '{$product->getName()}'. Los datos fueron modificados por otra operación."
            );
        }

        $this->trackedVersions[$id] = $currentVersion + 1;
    }

    public function delete(ProductId $id): void
    {
        // Baja lógica según ADR-003: actualiza deleted_at, no elimina físicamente la fila
        $affected = DB::table('product')
            ->where('id', $id->getValue())
            ->whereNull('deleted_at')
            ->update([
                'deleted_at' => now(),
                'version' => DB::raw('version + 1'),
            ]);

        if ($affected > 0 && isset($this->trackedVersions[$id->getValue()])) {
            $this->trackedVersions[$id->getValue()]++;
        }
    }

    /**
     * @return array<Product>
     */
    public function findAllActive(
        ?string $search = null,
        ?string $categoryId = null,
        int $page = 1,
        int $size = 20
    ): array {
        $query = ProductModel::whereNull('deleted_at');

        if ($search !== null && trim($search) !== '') {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], trim($search));
            $query->where('name', 'LIKE', "%{$escaped}%");
        }

        if ($categoryId !== null && trim($categoryId) !== '') {
            $query->where('category_id', trim($categoryId));
        }

        $models = $query->orderBy('name', 'asc')
            ->skip(($page - 1) * $size)
            ->take($size)
            ->get();

        $products = [];
        foreach ($models as $model) {
            $this->trackedVersions[$model->id] = (int) $model->version;
            $products[] = ProductMapper::toDomain($model);
        }

        return $products;
    }

    public function countAllActive(?string $search = null, ?string $categoryId = null): int
    {
        $query = ProductModel::whereNull('deleted_at');

        if ($search !== null && trim($search) !== '') {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], trim($search));
            $query->where('name', 'LIKE', "%{$escaped}%");
        }

        if ($categoryId !== null && trim($categoryId) !== '') {
            $query->where('category_id', trim($categoryId));
        }

        return $query->count();
    }
}

