<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
$user->name = 'Mentor ' . rand(1, 100);
$user->save();

print_r(\App\Models\Audit::orderBy('id', 'desc')->first()->toArray());
