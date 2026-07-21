<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$zeroArea = \App\Models\Country::where('area', 0)->count();
echo "Zero area: $zeroArea\n";

$noPop = \App\Models\Country::whereNull('population')->count();
echo "Null population: $noPop\n";
