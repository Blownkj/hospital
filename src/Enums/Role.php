<?php
declare(strict_types=1);

namespace App\Enums;

enum Role: string
{
    case Patient = 'patient';
    case Doctor  = 'doctor';
    case Admin   = 'admin';
}
