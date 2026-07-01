import fs from 'node:fs';
import path from 'node:path';
import archiver from 'archiver';
import pkg from './package.json' with { type: 'json' };

const filename = `mod_ishop_filter-${pkg.version}.zip`;
const outputDirectory = 'build';
const outputPath = path.join(outputDirectory, filename);

fs.mkdirSync(outputDirectory, { recursive: true });

const output = fs.createWriteStream(outputPath);
const archive = archiver('zip', { zlib: { level: 9 } });

archive.pipe(output);

for (const dir of ['language', 'media', 'services', 'src', 'tmpl']) {
  archive.directory(dir, dir);
}

for (const file of ['mod_ishop_filter.xml', 'script.php', 'README.md']) {
  archive.file(file, { name: file });
}

await archive.finalize();

console.log('\n✅ Создан архив для установки! Файл: ' + outputPath);
