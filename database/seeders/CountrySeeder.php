<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $endpoints = [
            'https://restcountries.com/v3.1/all?fields=name,cca2,cca3,capital,region,subregion,population,latlng,currencies,flag,area,languages',
            'https://restcountries.com/v3/all?fields=name,cca2,cca3,capital,region,subregion,population,latlng,currencies,flag,area,languages',
        ];

        $countries = null;

        foreach ($endpoints as $url) {
            $response = Http::timeout(30)
                ->withOptions(['verify' => false])
                ->get($url);

            if ($response->successful()) {
                $body = $response->json();
                if (is_array($body) && isset($body[0]['cca3'])) {
                    $countries = $body;
                    break;
                }
            }
        }

        if (!$countries) {
            $this->command->warn('REST Countries API unavailable or deprecated. Seeding from local fallback data...');
            $this->seedFallback();
            return;
        }

        $inserted = 0;

        foreach ($countries as $c) {
            if (empty($c['cca3'])) continue;

            $currencies     = $c['currencies'] ?? [];
            $currencyCode   = array_key_first($currencies);
            $currencyName   = $currencies[$currencyCode]['name'] ?? null;
            $currencySymbol = $currencies[$currencyCode]['symbol'] ?? null;
            $lat            = $c['latlng'][0] ?? null;
            $lng            = $c['latlng'][1] ?? null;

            $langArr        = $c['languages'] ?? [];
            $languagesStr   = is_array($langArr) ? implode(', ', $langArr) : null;

            Country::updateOrCreate(
                ['iso3' => $c['cca3']],
                [
                    'name'            => $c['name']['common'],
                    'iso2'            => $c['cca2'] ?? null,
                    'capital'         => $c['capital'][0] ?? null,
                    'region'          => $c['region'] ?? null,
                    'subregion'       => $c['subregion'] ?? null,
                    'population'      => $c['population'] ?? null,
                    'latitude'        => $lat,
                    'longitude'       => $lng,
                    'currency_code'   => $currencyCode,
                    'currency_name'   => $currencyName,
                    'currency_symbol' => $currencySymbol,
                    'flag_emoji'      => $c['flag'] ?? null,
                    'area'            => $c['area'] ?? null,
                    'languages'       => $languagesStr,
                ]
            );
            $inserted++;
        }

        $this->command->info("Inserted/Updated {$inserted} countries.");
    }

    private function seedFallback(): void
    {
        $data = [
            ['name'=>'United States','iso2'=>'US','iso3'=>'USA','capital'=>'Washington D.C.','region'=>'Americas','subregion'=>'Northern America','population'=>331002651,'latitude'=>38.0,'longitude'=>-97.0,'currency_code'=>'USD','currency_name'=>'United States dollar','currency_symbol'=>'$','flag_emoji'=>'🇺🇸','area'=>9833517,'languages'=>'English'],
            ['name'=>'China','iso2'=>'CN','iso3'=>'CHN','capital'=>'Beijing','region'=>'Asia','subregion'=>'Eastern Asia','population'=>1439323776,'latitude'=>35.0,'longitude'=>105.0,'currency_code'=>'CNY','currency_name'=>'Chinese yuan','currency_symbol'=>'¥','flag_emoji'=>'🇨🇳','area'=>9706961,'languages'=>'Chinese'],
            ['name'=>'Germany','iso2'=>'DE','iso3'=>'DEU','capital'=>'Berlin','region'=>'Europe','subregion'=>'Western Europe','population'=>83783942,'latitude'=>51.0,'longitude'=>9.0,'currency_code'=>'EUR','currency_name'=>'Euro','currency_symbol'=>'€','flag_emoji'=>'🇩🇪','area'=>357114,'languages'=>'German'],
            ['name'=>'Japan','iso2'=>'JP','iso3'=>'JPN','capital'=>'Tokyo','region'=>'Asia','subregion'=>'Eastern Asia','population'=>126476461,'latitude'=>36.0,'longitude'=>138.0,'currency_code'=>'JPY','currency_name'=>'Japanese yen','currency_symbol'=>'¥','flag_emoji'=>'🇯🇵','area'=>377975,'languages'=>'Japanese'],
            ['name'=>'United Kingdom','iso2'=>'GB','iso3'=>'GBR','capital'=>'London','region'=>'Europe','subregion'=>'Northern Europe','population'=>67886011,'latitude'=>54.0,'longitude'=>-2.0,'currency_code'=>'GBP','currency_name'=>'British pound','currency_symbol'=>'£','flag_emoji'=>'🇬🇧','area'=>242900,'languages'=>'English'],
            ['name'=>'France','iso2'=>'FR','iso3'=>'FRA','capital'=>'Paris','region'=>'Europe','subregion'=>'Western Europe','population'=>65273511,'latitude'=>46.0,'longitude'=>2.0,'currency_code'=>'EUR','currency_name'=>'Euro','currency_symbol'=>'€','flag_emoji'=>'🇫🇷','area'=>640679,'languages'=>'French'],
            ['name'=>'India','iso2'=>'IN','iso3'=>'IND','capital'=>'New Delhi','region'=>'Asia','subregion'=>'Southern Asia','population'=>1380004385,'latitude'=>20.0,'longitude'=>77.0,'currency_code'=>'INR','currency_name'=>'Indian rupee','currency_symbol'=>'₹','flag_emoji'=>'🇮🇳','area'=>3287590,'languages'=>'Hindi, English'],
            ['name'=>'Brazil','iso2'=>'BR','iso3'=>'BRA','capital'=>'Brasília','region'=>'Americas','subregion'=>'South America','population'=>212559417,'latitude'=>-10.0,'longitude'=>-55.0,'currency_code'=>'BRL','currency_name'=>'Brazilian real','currency_symbol'=>'R$','flag_emoji'=>'🇧🇷','area'=>8515767,'languages'=>'Portuguese'],
            ['name'=>'Canada','iso2'=>'CA','iso3'=>'CAN','capital'=>'Ottawa','region'=>'Americas','subregion'=>'Northern America','population'=>37742154,'latitude'=>60.0,'longitude'=>-95.0,'currency_code'=>'CAD','currency_name'=>'Canadian dollar','currency_symbol'=>'$','flag_emoji'=>'🇨🇦','area'=>9984670,'languages'=>'English, French'],
            ['name'=>'Australia','iso2'=>'AU','iso3'=>'AUS','capital'=>'Canberra','region'=>'Oceania','subregion'=>'Australia and New Zealand','population'=>25499884,'latitude'=>-25.0,'longitude'=>133.0,'currency_code'=>'AUD','currency_name'=>'Australian dollar','currency_symbol'=>'$','flag_emoji'=>'🇦🇺','area'=>7692024,'languages'=>'English'],
            ['name'=>'Russia','iso2'=>'RU','iso3'=>'RUS','capital'=>'Moscow','region'=>'Europe','subregion'=>'Eastern Europe','population'=>145934462,'latitude'=>60.0,'longitude'=>100.0,'currency_code'=>'RUB','currency_name'=>'Russian ruble','currency_symbol'=>'₽','flag_emoji'=>'🇷🇺','area'=>17098242,'languages'=>'Russian'],
            ['name'=>'South Korea','iso2'=>'KR','iso3'=>'KOR','capital'=>'Seoul','region'=>'Asia','subregion'=>'Eastern Asia','population'=>51269185,'latitude'=>37.0,'longitude'=>127.5,'currency_code'=>'KRW','currency_name'=>'South Korean won','currency_symbol'=>'₩','flag_emoji'=>'🇰🇷','area'=>100210,'languages'=>'Korean'],
            ['name'=>'Mexico','iso2'=>'MX','iso3'=>'MEX','capital'=>'Mexico City','region'=>'Americas','subregion'=>'North America','population'=>128932753,'latitude'=>23.0,'longitude'=>-102.0,'currency_code'=>'MXN','currency_name'=>'Mexican peso','currency_symbol'=>'$','flag_emoji'=>'🇲🇽','area'=>1964375,'languages'=>'Spanish'],
            ['name'=>'Indonesia','iso2'=>'ID','iso3'=>'IDN','capital'=>'Jakarta','region'=>'Asia','subregion'=>'South-Eastern Asia','population'=>273523615,'latitude'=>-5.0,'longitude'=>120.0,'currency_code'=>'IDR','currency_name'=>'Indonesian rupiah','currency_symbol'=>'Rp','flag_emoji'=>'🇮🇩','area'=>1904569,'languages'=>'Indonesian'],
            ['name'=>'Netherlands','iso2'=>'NL','iso3'=>'NLD','capital'=>'Amsterdam','region'=>'Europe','subregion'=>'Western Europe','population'=>17134872,'latitude'=>52.5,'longitude'=>5.75,'currency_code'=>'EUR','currency_name'=>'Euro','currency_symbol'=>'€','flag_emoji'=>'🇳🇱','area'=>41850,'languages'=>'Dutch'],
            ['name'=>'Saudi Arabia','iso2'=>'SA','iso3'=>'SAU','capital'=>'Riyadh','region'=>'Asia','subregion'=>'Western Asia','population'=>34813871,'latitude'=>25.0,'longitude'=>45.0,'currency_code'=>'SAR','currency_name'=>'Saudi riyal','currency_symbol'=>'ر.س','flag_emoji'=>'🇸🇦','area'=>2149690,'languages'=>'Arabic'],
            ['name'=>'Turkey','iso2'=>'TR','iso3'=>'TUR','capital'=>'Ankara','region'=>'Asia','subregion'=>'Western Asia','population'=>84339067,'latitude'=>39.0,'longitude'=>35.0,'currency_code'=>'TRY','currency_name'=>'Turkish lira','currency_symbol'=>'₺','flag_emoji'=>'🇹🇷','area'=>783562,'languages'=>'Turkish'],
            ['name'=>'Switzerland','iso2'=>'CH','iso3'=>'CHE','capital'=>'Bern','region'=>'Europe','subregion'=>'Western Europe','population'=>8654622,'latitude'=>47.0,'longitude'=>8.0,'currency_code'=>'CHF','currency_name'=>'Swiss franc','currency_symbol'=>'Fr','flag_emoji'=>'🇨🇭','area'=>41285,'languages'=>'German, French, Italian, Romansh'],
            ['name'=>'Argentina','iso2'=>'AR','iso3'=>'ARG','capital'=>'Buenos Aires','region'=>'Americas','subregion'=>'South America','population'=>45195777,'latitude'=>-34.0,'longitude'=>-64.0,'currency_code'=>'ARS','currency_name'=>'Argentine peso','currency_symbol'=>'$','flag_emoji'=>'🇦🇷','area'=>2780400,'languages'=>'Spanish'],
            ['name'=>'Poland','iso2'=>'PL','iso3'=>'POL','capital'=>'Warsaw','region'=>'Europe','subregion'=>'Eastern Europe','population'=>37950802,'latitude'=>52.0,'longitude'=>20.0,'currency_code'=>'PLN','currency_name'=>'Polish złoty','currency_symbol'=>'zł','flag_emoji'=>'🇵🇱','area'=>312696,'languages'=>'Polish'],
            ['name'=>'Sweden','iso2'=>'SE','iso3'=>'SWE','capital'=>'Stockholm','region'=>'Europe','subregion'=>'Northern Europe','population'=>10099265,'latitude'=>62.0,'longitude'=>15.0,'currency_code'=>'SEK','currency_name'=>'Swedish krona','currency_symbol'=>'kr','flag_emoji'=>'🇸🇪','area'=>450295,'languages'=>'Swedish'],
            ['name'=>'Belgium','iso2'=>'BE','iso3'=>'BEL','capital'=>'Brussels','region'=>'Europe','subregion'=>'Western Europe','population'=>11589623,'latitude'=>50.83,'longitude'=>4.0,'currency_code'=>'EUR','currency_name'=>'Euro','currency_symbol'=>'€','flag_emoji'=>'🇧🇪','area'=>30528,'languages'=>'Dutch, French, German'],
            ['name'=>'Thailand','iso2'=>'TH','iso3'=>'THA','capital'=>'Bangkok','region'=>'Asia','subregion'=>'South-Eastern Asia','population'=>69799978,'latitude'=>15.0,'longitude'=>100.0,'currency_code'=>'THB','currency_name'=>'Thai baht','currency_symbol'=>'฿','flag_emoji'=>'🇹🇭','area'=>513120,'languages'=>'Thai'],
            ['name'=>'Malaysia','iso2'=>'MY','iso3'=>'MYS','capital'=>'Kuala Lumpur','region'=>'Asia','subregion'=>'South-Eastern Asia','population'=>32365999,'latitude'=>2.5,'longitude'=>112.5,'currency_code'=>'MYR','currency_name'=>'Malaysian ringgit','currency_symbol'=>'RM','flag_emoji'=>'🇲🇾','area'=>329847,'languages'=>'Malay'],
            ['name'=>'Singapore','iso2'=>'SG','iso3'=>'SGP','capital'=>'Singapore','region'=>'Asia','subregion'=>'South-Eastern Asia','population'=>5850342,'latitude'=>1.3667,'longitude'=>103.8,'currency_code'=>'SGD','currency_name'=>'Singapore dollar','currency_symbol'=>'S$','flag_emoji'=>'🇸🇬','area'=>710,'languages'=>'English, Malay, Mandarin, Tamil'],
            ['name'=>'United Arab Emirates','iso2'=>'AE','iso3'=>'ARE','capital'=>'Abu Dhabi','region'=>'Asia','subregion'=>'Western Asia','population'=>9890402,'latitude'=>24.0,'longitude'=>54.0,'currency_code'=>'AED','currency_name'=>'United Arab Emirates dirham','currency_symbol'=>'د.إ','flag_emoji'=>'🇦🇪','area'=>83600,'languages'=>'Arabic'],
            ['name'=>'South Africa','iso2'=>'ZA','iso3'=>'ZAF','capital'=>'Pretoria','region'=>'Africa','subregion'=>'Southern Africa','population'=>59308690,'latitude'=>-29.0,'longitude'=>25.0,'currency_code'=>'ZAR','currency_name'=>'South African rand','currency_symbol'=>'R','flag_emoji'=>'🇿🇦','area'=>1221037,'languages'=>'Zulu, Xhosa, Afrikaans, English'],
            ['name'=>'Nigeria','iso2'=>'NG','iso3'=>'NGA','capital'=>'Abuja','region'=>'Africa','subregion'=>'Western Africa','population'=>206139589,'latitude'=>10.0,'longitude'=>8.0,'currency_code'=>'NGN','currency_name'=>'Nigerian naira','currency_symbol'=>'₦','flag_emoji'=>'🇳🇬','area'=>923768,'languages'=>'English'],
            ['name'=>'Egypt','iso2'=>'EG','iso3'=>'EGY','capital'=>'Cairo','region'=>'Africa','subregion'=>'Northern Africa','population'=>102334404,'latitude'=>27.0,'longitude'=>30.0,'currency_code'=>'EGP','currency_name'=>'Egyptian pound','currency_symbol'=>'£','flag_emoji'=>'🇪🇬','area'=>1002450,'languages'=>'Arabic'],
            ['name'=>'Vietnam','iso2'=>'VN','iso3'=>'VNM','capital'=>'Hanoi','region'=>'Asia','subregion'=>'South-Eastern Asia','population'=>97338579,'latitude'=>16.0,'longitude'=>106.0,'currency_code'=>'VND','currency_name'=>'Vietnamese đồng','currency_symbol'=>'₫','flag_emoji'=>'🇻🇳','area'=>331212,'languages'=>'Vietnamese'],
            ['name'=>'Philippines','iso2'=>'PH','iso3'=>'PHL','capital'=>'Manila','region'=>'Asia','subregion'=>'South-Eastern Asia','population'=>109581078,'latitude'=>13.0,'longitude'=>122.0,'currency_code'=>'PHP','currency_name'=>'Philippine peso','currency_symbol'=>'₱','flag_emoji'=>'🇵🇭','area'=>300000,'languages'=>'Filipino, English'],
            ['name'=>'Pakistan','iso2'=>'PK','iso3'=>'PAK','capital'=>'Islamabad','region'=>'Asia','subregion'=>'Southern Asia','population'=>220892340,'latitude'=>30.0,'longitude'=>70.0,'currency_code'=>'PKR','currency_name'=>'Pakistani rupee','currency_symbol'=>'₨','flag_emoji'=>'🇵🇰','area'=>796095,'languages'=>'Urdu, English'],
            ['name'=>'Bangladesh','iso2'=>'BD','iso3'=>'BGD','capital'=>'Dhaka','region'=>'Asia','subregion'=>'Southern Asia','population'=>164689383,'latitude'=>24.0,'longitude'=>90.0,'currency_code'=>'BDT','currency_name'=>'Bangladeshi taka','currency_symbol'=>'৳','flag_emoji'=>'🇧🇩','area'=>147570,'languages'=>'Bengali'],
            ['name'=>'Greece','iso2'=>'GR','iso3'=>'GRC','capital'=>'Athens','region'=>'Europe','subregion'=>'Southern Europe','population'=>10718565,'latitude'=>39.0,'longitude'=>22.0,'currency_code'=>'EUR','currency_name'=>'Euro','currency_symbol'=>'€','flag_emoji'=>'🇬🇷','area'=>131957,'languages'=>'Greek'],
            ['name'=>'Portugal','iso2'=>'PT','iso3'=>'PRT','capital'=>'Lisbon','region'=>'Europe','subregion'=>'Southern Europe','population'=>10196709,'latitude'=>39.5,'longitude'=>-8.0,'currency_code'=>'EUR','currency_name'=>'Euro','currency_symbol'=>'€','flag_emoji'=>'🇵🇹','area'=>92090,'languages'=>'Portuguese'],
            ['name'=>'Spain','iso2'=>'ES','iso3'=>'ESP','capital'=>'Madrid','region'=>'Europe','subregion'=>'Southern Europe','population'=>46754778,'latitude'=>40.0,'longitude'=>-4.0,'currency_code'=>'EUR','currency_name'=>'Euro','currency_symbol'=>'€','flag_emoji'=>'🇪🇸','area'=>505992,'languages'=>'Spanish'],
            ['name'=>'Italy','iso2'=>'IT','iso3'=>'ITA','capital'=>'Rome','region'=>'Europe','subregion'=>'Southern Europe','population'=>60461826,'latitude'=>42.83,'longitude'=>12.83,'currency_code'=>'EUR','currency_name'=>'Euro','currency_symbol'=>'€','flag_emoji'=>'🇮🇹','area'=>301340,'languages'=>'Italian'],
            ['name'=>'New Zealand','iso2'=>'NZ','iso3'=>'NZL','capital'=>'Wellington','region'=>'Oceania','subregion'=>'Australia and New Zealand','population'=>5084300,'latitude'=>-41.0,'longitude'=>174.0,'currency_code'=>'NZD','currency_name'=>'New Zealand dollar','currency_symbol'=>'$','flag_emoji'=>'🇳🇿','area'=>268021,'languages'=>'English, Māori'],
            ['name'=>'Colombia','iso2'=>'CO','iso3'=>'COL','capital'=>'Bogotá','region'=>'Americas','subregion'=>'South America','population'=>50882891,'latitude'=>4.0,'longitude'=>-72.0,'currency_code'=>'COP','currency_name'=>'Colombian peso','currency_symbol'=>'$','flag_emoji'=>'🇨🇴','area'=>1141748,'languages'=>'Spanish'],
            ['name'=>'Chile','iso2'=>'CL','iso3'=>'CHL','capital'=>'Santiago','region'=>'Americas','subregion'=>'South America','population'=>19116201,'latitude'=>-30.0,'longitude'=>-71.0,'currency_code'=>'CLP','currency_name'=>'Chilean peso','currency_symbol'=>'$','flag_emoji'=>'🇨🇱','area'=>756102,'languages'=>'Spanish'],
            ['name'=>'Kenya','iso2'=>'KE','iso3'=>'KEN','capital'=>'Nairobi','region'=>'Africa','subregion'=>'Eastern Africa','population'=>53771296,'latitude'=>1.0,'longitude'=>38.0,'currency_code'=>'KES','currency_name'=>'Kenyan shilling','currency_symbol'=>'KSh','flag_emoji'=>'🇰🇪','area'=>580367,'languages'=>'English, Swahili'],
            ['name'=>'Ethiopia','iso2'=>'ET','iso3'=>'ETH','capital'=>'Addis Ababa','region'=>'Africa','subregion'=>'Eastern Africa','population'=>114963588,'latitude'=>8.0,'longitude'=>38.0,'currency_code'=>'ETB','currency_name'=>'Ethiopian birr','currency_symbol'=>'Br','flag_emoji'=>'🇪🇹','area'=>1104300,'languages'=>'Amharic'],
            ['name'=>'Morocco','iso2'=>'MA','iso3'=>'MAR','capital'=>'Rabat','region'=>'Africa','subregion'=>'Northern Africa','population'=>36910560,'latitude'=>32.0,'longitude'=>-5.0,'currency_code'=>'MAD','currency_name'=>'Moroccan dirham','currency_symbol'=>'MAD','flag_emoji'=>'🇲🇦','area'=>446550,'languages'=>'Arabic, Berber'],
            ['name'=>'Ukraine','iso2'=>'UA','iso3'=>'UKR','capital'=>'Kyiv','region'=>'Europe','subregion'=>'Eastern Europe','population'=>43733762,'latitude'=>49.0,'longitude'=>32.0,'currency_code'=>'UAH','currency_name'=>'Ukrainian hryvnia','currency_symbol'=>'₴','flag_emoji'=>'🇺🇦','area'=>603500,'languages'=>'Ukrainian'],
            ['name'=>'Romania','iso2'=>'RO','iso3'=>'ROU','capital'=>'Bucharest','region'=>'Europe','subregion'=>'Eastern Europe','population'=>19237691,'latitude'=>46.0,'longitude'=>25.0,'currency_code'=>'RON','currency_name'=>'Romanian leu','currency_symbol'=>'lei','flag_emoji'=>'🇷🇴','area'=>238397,'languages'=>'Romanian'],
            ['name'=>'Czech Republic','iso2'=>'CZ','iso3'=>'CZE','capital'=>'Prague','region'=>'Europe','subregion'=>'Eastern Europe','population'=>10708981,'latitude'=>49.75,'longitude'=>15.5,'currency_code'=>'CZK','currency_name'=>'Czech koruna','currency_symbol'=>'Kč','flag_emoji'=>'🇨🇿','area'=>78865,'languages'=>'Czech'],
            ['name'=>'Denmark','iso2'=>'DK','iso3'=>'DNK','capital'=>'Copenhagen','region'=>'Europe','subregion'=>'Northern Europe','population'=>5792202,'latitude'=>56.0,'longitude'=>10.0,'currency_code'=>'DKK','currency_name'=>'Danish krone','currency_symbol'=>'kr','flag_emoji'=>'🇩🇰','area'=>43094,'languages'=>'Danish'],
            ['name'=>'Norway','iso2'=>'NO','iso3'=>'NOR','capital'=>'Oslo','region'=>'Europe','subregion'=>'Northern Europe','population'=>5421241,'latitude'=>62.0,'longitude'=>10.0,'currency_code'=>'NOK','currency_name'=>'Norwegian krone','currency_symbol'=>'kr','flag_emoji'=>'🇳🇴','area'=>323802,'languages'=>'Norwegian'],
            ['name'=>'Finland','iso2'=>'FI','iso3'=>'FIN','capital'=>'Helsinki','region'=>'Europe','subregion'=>'Northern Europe','population'=>5540720,'latitude'=>64.0,'longitude'=>26.0,'currency_code'=>'EUR','currency_name'=>'Euro','currency_symbol'=>'€','flag_emoji'=>'🇫🇮','area'=>338424,'languages'=>'Finnish, Swedish'],
            ['name'=>'Iran','iso2'=>'IR','iso3'=>'IRN','capital'=>'Tehran','region'=>'Asia','subregion'=>'Southern Asia','population'=>83992949,'latitude'=>32.0,'longitude'=>53.0,'currency_code'=>'IRR','currency_name'=>'Iranian rial','currency_symbol'=>'﷼','flag_emoji'=>'🇮🇷','area'=>1648195,'languages'=>'Persian'],
            ['name'=>'Iraq','iso2'=>'IQ','iso3'=>'IRQ','capital'=>'Baghdad','region'=>'Asia','subregion'=>'Western Asia','population'=>40222493,'latitude'=>33.0,'longitude'=>44.0,'currency_code'=>'IQD','currency_name'=>'Iraqi dinar','currency_symbol'=>'ع.d','flag_emoji'=>'🇮🇶','area'=>438317,'languages'=>'Arabic, Kurdish'],
            ['name'=>'Israel','iso2'=>'IL','iso3'=>'ISR','capital'=>'Jerusalem','region'=>'Asia','subregion'=>'Western Asia','population'=>9216900,'latitude'=>31.5,'longitude'=>34.75,'currency_code'=>'ILS','currency_name'=>'Israeli new shekel','currency_symbol'=>'₪','flag_emoji'=>'🇮🇱','area'=>20770,'languages'=>'Hebrew, Arabic'],
            ['name'=>'Hong Kong','iso2'=>'HK','iso3'=>'HKG','capital'=>'Hong Kong','region'=>'Asia','subregion'=>'Eastern Asia','population'=>7496988,'latitude'=>22.267,'longitude'=>114.188,'currency_code'=>'HKD','currency_name'=>'Hong Kong dollar','currency_symbol'=>'$','flag_emoji'=>'🇭🇰','area'=>1104,'languages'=>'Chinese, English'],
            ['name'=>'Taiwan','iso2'=>'TW','iso3'=>'TWN','capital'=>'Taipei','region'=>'Asia','subregion'=>'Eastern Asia','population'=>23573876,'latitude'=>23.5,'longitude'=>121.0,'currency_code'=>'TWD','currency_name'=>'New Taiwan dollar','currency_symbol'=>'NT$','flag_emoji'=>'🇹🇼','area'=>36193,'languages'=>'Chinese'],
            ['name'=>'Sri Lanka','iso2'=>'LK','iso3'=>'LKA','capital'=>'Sri Jayawardenepura Kotte','region'=>'Asia','subregion'=>'Southern Asia','population'=>21919000,'latitude'=>7.0,'longitude'=>81.0,'currency_code'=>'LKR','currency_name'=>'Sri Lankan rupee','currency_symbol'=>'Rs','flag_emoji'=>'🇱🇰','area'=>65610,'languages'=>'Sinhala, Tamil'],
            ['name'=>'Myanmar','iso2'=>'MM','iso3'=>'MMR','capital'=>'Naypyidaw','region'=>'Asia','subregion'=>'South-Eastern Asia','population'=>54409800,'latitude'=>22.0,'longitude'=>98.0,'currency_code'=>'MMK','currency_name'=>'Burmese kyat','currency_symbol'=>'K','flag_emoji'=>'🇲🇲','area'=>676578,'languages'=>'Burmese'],
            ['name'=>'Ghana','iso2'=>'GH','iso3'=>'GHA','capital'=>'Accra','region'=>'Africa','subregion'=>'Western Africa','population'=>31072940,'latitude'=>8.0,'longitude'=>-2.0,'currency_code'=>'GHS','currency_name'=>'Ghanaian cedi','currency_symbol'=>'₵','flag_emoji'=>'🇬🇭','area'=>238533,'languages'=>'English'],
            ['name'=>'Tanzania','iso2'=>'TZ','iso3'=>'TZA','capital'=>'Dodoma','region'=>'Africa','subregion'=>'Eastern Africa','population'=>59734218,'latitude'=>-6.0,'longitude'=>35.0,'currency_code'=>'TZS','currency_name'=>'Tanzanian shilling','currency_symbol'=>'Sh','flag_emoji'=>'🇹🇿','area'=>947303,'languages'=>'Swahili, English'],
            ['name'=>'Peru','iso2'=>'PE','iso3'=>'PER','capital'=>'Lima','region'=>'Americas','subregion'=>'South America','population'=>32971854,'latitude'=>-10.0,'longitude'=>-76.0,'currency_code'=>'PEN','currency_name'=>'Peruvian sol','currency_symbol'=>'S/.','flag_emoji'=>'🇵🇪','area'=>1285216,'languages'=>'Spanish'],
            ['name'=>'Kazakhstan','iso2'=>'KZ','iso3'=>'KAZ','capital'=>'Nur-Sultan','region'=>'Asia','subregion'=>'Central Asia','population'=>18776707,'latitude'=>48.0,'longitude'=>68.0,'currency_code'=>'KZT','currency_name'=>'Kazakhstani tenge','currency_symbol'=>'₸','flag_emoji'=>'🇰🇿','area'=>2724900,'languages'=>'Kazakh, Russian'],
        ];

        $count = 0;
        foreach ($data as $c) {
            Country::updateOrCreate(['iso3' => $c['iso3']], $c);
            $count++;
        }
        $this->command->info("Seeded {$count} countries from local fallback data.");
    }
}
