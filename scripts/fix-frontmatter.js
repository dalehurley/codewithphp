import fs from 'fs/promises';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const rootDir = path.join(__dirname, '..');

async function fixFrontmatter(filePath) {
  try {
    let content = await fs.readFile(filePath, 'utf-8');
    
    // Fix duplicated chapter numbers in labels (e.g., "01: 01: Title" -> "01: Title")
    content = content.replace(
      /label:\s*(\d+):\s+\1:\s+(.+)/g,
      'label: "$1: $2"'
    );
    
    // Fix unquoted colons in labels
    content = content.replace(
      /label:\s+(\d+):\s+([^\n"]+)/g,
      (match, num, title) => {
        if (!title.startsWith('"')) {
          return `label: "${num}: ${title.trim()}"`;
        }
        return match;
      }
    );
    
    await fs.writeFile(filePath, content, 'utf-8');
    return true;
  } catch (error) {
    console.error(`Error fixing ${filePath}:`, error.message);
    return false;
  }
}

async function processDirectory(dir) {
  const entries = await fs.readdir(dir, { withFileTypes: true });
  let fixed = 0;
  
  for (const entry of entries) {
    const fullPath = path.join(dir, entry.name);
    
    if (entry.isDirectory()) {
      fixed += await processDirectory(fullPath);
    } else if (entry.name.endsWith('.md') || entry.name.endsWith('.mdx')) {
      const success = await fixFrontmatter(fullPath);
      if (success) fixed++;
    }
  }
  
  return fixed;
}

const contentDir = path.join(rootDir, 'src/content/docs');
console.log('Fixing frontmatter issues...\n');

const fixed = await processDirectory(contentDir);
console.log(`\n✓ Fixed frontmatter in ${fixed} files`);
