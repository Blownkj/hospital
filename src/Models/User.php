<?php
declare(strict_types=1);

namespace App\Models;

class User
{
    public function __construct(
        public readonly int    $id,
        public readonly string $email,
        public readonly string $passwordHash,
        public readonly string $role,
        public readonly string $createdAt,
    ) {}
}