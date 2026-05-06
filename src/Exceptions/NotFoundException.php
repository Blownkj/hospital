<?php
declare(strict_types=1);

namespace App\Exceptions;

/** Resource not found — Router catches this and returns 404. */
class NotFoundException extends \RuntimeException {}
