import { readFileSync, readdirSync, statSync } from 'node:fs';
import { join, extname } from 'node:path';
import YAML from 'yaml';

const ignored = new Set(['.git', 'vendor', 'node_modules', 'wordpress', 'wp-content/uploads']);
const roots = ['.'];
const files = [];

function walk(dir) {
  for (const name of readdirSync(dir)) {
    const path = join(dir, name);
    const normalized = path.replace(/^\.\//, '');
    if ([...ignored].some((prefix) => normalized === prefix || normalized.startsWith(`${prefix}/`))) {
      continue;
    }
    const stat = statSync(path);
    if (stat.isDirectory()) {
      walk(path);
    } else if (['.json', '.yml', '.yaml'].includes(extname(name))) {
      files.push(path);
    }
  }
}

for (const root of roots) {
  walk(root);
}

for (const file of files) {
  const source = readFileSync(file, 'utf8');
  if (file.endsWith('.json')) {
    JSON.parse(source);
  } else {
    YAML.parse(source);
  }
}

console.log(`Validated ${files.length} JSON/YAML files.`);
