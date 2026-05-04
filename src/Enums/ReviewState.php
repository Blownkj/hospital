<?php
declare(strict_types=1);

namespace App\Enums;

enum ReviewState: int
{
    case Pending  = 0;
    case Approved = 1;
}
