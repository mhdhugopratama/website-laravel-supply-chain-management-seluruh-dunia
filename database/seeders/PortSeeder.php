<?php

namespace Database\Seeders;

use App\Models\Port;
use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PortSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('seeders/raw_ports.json');
        
        if (!file_exists($jsonPath)) {
            $this->command->error("raw_ports.json not found! Please download it first.");
            return;
        }

        $this->command->info("Parsing raw_ports.json...");
        $rawPorts = json_decode(file_get_contents($jsonPath), true);
        
        if (!$rawPorts) {
            $this->command->error("Failed to parse JSON.");
            return;
        }

        // To map country names from ports JSON to iso3 codes
        $countries = Country::pluck('iso3', 'name')->mapWithKeys(function($item, $key) {
            return [strtolower($key) => $item];
        })->toArray();
        
        // Aliases for common mismatches
        $countryAliases = [
            'united states' => 'USA',
            'united kingdom' => 'GBR',
            'russia' => 'RUS',
            'south korea' => 'KOR',
            'vietnam' => 'VNM',
            'taiwan' => 'TWN'
        ];

        $portsByCountry = [];

        foreach ($rawPorts as $unlocode => $portData) {
            if (empty($portData['country']) || empty($portData['coordinates'])) continue;
            
            $countryNameLower = strtolower($portData['country']);
            $iso3 = $countryAliases[$countryNameLower] ?? ($countries[$countryNameLower] ?? null);
            
            if (!$iso3) continue;

            if (!isset($portsByCountry[$iso3])) {
                $portsByCountry[$iso3] = [];
            }

            if (count($portsByCountry[$iso3]) >= 5) {
                continue; // Only take up to 5 ports per country
            }

            $portsByCountry[$iso3][] = [
                'name'         => $portData['name'],
                'country_code' => $iso3,
                'country_name' => $portData['country'],
                'latitude'     => $portData['coordinates'][1], // JSON has [lon, lat]
                'longitude'    => $portData['coordinates'][0],
                'un_locode'    => $unlocode,
                'type'         => 'Sea Port',
            ];
        }

        $this->command->info("Inserting ports into database...");
        
        $inserted = 0;
        foreach ($portsByCountry as $iso3 => $ports) {
            foreach ($ports as $port) {
                Port::updateOrCreate(['un_locode' => $port['un_locode']], $port);
                $inserted++;
            }
        }

        $this->command->info("Successfully seeded $inserted ports across " . count($portsByCountry) . " countries.");
    }
}
