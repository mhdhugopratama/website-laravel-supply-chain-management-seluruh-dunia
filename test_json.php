<?php
$json = json_decode(file_get_contents('C:\laragon\www\global_supply_chain\database\seeders\raw_countries.json'), true);
$missingPop = 0;
$missingArea = 0;
foreach($json as $c) {
    if(empty($c['population'])) $missingPop++;
    if(empty($c['area'])) $missingArea++;
}
echo "No pop: $missingPop, No area: $missingArea\n";
