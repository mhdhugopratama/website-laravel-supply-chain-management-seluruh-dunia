<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$iso3 = 'VAT';
$restData = app(\App\Services\RestCountriesService::class)->getCountryData($iso3);
print_r($restData);

$country = \App\Models\Country::where('iso3', $iso3)->first();
if ($country) {
    $updated = array_filter([
        'capital'         => $restData['capital'] ?? $country->capital,
        'population'      => $restData['population'] ?? $country->population,
        'languages'       => $restData['languages'] ?? $country->languages,
        'area'            => $restData['area'] ?? $country->area,
        'latitude'        => $restData['latitude'] ?? $country->latitude,
        'longitude'       => $restData['longitude'] ?? $country->longitude,
        'currency_code'   => $restData['currency_code'] ?? $country->currency_code,
        'currency_name'   => $restData['currency_name'] ?? $country->currency_name,
        'currency_symbol' => $restData['currency_symbol'] ?? $country->currency_symbol,
    ], fn($val) => !is_null($val));
    print_r($updated);
    
    $country->update($updated);
    echo "Updated!\n";
}
