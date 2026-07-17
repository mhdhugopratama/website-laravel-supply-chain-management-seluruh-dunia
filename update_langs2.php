<?php
$id = include('lang/id/app.php');
$en = include('lang/en/app.php');

$id['dashboard']['step1'] = 'Langkah 1: Klik Negara Asal di Peta';
$id['dashboard']['step2'] = 'Langkah 2: Klik Negara Tujuan di Peta';
$id['dashboard']['route_analyzed'] = 'Rute Dianalisis:';
$id['dashboard']['reset'] = 'Ulangi';

$en['dashboard']['step1'] = 'Step 1: Click Origin Country on map';
$en['dashboard']['step2'] = 'Step 2: Click Destination Country on map';
$en['dashboard']['route_analyzed'] = 'Route Analyzed:';
$en['dashboard']['reset'] = 'Reset';

file_put_contents('lang/id/app.php', "<?php\n\nreturn " . var_export($id, true) . ";\n");
file_put_contents('lang/en/app.php', "<?php\n\nreturn " . var_export($en, true) . ";\n");
echo "Done";
