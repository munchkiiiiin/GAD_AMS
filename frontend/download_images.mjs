import fs from 'fs/promises';
import { createWriteStream } from 'fs';
import path from 'path';
import https from 'https';

const SRC_DIR = './src';
const IMAGES_DIR = './public/images';

async function downloadImage(url, dest) {
    return new Promise((resolve, reject) => {
        https.get(url, (response) => {
            if (response.statusCode === 200) {
                const file = createWriteStream(dest);
                response.pipe(file);
                file.on('finish', () => {
                    file.close(resolve);
                });
            } else {
                reject(new Error(`Failed to download ${url}: ${response.statusCode}`));
            }
        }).on('error', reject);
    });
}

async function findVueFiles(dir) {
    const files = [];
    const entries = await fs.readdir(dir, { withFileTypes: true });
    for (const entry of entries) {
        const fullPath = path.join(dir, entry.name);
        if (entry.isDirectory()) {
            files.push(...await findVueFiles(fullPath));
        } else if (entry.name.endsWith('.vue')) {
            files.push(fullPath);
        }
    }
    return files;
}

async function run() {
    await fs.mkdir(IMAGES_DIR, { recursive: true });
    const vueFiles = await findVueFiles(SRC_DIR);
    
    let urlMap = new Map();
    let index = 1;

    // 1. Scan and find all unique Google User Content image URLs
    const regex = /https:\/\/lh3\.googleusercontent\.com\/aida-public\/[a-zA-Z0-9_-]+/g;
    
    for (const file of vueFiles) {
        const content = await fs.readFile(file, 'utf-8');
        let match;
        while ((match = regex.exec(content)) !== null) {
            const url = match[0];
            if (!urlMap.has(url)) {
                urlMap.set(url, `img_${index}.jpg`);
                index++;
            }
        }
    }

    console.log(`Found ${urlMap.size} unique images to download.`);

    // 2. Download them
    for (const [url, filename] of urlMap.entries()) {
        const dest = path.join(IMAGES_DIR, filename);
        console.log(`Downloading ${url.substring(0, 50)}... to ${filename}`);
        await downloadImage(url, dest);
    }

    console.log('Done downloading images!');

    // 3. Replace in files
    for (const file of vueFiles) {
        let content = await fs.readFile(file, 'utf-8');
        let modified = false;
        
        for (const [url, filename] of urlMap.entries()) {
            if (content.includes(url)) {
                content = content.split(url).join(`/images/${filename}`);
                modified = true;
            }
        }
        
        if (modified) {
            await fs.writeFile(file, content);
            console.log(`Updated ${file}`);
        }
    }
}

run().catch(console.error);
