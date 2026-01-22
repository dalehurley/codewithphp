import fs from 'fs/promises';
import path from 'path';
import { fileURLToPath } from 'url';
import yaml from 'js-yaml';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const rootDir = path.join(__dirname, '..');

async function fixYAMLQuotes(filePath) {
  try {
    let content = await fs.readFile(filePath, 'utf-8');
    
    // Extract frontmatter
    const frontmatterMatch = content.match(/^---\n([\s\S]*?)\n---/);
    if (!frontmatterMatch) return false;
    
    const frontmatter = frontmatterMatch[1];
    const restOfContent = content.substring(frontmatterMatch[0].length);
    
    // Parse to validate and fix
    try {
      yaml.load(frontmatter);
      return false; // Already valid
    } catch (e) {
      // Try to fix common issues
      
      // Fix 1: Escape nested quotes in description/title
      let fixed = frontmatter
        .replace(/^(title|description):\s*"([^"]*)"([^"]*)"([^"]*)"/gm, (match, key, before, middle, after) => {
          return `${key}: "${before}\\"${middle}\\"${after}"`;
        })
        .replace(/^(title|description):\s*"([^"]*)$/gm, '$1: "$2"'); // Fix unclosed quotes
      
      // Fix 2: Ensure proper label quoting
      fixed = fixed.replace(/^\s+label:\s+([^"\n]+)$/gm, (match, label) => {
        if (!label.startsWith('"')) {
          return `  label: "${label.trim()}"`;
        }
        return match;
      });
      
      // Try to validate the fix
      try {
        yaml.load(fixed);
        
        // Write back
        const newContent = `---\n${fixed}\n---${restOfContent}`;
        await fs.writeFile(filePath, newContent, 'utf-8');
        return true;
      } catch (e2) {
        console.error(`Could not auto-fix ${path.basename(filePath)}: ${e2.message}`);
        return false;
      }
    }
  } catch (error) {
    console.error(`Error processing ${filePath}:`, error.message);
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
      const success = await fixYAMLQuotes(fullPath);
      if (success) {
        fixed++;
        console.log(`✓ Fixed: ${path.relative(rootDir, fullPath)}`);
      }
    }
  }
  
  return fixed;
}

const contentDir = path.join(rootDir, 'src/content/docs');
console.log('Fixing YAML quote issues...\n');

const fixed = await processDirectory(contentDir);
console.log(`\n✓ Fixed YAML in ${fixed} files`);
