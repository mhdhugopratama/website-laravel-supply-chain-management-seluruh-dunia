<?php
$id = include('lang/id/app.php');
$en = include('lang/en/app.php');

$id['dashboard']['track_globally'] = 'Terlacak Global';
$id['dashboard']['sea_ports_mapped'] = 'Pelabuhan Dipetakan';
$id['dashboard']['weather_stations'] = 'Stasiun Cuaca';
$id['dashboard']['live_readings'] = 'Pembacaan Langsung';
$id['dashboard']['realtime_feed'] = 'Umpan Waktu Nyata';
$id['dashboard']['global_maps'] = 'Peta Intelijen Global';
$id['dashboard']['data_updated'] = 'Data diperbarui setiap 30 mnt';
$id['dashboard']['click_marker'] = 'Klik penanda untuk detail';
$id['dashboard']['risk_map'] = 'Risiko Rantai Pasok';
$id['dashboard']['route_tracker'] = 'Pelacak Rute';
$id['dashboard']['live_weather_map'] = 'Cuaca Langsung';
$id['dashboard']['port_map'] = 'Distribusi Pelabuhan';
$id['dashboard']['origin_country'] = '-- Negara Asal --';
$id['dashboard']['dest_country'] = '-- Negara Tujuan --';
$id['dashboard']['analyze_route'] = 'Analisis Rute';
$id['dashboard']['route_tracking'] = 'Pelacakan Rute';
$id['dashboard']['route_est'] = 'Mengestimasi risiko pengiriman lintas zona geopolitik';
$id['dashboard']['regional_coverage'] = 'Cakupan Regional';

$en['dashboard']['track_globally'] = 'Tracked Globally';
$en['dashboard']['sea_ports_mapped'] = 'Sea Ports Mapped';
$en['dashboard']['weather_stations'] = 'Weather Stations';
$en['dashboard']['live_readings'] = 'Live Readings';
$en['dashboard']['realtime_feed'] = 'Real-time Feed';
$en['dashboard']['global_maps'] = 'Global Intelligence Maps';
$en['dashboard']['data_updated'] = 'Data updated every 30 min';
$en['dashboard']['click_marker'] = 'Click any marker for details';
$en['dashboard']['risk_map'] = 'Supply Chain Risk';
$en['dashboard']['route_tracker'] = 'Route Tracker';
$en['dashboard']['live_weather_map'] = 'Live Weather';
$en['dashboard']['port_map'] = 'Port Distribution';
$en['dashboard']['origin_country'] = '-- Origin Country --';
$en['dashboard']['dest_country'] = '-- Destination Country --';
$en['dashboard']['analyze_route'] = 'Analyze Route';
$en['dashboard']['route_tracking'] = 'Route Tracking';
$en['dashboard']['route_est'] = 'Estimates delivery risk across geopolitical zones';
$en['dashboard']['regional_coverage'] = 'Regional Coverage';

$id['news']['global_trade_intel'] = 'Intelijen Perdagangan Global';
$id['news']['global_trade_desc'] = 'Pembaruan ekonomi, geopolitik, dan logistik langsung untuk pengambilan keputusan.';
$id['news']['cached_result'] = 'Hasil Tembolok';
$id['news']['live_stream'] = 'Siaran Langsung';
$id['news']['intel_filters'] = 'Filter Intelijen';
$id['news']['analyze_btn'] = 'Analisis';
$id['news']['categories'] = 'Kategori:';
$id['news']['market_sentiment'] = 'Analisis Sentimen Pasar';
$id['news']['pos_growth'] = 'Positif/Tumbuh';
$id['news']['neutral'] = 'Netral';
$id['news']['risk_neg'] = 'Risiko/Negatif';
$id['news']['intel_briefs'] = 'Ringkasan Intelijen';
$id['news']['read_full'] = 'Baca Artikel Lengkap';
$id['news']['no_intel'] = 'Data intelijen tidak ditemukan';
$id['news']['try_adjusting'] = 'Coba sesuaikan filter pencarian atau perluas ruang lingkup.';

$en['news']['global_trade_intel'] = 'Global Trade Intelligence';
$en['news']['global_trade_desc'] = 'Live economic, geopolitical, and logistics updates for decision-making.';
$en['news']['cached_result'] = 'Cached Result';
$en['news']['live_stream'] = 'Live Stream';
$en['news']['intel_filters'] = 'Intelligence Filters';
$en['news']['analyze_btn'] = 'Analyze';
$en['news']['categories'] = 'Categories:';
$en['news']['market_sentiment'] = 'Market Sentiment Analysis';
$en['news']['pos_growth'] = 'Positive/Growth';
$en['news']['neutral'] = 'Neutral';
$en['news']['risk_neg'] = 'Risk/Negative';
$en['news']['intel_briefs'] = 'Intelligence Briefs';
$en['news']['read_full'] = 'Read Full Article';
$en['news']['no_intel'] = 'No intelligence data found';
$en['news']['try_adjusting'] = 'Try adjusting your search filters or broadening your scope.';

file_put_contents('lang/id/app.php', "<?php\n\nreturn " . var_export($id, true) . ";\n");
file_put_contents('lang/en/app.php', "<?php\n\nreturn " . var_export($en, true) . ";\n");

echo "Langs updated!";
