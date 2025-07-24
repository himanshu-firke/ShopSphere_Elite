import fs from 'fs';
import https from 'https';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const productImages = [
    {
        name: 'sofa-1.jpg',
        url: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=800&q=80'
    },
    {
        name: 'sofa-2.jpg',
        url: 'https://images.unsplash.com/photo-1550254478-ead40cc54513?w=800&q=80'
    },
    {
        name: 'bed-1.jpg',
        url: 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=800&q=80'
    },
    {
        name: 'bed-2.jpg',
        url: 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=800&q=80'
    },
    {
        name: 'dining-1.jpg',
        url: 'https://images.unsplash.com/photo-1617098900591-3f90928e8c54?w=800&q=80'
    },
    {
        name: 'dining-2.jpg',
        url: 'https://images.unsplash.com/photo-1616627547584-bf28cee262db?w=800&q=80'
    },
    {
        name: 'wardrobe-1.jpg',
        url: 'https://images.unsplash.com/photo-1631679706909-1844bbd07221?w=800&q=80'
    },
    {
        name: 'wardrobe-2.jpg',
        url: 'https://images.unsplash.com/photo-1595428774223-ef52624120d2?w=800&q=80'
    },
    {
        name: 'desk-1.jpg',
        url: 'https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd?w=800&q=80'
    },
    {
        name: 'desk-2.jpg',
        url: 'https://images.unsplash.com/photo-1611269154421-4e27233ac5c7?w=800&q=80'
    }
];

const downloadImage = (url, filename) => {
    return new Promise((resolve, reject) => {
        const targetDir = path.join(__dirname, 'public/images/products');
        if (!fs.existsSync(targetDir)) {
            fs.mkdirSync(targetDir, { recursive: true });
        }

        const filepath = path.join(targetDir, filename);
        const file = fs.createWriteStream(filepath);

        https.get(url, (response) => {
            response.pipe(file);
            file.on('finish', () => {
                file.close();
                console.log(`Downloaded: ${filename}`);
                resolve();
            });
        }).on('error', (err) => {
            fs.unlink(filepath, () => {});
            reject(err);
        });
    });
};

const downloadAll = async () => {
    for (const image of productImages) {
        try {
            await downloadImage(image.url, image.name);
        } catch (error) {
            console.error(`Error downloading ${image.name}:`, error);
        }
    }
};

downloadAll(); 