<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__.'/../vendor/orchestra/testbench-core/laravel/bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
