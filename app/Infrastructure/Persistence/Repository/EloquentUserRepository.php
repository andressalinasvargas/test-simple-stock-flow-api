<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Application\Ports\Outbound\UserRepository;
use App\Domain\Model\User;
use App\Infrastructure\Persistence\Mapper\UserMapper;
use App\Infrastructure\Persistence\Model\UserModel;

final class EloquentUserRepository implements UserRepository
{
    public function findById(string $id): ?User
    {
        $model = UserModel::find($id);
        return $model !== null ? UserMapper::toDomain($model) : null;
    }

    public function findByUsername(string $username): ?User
    {
        $normalized = strtolower(trim($username));
        $model = UserModel::where('username', $normalized)->first();
        return $model !== null ? UserMapper::toDomain($model) : null;
    }

    public function save(User $user): void
    {
        UserModel::updateOrCreate(
            ['id' => $user->getId()],
            [
                'username' => $user->getUsername()->getValue(),
                'password_hash' => $user->getPasswordHash(),
                'role' => $user->getRole()->getValue(),
            ]
        );
    }
}
