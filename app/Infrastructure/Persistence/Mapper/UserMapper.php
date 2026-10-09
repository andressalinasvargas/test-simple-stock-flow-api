<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mapper;

use App\Domain\Model\User;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\Username;
use App\Infrastructure\Persistence\Model\UserModel;

final class UserMapper
{
    public static function toDomain(UserModel $model): User
    {
        return new User(
            id: (string) $model->id,
            username: new Username((string) $model->username),
            passwordHash: (string) $model->password_hash,
            role: Role::from((string) $model->role)
        );
    }

    public static function toModel(User $user): UserModel
    {
        $model = new UserModel();
        $model->id = $user->getId();
        $model->username = $user->getUsername()->value();
        $model->password_hash = $user->getPasswordHash();
        $model->role = $user->getRole()->value;

        return $model;
    }
}

