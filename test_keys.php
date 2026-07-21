<?php
$json = json_decode(file_get_contents('C:\laragon\www\global_supply_chain\database\seeders\raw_countries.json'), true);
print_r(array_keys($json[0]));
