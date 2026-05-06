<?php
declare(strict_types=1);

namespace App\Exceptions;

/** Access denied — Router catches this and returns 403. */
class ForbiddenException extends \RuntimeException {}
