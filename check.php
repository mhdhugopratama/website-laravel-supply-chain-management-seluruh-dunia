<?php
$j = json_decode(file_get_contents('database/seeders/raw_ports.json'), true);
print_r($j['IDBNT']);
