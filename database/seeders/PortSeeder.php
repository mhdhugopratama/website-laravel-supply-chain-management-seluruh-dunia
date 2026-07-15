<?php

namespace Database\Seeders;

use App\Models\Port;
use Illuminate\Database\Seeder;

class PortSeeder extends Seeder
{
    public function run(): void
    {
        $ports = [
            ['name' => 'Port of Shanghai', 'country_code' => 'CHN', 'country_name' => 'China', 'latitude' => 31.2304, 'longitude' => 121.4737, 'un_locode' => 'CNSHA', 'type' => 'Sea Port'],
            ['name' => 'Port of Singapore', 'country_code' => 'SGP', 'country_name' => 'Singapore', 'latitude' => 1.2897, 'longitude' => 103.8501, 'un_locode' => 'SGSIN', 'type' => 'Sea Port'],
            ['name' => 'Port of Rotterdam', 'country_code' => 'NLD', 'country_name' => 'Netherlands', 'latitude' => 51.9225, 'longitude' => 4.47917, 'un_locode' => 'NLRTM', 'type' => 'Sea Port'],
            ['name' => 'Port of Ningbo-Zhoushan', 'country_code' => 'CHN', 'country_name' => 'China', 'latitude' => 29.8683, 'longitude' => 121.5440, 'un_locode' => 'CNNBO', 'type' => 'Sea Port'],
            ['name' => 'Port of Guangzhou', 'country_code' => 'CHN', 'country_name' => 'China', 'latitude' => 23.1291, 'longitude' => 113.2644, 'un_locode' => 'CNGZH', 'type' => 'Sea Port'],
            ['name' => 'Port of Busan', 'country_code' => 'KOR', 'country_name' => 'South Korea', 'latitude' => 35.1796, 'longitude' => 129.0756, 'un_locode' => 'KRPUS', 'type' => 'Sea Port'],
            ['name' => 'Port of Jebel Ali', 'country_code' => 'ARE', 'country_name' => 'United Arab Emirates', 'latitude' => 24.9857, 'longitude' => 55.0272, 'un_locode' => 'AEJEA', 'type' => 'Sea Port'],
            ['name' => 'Port of Qingdao', 'country_code' => 'CHN', 'country_name' => 'China', 'latitude' => 36.0671, 'longitude' => 120.3826, 'un_locode' => 'CNTAO', 'type' => 'Sea Port'],
            ['name' => 'Port of Hong Kong', 'country_code' => 'HKG', 'country_name' => 'Hong Kong', 'latitude' => 22.2855, 'longitude' => 114.1577, 'un_locode' => 'HKHKG', 'type' => 'Sea Port'],
            ['name' => 'Port of Tianjin', 'country_code' => 'CHN', 'country_name' => 'China', 'latitude' => 39.0075, 'longitude' => 117.7133, 'un_locode' => 'CNTJN', 'type' => 'Sea Port'],
            ['name' => 'Port of Los Angeles', 'country_code' => 'USA', 'country_name' => 'United States', 'latitude' => 33.7383, 'longitude' => -118.2712, 'un_locode' => 'USLAX', 'type' => 'Sea Port'],
            ['name' => 'Port of Long Beach', 'country_code' => 'USA', 'country_name' => 'United States', 'latitude' => 33.7676, 'longitude' => -118.1978, 'un_locode' => 'USLGB', 'type' => 'Sea Port'],
            ['name' => 'Port of Antwerp', 'country_code' => 'BEL', 'country_name' => 'Belgium', 'latitude' => 51.2194, 'longitude' => 4.4025, 'un_locode' => 'BEANR', 'type' => 'Sea Port'],
            ['name' => 'Port of Hamburg', 'country_code' => 'DEU', 'country_name' => 'Germany', 'latitude' => 53.5500, 'longitude' => 9.9937, 'un_locode' => 'DEHAM', 'type' => 'Sea Port'],
            ['name' => 'Port of Klang', 'country_code' => 'MYS', 'country_name' => 'Malaysia', 'latitude' => 3.0319, 'longitude' => 101.3924, 'un_locode' => 'MYPKG', 'type' => 'Sea Port'],
            ['name' => 'Port Tanjung Pelepas', 'country_code' => 'MYS', 'country_name' => 'Malaysia', 'latitude' => 1.3628, 'longitude' => 103.5510, 'un_locode' => 'MYPTP', 'type' => 'Sea Port'],
            ['name' => 'Port of Xiamen', 'country_code' => 'CHN', 'country_name' => 'China', 'latitude' => 24.4798, 'longitude' => 118.0894, 'un_locode' => 'CNXMN', 'type' => 'Sea Port'],
            ['name' => 'Port of Kaohsiung', 'country_code' => 'TWN', 'country_name' => 'Taiwan', 'latitude' => 22.6273, 'longitude' => 120.3014, 'un_locode' => 'TWKHH', 'type' => 'Sea Port'],
            ['name' => 'Port of Mumbai', 'country_code' => 'IND', 'country_name' => 'India', 'latitude' => 18.9322, 'longitude' => 72.8375, 'un_locode' => 'INBOM', 'type' => 'Sea Port'],
            ['name' => 'Port of Colombo', 'country_code' => 'LKA', 'country_name' => 'Sri Lanka', 'latitude' => 6.9271, 'longitude' => 79.8612, 'un_locode' => 'LKCMB', 'type' => 'Sea Port'],
            ['name' => 'Port of Durban', 'country_code' => 'ZAF', 'country_name' => 'South Africa', 'latitude' => -29.8587, 'longitude' => 31.0218, 'un_locode' => 'ZADDR', 'type' => 'Sea Port'],
            ['name' => 'Port of Santos', 'country_code' => 'BRA', 'country_name' => 'Brazil', 'latitude' => -23.9608, 'longitude' => -46.3336, 'un_locode' => 'BRSSZ', 'type' => 'Sea Port'],
            ['name' => 'Port of Barcelona', 'country_code' => 'ESP', 'country_name' => 'Spain', 'latitude' => 41.3879, 'longitude' => 2.1699, 'un_locode' => 'ESBCN', 'type' => 'Sea Port'],
            ['name' => 'Port of Piraeus', 'country_code' => 'GRC', 'country_name' => 'Greece', 'latitude' => 37.9375, 'longitude' => 23.6460, 'un_locode' => 'GRPIR', 'type' => 'Sea Port'],
            ['name' => 'Port of Felixstowe', 'country_code' => 'GBR', 'country_name' => 'United Kingdom', 'latitude' => 51.9531, 'longitude' => 1.3503, 'un_locode' => 'GBFXT', 'type' => 'Sea Port'],
            ['name' => 'Port of New York', 'country_code' => 'USA', 'country_name' => 'United States', 'latitude' => 40.6892, 'longitude' => -74.0445, 'un_locode' => 'USNYC', 'type' => 'Sea Port'],
            ['name' => 'Port of Valencia', 'country_code' => 'ESP', 'country_name' => 'Spain', 'latitude' => 39.4559, 'longitude' => -0.3271, 'un_locode' => 'ESVLC', 'type' => 'Sea Port'],
            ['name' => 'Port of Tokyo', 'country_code' => 'JPN', 'country_name' => 'Japan', 'latitude' => 35.6496, 'longitude' => 139.7748, 'un_locode' => 'JPTYO', 'type' => 'Sea Port'],
            ['name' => 'Port of Osaka', 'country_code' => 'JPN', 'country_name' => 'Japan', 'latitude' => 34.6544, 'longitude' => 135.5091, 'un_locode' => 'JPOSA', 'type' => 'Sea Port'],
            ['name' => 'Port of Sydney', 'country_code' => 'AUS', 'country_name' => 'Australia', 'latitude' => -33.8688, 'longitude' => 151.2093, 'un_locode' => 'AUSYD', 'type' => 'Sea Port'],
            ['name' => 'Port of Cape Town', 'country_code' => 'ZAF', 'country_name' => 'South Africa', 'latitude' => -33.9249, 'longitude' => 18.4241, 'un_locode' => 'ZACPT', 'type' => 'Sea Port'],
            ['name' => 'Port of Lagos', 'country_code' => 'NGA', 'country_name' => 'Nigeria', 'latitude' => 6.4541, 'longitude' => 3.3947, 'un_locode' => 'NGLOS', 'type' => 'Sea Port'],
            ['name' => 'Port of Mombasa', 'country_code' => 'KEN', 'country_name' => 'Kenya', 'latitude' => -4.0435, 'longitude' => 39.6682, 'un_locode' => 'KEMBA', 'type' => 'Sea Port'],
            ['name' => 'Port of Alexandria', 'country_code' => 'EGY', 'country_name' => 'Egypt', 'latitude' => 31.2001, 'longitude' => 29.9187, 'un_locode' => 'EGALX', 'type' => 'Sea Port'],
            ['name' => 'Port of Istanbul', 'country_code' => 'TUR', 'country_name' => 'Turkey', 'latitude' => 41.0082, 'longitude' => 28.9784, 'un_locode' => 'TRIST', 'type' => 'Sea Port'],
        ];

        foreach ($ports as $port) {
            Port::updateOrCreate(['un_locode' => $port['un_locode']], $port);
        }

        $this->command->info('Seeded ' . count($ports) . ' major world ports.');
    }
}
