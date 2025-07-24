import https from 'https';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const images = [
    {
        name: 'living-room.jpg',
        url: 'https://images.unsplash.com/photo-1618220179428-22790b461013?w=800&q=80',
    },
    {
        name: 'bedroom.jpg',
        url: 'https://images.unsplash.com/photo-1540518614846-7eded433c457?w=800&q=80',
    },
    {
        name: 'dining-room.jpg',
        url: 'https://images.unsplash.com/photo-1617806118233-18e1de247200?w=800&q=80',
    },
    {
        name: 'home-office.jpg',
        url: 'https://images.unsplash.com/photo-1486946255434-2466348c2166?w=800&q=80',
    },
    {
        name: 'kitchen.jpg',
        url: 'https://images.unsplash.com/photo-1600489000022-c2086d79f9d4?w=800&q=80',
    },
    {
        name: 'outdoor.jpg',
        url: 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800&q=80',
    },
];

const downloadImage = (url, filename) => {
    return new Promise((resolve, reject) => {
        const filepath = path.join(__dirname, 'public', 'images', 'categories', filename);
        const file = fs.createWriteStream(filepath);

        https.get(url, (response) => {
            response.pipe(file);
            file.on('finish', () => {
                file.close();
                console.log(`Downloaded: ${filename}`);
                resolve();
            });
        }).on('error', (err) => {
            fs.unlink(filepath, () => {
                console.error(`Error downloading ${filename}:`, err.message);
                reject(err);
            });
        });
    });
};

async function downloadAll() {
    try {
        for (const image of images) {
            await downloadImage(image.url, image.name);
        }
        console.log('All images downloaded successfully!');
    } catch (error) {
        console.error('Error downloading images:', error);
    }
}

downloadAll(); 