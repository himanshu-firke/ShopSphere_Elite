import https from 'https';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const images = [
    // Collections
    {
        name: 'milford.jpg',
        url: 'https://images.unsplash.com/photo-1556438064-2d7646166914?w=800&q=80',
        dir: 'collections'
    },
    {
        name: 'mozart.jpg',
        url: 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=800&q=80',
        dir: 'collections'
    },
    {
        name: 'maxsif.jpg',
        url: 'https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?w=800&q=80',
        dir: 'collections'
    },
    // Popular Categories
    {
        name: 'plastic-chairs.jpg',
        url: 'https://images.unsplash.com/photo-1592078615290-033ee584e267?w=800&q=80',
        dir: 'categories'
    },
    {
        name: 'office-chairs.jpg',
        url: 'https://images.unsplash.com/photo-1505797149-0f45b5f0b5bc?w=800&q=80',
        dir: 'categories'
    },
    {
        name: 'recliners.jpg',
        url: 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800&q=80',
        dir: 'categories'
    }
];

const downloadImage = (url, filename, directory) => {
    return new Promise((resolve, reject) => {
        const dir = path.join(__dirname, 'public', 'images', directory);
        if (!fs.existsSync(dir)) {
            fs.mkdirSync(dir, { recursive: true });
        }

        const filepath = path.join(dir, filename);
        const file = fs.createWriteStream(filepath);

        https.get(url, (response) => {
            response.pipe(file);
            file.on('finish', () => {
                file.close();
                console.log(`Downloaded: ${directory}/${filename}`);
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
            await downloadImage(image.url, image.name, image.dir);
        }
        console.log('All images downloaded successfully!');
    } catch (error) {
        console.error('Error downloading images:', error);
    }
}

downloadAll(); 