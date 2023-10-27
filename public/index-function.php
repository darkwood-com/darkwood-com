<?php

declare(strict_types=1);

namespace App;

use App\Kernel;
use Bref\SymfonyBridge\Http\KernelAdapter;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';
require_once dirname(__DIR__) . '/.env.local.php';

return new KernelAdapter(new Kernel('prod', false));
