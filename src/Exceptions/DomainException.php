<?php
declare(strict_types=1);

namespace App\Exceptions;

/** Business-rule violation — shown to the user as a flash error. */
class DomainException extends \DomainException {}
