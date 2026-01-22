import fs from 'fs/promises';
import path from 'path';
import { fileURLToPath } from 'url';
import yaml from 'js-yaml';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const rootDir = path.join(__dirname, '..');

const stats = {
  total: 0,
  valid: 0,
  fixed: 0,
  errors: []
};

async function validateAndFixFrontmatter(filePath) {
  stats.total++;
  
  try {
    let content = await fs.readFile(filePath, 'utf-8');
    const relativePath = path.relative(rootDir, filePath);
    
    // Extract frontmatter
    const frontmatterMatch = content.match(/^---\n([\s\S]*?)\n---/);
    if (!frontmatterMatch) {
      stats.errors.push({ file: relativePath, error: 'No frontmatter found' });
      return false;
    }
    
    let frontmatter = frontmatterMatch[1];
    const restOfContent = content.substring(frontmatterMatch[0].length);
    let needsFix = false;
    
    // Try to parse original
    try {
      const parsed = yaml.load(frontmatter);
      
      // Validate required fields
      if (!parsed.title) {
        stats.errors.push({ file: relativePath, error: 'Missing title' });
        return false;
      }
      if (!parsed.description) {
        stats.errors.push({ file: relativePath, error: 'Missing description' });
        return false;
      }
      
      stats.valid++;
      return true;
    } catch (yamlError) {
      // Try to fix common issues
      needsFix = true;
      let fixed = frontmatter;
      
      // Fix 1: Escape nested quotes in title/description
      // Replace double quotes inside double-quoted strings with single quotes
      fixed = fixed.replace(
        /^(title|description):\s*"([^"]*)"/gm,
        (match, key, value) => {
          // If value contains unescaped quotes, use single quotes for outer
          if (value.includes('"') && !value.includes('\\"')) {
            return `${key}: '${value}'`;
          }
          return match;
        }
      );
      
      // Fix 2: Handle colons in unquoted values
      fixed = fixed.replace(
        /^(title|description):\s*([^'"\n][^\n]*:[^\n]+)$/gm,
        (match, key, value) => {
          return `${key}: "${value.trim()}"`;
        }
      );
      
      // Fix 3: Fix duplicated chapter numbers in sidebar labels (e.g., "01: 01: Title")
      fixed = fixed.replace(
        /^\s+label:\s*"?(\d+):\s+\1:\s+([^"]+)"?$/gm,
        (match, num, title) => {
          return `  label: "${num}: ${title.trim()}"`;
        }
      );
      
      // Fix 4: Ensure labels are quoted if they contain colons
      fixed = fixed.replace(
        /^\s+label:\s+([^"\n]+:[^\n]+)$/gm,
        (match, label) => {
          if (!label.trim().startsWith('"')) {
            return `  label: "${label.trim()}"`;
          }
          return match;
        }
      );
      
      // Fix 5: Remove trailing colons from descriptions
      fixed = fixed.replace(
        /^(description):\s*"([^"]+):"$/gm,
        '$1: "$2"'
      );
      
      // Try to parse the fixed version
      try {
        const parsed = yaml.load(fixed);
        
        // Validate required fields
        if (!parsed.title || !parsed.description) {
          stats.errors.push({ 
            file: relativePath, 
            error: `Missing required fields: ${!parsed.title ? 'title' : ''} ${!parsed.description ? 'description' : ''}` 
          });
          return false;
        }
        
        // Write back if fixed
        const newContent = `---\n${fixed}\n---${restOfContent}`;
        await fs.writeFile(filePath, newContent, 'utf-8');
        stats.fixed++;
        console.log(`✓ Fixed: ${relativePath}`);
        return true;
        
      } catch (e2) {
        stats.errors.push({ 
          file: relativePath, 
          error: yamlError.message,
          details: e2.message
        });
        return false;
      }
    }
  } catch (error) {
    stats.errors.push({ 
      file: path.relative(rootDir, filePath), 
      error: error.message 
    });
    return false;
  }
}

async function processDirectory(dir) {
  const entries = await fs.readdir(dir, { withFileTypes: true });
  
  for (const entry of entries) {
    const fullPath = path.join(dir, entry.name);
    
    if (entry.isDirectory()) {
      await processDirectory(fullPath);
    } else if (entry.name.endsWith('.md') || entry.name.endsWith('.mdx')) {
      await validateAndFixFrontmatter(fullPath);
    }
  }
}

const contentDir = path.join(rootDir, 'src/content/docs');
console.log('Validating and fixing frontmatter...\n');

await processDirectory(contentDir);

console.log('\n' + '='.repeat(60));
console.log('VALIDATION SUMMARY');
console.log('='.repeat(60));
console.log(`Total files:    ${stats.total}`);
console.log(`Valid:          ${stats.valid} (${Math.round(stats.valid/stats.total*100)}%)`);
console.log(`Fixed:          ${stats.fixed}`);
console.log(`Errors:         ${stats.errors.length}`);

if (stats.errors.length > 0) {
  console.log('\n' + '='.repeat(60));
  console.log('ERRORS');
  console.log('='.repeat(60));
  stats.errors.forEach(({ file, error, details }) => {
    console.log(`\n❌ ${file}`);
    console.log(`   ${error}`);
    if (details) console.log(`   (${details})`);
  });
}

console.log('\n' + '='.repeat(60));
console.log(`Final success rate: ${Math.round((stats.valid + stats.fixed)/stats.total*100)}%`);
console.log('='.repeat(60));
