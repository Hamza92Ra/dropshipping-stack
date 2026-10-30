// tools/build-assets.mjs
import { execSync } from 'child_process';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const rootDir = path.dirname(__dirname);
const distDir = path.join(rootDir, 'assets', 'dist');

if (!fs.existsSync(distDir)) {
  fs.mkdirSync(distDir, { recursive: true });
}

console.log('=== Building Minified Assets via esbuild ===');

const cssFiles = ['theme.css', 'style.css', 'mobile.css'];
const jsFiles = ['main.js', 'theme.js', 'chat-widget.js', 'bookmarks.js', 'price-alert.js', 'darkmode.js'];

let totalOrigBytes = 0;
let totalMinBytes = 0;

for (const file of cssFiles) {
  const src = path.join(rootDir, 'assets', 'css', file);
  if (!fs.existsSync(src)) continue;
  const base = path.basename(file, '.css');
  const out = path.join(distDir, `${base}.min.css`);
  
  execSync(`npx esbuild "${src}" --minify --outfile="${out}"`, { stdio: 'inherit' });
  const origSz = fs.statSync(src).size;
  const minSz = fs.statSync(out).size;
  totalOrigBytes += origSz;
  totalMinBytes += minSz;
  console.log(`CSS: ${file.padEnd(16)} ${(origSz / 1024).toFixed(2).padStart(7)} KB -> ${(minSz / 1024).toFixed(2).padStart(7)} KB (saved ${((1 - minSz / origSz) * 100).toFixed(1)}%)`);
}

for (const file of jsFiles) {
  const src = path.join(rootDir, 'assets', 'js', file);
  if (!fs.existsSync(src)) continue;
  const base = path.basename(file, '.js');
  const out = path.join(distDir, `${base}.min.js`);
  
  execSync(`npx esbuild "${src}" --minify --outfile="${out}"`, { stdio: 'inherit' });
  const origSz = fs.statSync(src).size;
  const minSz = fs.statSync(out).size;
  totalOrigBytes += origSz;
  totalMinBytes += minSz;
  console.log(`JS:  ${file.padEnd(16)} ${(origSz / 1024).toFixed(2).padStart(7)} KB -> ${(minSz / 1024).toFixed(2).padStart(7)} KB (saved ${((1 - minSz / origSz) * 100).toFixed(1)}%)`);
}

console.log('============================================');
console.log(`Total original: ${(totalOrigBytes / 1024).toFixed(2)} KB`);
console.log(`Total minified: ${(totalMinBytes / 1024).toFixed(2)} KB (Overall saved ${((1 - totalMinBytes / totalOrigBytes) * 100).toFixed(1)}%)`);
console.log('Dist assets built into assets/dist/');
