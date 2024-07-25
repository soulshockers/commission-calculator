<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Kernel;

$kernel = new Kernel();
$kernel->boot();

return $kernel->getContainer();