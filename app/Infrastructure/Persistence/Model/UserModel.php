<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'username',
        'password_hash',
        'role',
    ];

    protected $hidden = [
        'password_hash',
    ];
}
