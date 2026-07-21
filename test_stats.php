<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$noPop = \App\Models\Country::whereNull('population')->orWhere('population', 0)->count();
$noArea = \App\Models\Country::whereNull('area')->orWhere('area', 0)->count();
echo "No population: $noPop\n";
echo "No area: $noArea\n";
