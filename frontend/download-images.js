import https from 'https';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const downloadImage = (url, filepath) => {
    return new Promise((resolve, reject) => {
        const request = https.get(url, (response) => {
            // Handle redirects
            if (response.statusCode === 301 || response.statusCode === 302) {
                downloadImage(response.headers.location, filepath)
                    .then(resolve)
                    .catch(reject);
                return;
            }

            if (response.statusCode === 200) {
                response.pipe(fs.createWriteStream(filepath))
                    .on('error', reject)
                    .once('close', () => resolve(filepath));
            } else {
                response.resume();
                reject(new Error(`Request Failed With a Status Code: ${response.statusCode}`));
            }
        });

        request.on('error', reject);
    });
};

const images = {
    banners: [
        {
            name: 'dining-set.jpg',
            url: 'https://images.pexels.com/photos/1080696/pexels-photo-1080696.jpeg'
        },
        {
            name: 'bedroom.jpg',
            url: 'https://images.pexels.com/photos/1454806/pexels-photo-1454806.jpeg'
        },
        {
            name: 'office.jpg',
            url: 'https://images.pexels.com/photos/1957477/pexels-photo-1957477.jpeg'
        }
    ],
    categories: [
        {
            name: 'living-room.jpg',
            url: 'https://images.pexels.com/photos/1571458/pexels-photo-1571458.jpeg'
        },
        {
            name: 'bedroom.jpg',
            url: 'https://images.pexels.com/photos/3773575/pexels-photo-3773575.png'
        },
        {
            name: 'dining-room.jpg',
            url: 'https://images.pexels.com/photos/1395967/pexels-photo-1395967.jpeg'
        },
        {
            name: 'home-office.jpg',
            url: 'https://images.pexels.com/photos/3932930/pexels-photo-3932930.jpeg'
        },
        {
            name: 'kids-room.jpg',
            url: 'https://images.pexels.com/photos/3932957/pexels-photo-3932957.jpeg'
        },
        {
            name: 'premium.jpg',
            url: 'https://images.pexels.com/photos/3932934/pexels-photo-3932934.jpeg'
        }
    ],
    products: [
        {
            name: 'sofa-1.jpg',
            url: 'https://images.pexels.com/photos/1866149/pexels-photo-1866149.jpeg'
        },
        {
            name: 'bed-1.jpg',
            url: 'https://images.pexels.com/photos/3773581/pexels-photo-3773581.png'
        },
        {
            name: 'dining-1.jpg',
            url: 'https://images.pexels.com/photos/3935353/pexels-photo-3935353.jpeg'
        },
        {
            name: 'chair-1.jpg',
            url: 'https://images.pexels.com/photos/3932930/pexels-photo-3932930.jpeg'
        },
        {
            name: 'tv-unit-1.jpg',
            url: 'https://images.pexels.com/photos/3932924/pexels-photo-3932924.jpeg'
        },
        {
            name: 'mattress-1.jpg',
            url: 'https://images.pexels.com/photos/3932940/pexels-photo-3932940.jpeg'
        },
        {
            name: 'wardrobe-1.jpg',
            url: 'https://images.pexels.com/photos/3932926/pexels-photo-3932926.jpeg'
        },
        {
            name: 'study-1.jpg',
            url: 'https://images.pexels.com/photos/3932930/pexels-photo-3932930.jpeg'
        }
    ],
    payment: [
        {
            name: 'visa.png',
            url: 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/800px-Visa_Inc._logo.svg.png'
        },
        {
            name: 'mastercard.png',
            url: 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/800px-Mastercard-logo.svg.png'
        },
        {
            name: 'paypal.png',
            url: 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b5/PayPal.svg/800px-PayPal.svg.png'
        },
        {
            name: 'upi.png',
            url: 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e1/UPI-Logo-vector.svg/800px-UPI-Logo-vector.svg.png'
        }
    ]
};

const downloadImages = async () => {
    const baseDir = path.join(__dirname, 'public', 'images');

    for (const [category, categoryImages] of Object.entries(images)) {
        const categoryDir = path.join(baseDir, category);
        
        // Create directory if it doesn't exist
        if (!fs.existsSync(categoryDir)) {
            fs.mkdirSync(categoryDir, { recursive: true });
        }

        for (const image of categoryImages) {
            const filepath = path.join(categoryDir, image.name);
            try {
                await downloadImage(image.url, filepath);
                console.log(`Downloaded: ${image.name}`);
                // Add a small delay between downloads
                await new Promise(resolve => setTimeout(resolve, 500));
            } catch (error) {
                console.error(`Error downloading ${image.name}:`, error.message);
            }
        }
    }
};

downloadImages().then(() => {
    console.log('All images downloaded successfully!');
}).catch((error) => {
    console.error('Error downloading images:', error);
}); 