<?php
declare(strict_types=1);

namespace App\Enums;

enum PrescriptionType: string
{
    case Drug      = 'drug';
    case Procedure = 'procedure';
    case Referral  = 'referral';
}
