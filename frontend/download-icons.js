import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// SVG icons content
const icons = {
    'living-room': `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7"/><path d="M21 7H3"/><path d="M18 7V3a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1v4"/></svg>`,
    'dining-room': `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="8" width="18" height="12" rx="2"/><path d="M19 8V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v3"/><line x1="12" y1="12" x2="12" y2="16"/></svg>`,
    'bed-room': `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4v16"/><path d="M2 8h18a2 2 0 0 1 2 2v10"/><path d="M2 17h20"/><path d="M6 8v9"/></svg>`,
    'study-room': `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>`,
    'kids-furniture': `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3L4 9v12h16V9l-8-6z"/><path d="M16 12l-4 4-4-4"/><path d="M12 7v9"/></svg>`,
    'kitchen': `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 3h16a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/><path d="M4 11h16"/><path d="M11 4v16"/></svg>`
};

// Create directories if they don't exist
const iconsDir = path.join(__dirname, 'public', 'images', 'icons');
if (!fs.existsSync(iconsDir)) {
    fs.mkdirSync(iconsDir, { recursive: true });
}

// Save icons
Object.entries(icons).forEach(([name, svg]) => {
    const filePath = path.join(iconsDir, `${name}.svg`);
    fs.writeFileSync(filePath, svg);
    console.log(`Created: ${name}.svg`);
});

console.log('All icons created successfully!'); 