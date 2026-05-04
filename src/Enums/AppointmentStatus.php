<?php
declare(strict_types=1);

namespace App\Enums;

enum AppointmentStatus: string
{
    case Pending    = 'pending';
    case Confirmed  = 'confirmed';
    case InProgress = 'in_progress';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';
}
