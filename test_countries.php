<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = \Illuminate\Support\Facades\Http::withoutVerifying()
    ->get('https://restcountries.com/v3.1/all?fields=name,cca2')
    ->collect()
    ->mapWithKeys(fn ($c) => [($c['name']['common'] ?? '') => ($c['name']['common'] ?? '')])
    ->filter(fn ($name, $code) => !empty($name))
    ->sort()
    ->toArray();

echo count($c) . " countries found\n";
print_r(array_slice($c, 0, 5));
print_r(array_slice($c, -5));
