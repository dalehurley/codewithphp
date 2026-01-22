import fs from 'fs/promises';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const rootDir = path.join(__dirname, '..');

/**
 * Convert VitePress markdown files to Starlight format
 */

// Remove Vue components from content
function removeVueComponents(content) {
  // Remove ProgressTracker
  content = content.replace(/<ProgressTracker[^>]*\/>/g, '');
  content = content.replace(/<ProgressTracker[^>]*>[\s\S]*?<\/ProgressTracker>/g, '');
  
  // Remove ChapterCheckbox  
  content = content.replace(/<ChapterCheckbox[^>]*\/>/g, '');
  content = content.replace(/<ChapterCheckbox[^>]*>[\s\S]*?<\/ChapterCheckbox>/g, '');
  
  // Remove Quiz
  content = content.replace(/<Quiz[^>]*\/>/g, '');
  content = content.replace(/<Quiz[^>]*>[\s\S]*?<\/Quiz>/g, '');
  
  // Remove home page Vue components
  content = content.replace(/<HeroSection[^>]*\/>/g, '');
  content = content.replace(/<SocialProofSection[^>]*\/>/g, '');
  content = content.replace(/<FeaturesSection[^>]*\/>/g, '');
  content = content.replace(/<LearningPathsSection[^>]*\/>/g, '');
  content = content.replace(/<CTASection[^>]*\/>/g, '');
  content = content.replace(/<MissionSection[^>]*\/>/g, '');
  content = content.replace(/<TestimonialsSection[^>]*\/>/g, '');
  
  return content;
}

// Remove custom breadcrumbs (Starlight provides automatic breadcrumbs)
function removeBreadcrumbs(content) {
  content = content.replace(/<div class="breadcrumbs">[\s\S]*?<\/div>/g, '');
  return content;
}

// Convert VitePress frontmatter to Starlight frontmatter
function convertFrontmatter(content, filePath) {
  const frontmatterMatch = content.match(/^---\n([\s\S]*?)\n---/);
  if (!frontmatterMatch) {
    return content;
  }

  const frontmatter = frontmatterMatch[1];
  const lines = frontmatter.split('\n');
  const meta = {};

  // Parse existing frontmatter
  lines.forEach(line => {
    const match = line.match(/^(\w+):\s*(.+)/);
    if (match) {
      const key = match[1];
      const value = match[2].replace(/^["']|["']$/g, '');
      meta[key] = value;
    }
  });

  // Build Starlight frontmatter
  const newFrontmatter = {
    title: meta.title || '',
    description: meta.description || '',
  };

  // Add sidebar configuration for chapters
  if (meta.chapter !== undefined) {
    const chapterNum = String(meta.chapter).padStart(2, '0');
    newFrontmatter.sidebar = {
      label: `${chapterNum}: ${meta.title}`,
      order: parseInt(meta.chapter) || 0,
    };

    // Add badge for difficulty
    if (meta.difficulty) {
      newFrontmatter.sidebar.badge = {
        text: meta.difficulty,
        variant: meta.difficulty === 'Beginner' ? 'success' : 
                 meta.difficulty === 'Intermediate' ? 'caution' : 'danger',
      };
    }
  }

  // Build new frontmatter YAML
  const newFrontmatterYaml = Object.entries(newFrontmatter)
    .map(([key, value]) => {
      if (typeof value === 'object') {
        const nested = Object.entries(value)
          .map(([k, v]) => {
            if (typeof v === 'object') {
              const deepNested = Object.entries(v)
                .map(([dk, dv]) => `    ${dk}: ${dv}`)
                .join('\n');
              return `  ${k}:\n${deepNested}`;
            }
            return `  ${k}: ${v}`;
          })
          .join('\n');
        return `${key}:\n${nested}`;
      }
      return `${key}: "${value}"`;
    })
    .join('\n');

  const restOfContent = content.replace(/^---\n[\s\S]*?\n---\n/, '');
  return `---\n${newFrontmatterYaml}\n---\n${restOfContent}`;
}

// Main conversion function
async function convertFile(sourcePath, destPath) {
  try {
    let content = await fs.readFile(sourcePath, 'utf-8');

    // Apply conversions
    content = convertFrontmatter(content, sourcePath);
    content = removeBreadcrumbs(content);
    content = removeVueComponents(content);

    // Clean up excessive blank lines
    content = content.replace(/\n{4,}/g, '\n\n\n');

    // Ensure destination directory exists
    await fs.mkdir(path.dirname(destPath), { recursive: true });

    // Write converted content
    await fs.writeFile(destPath, content, 'utf-8');
    console.log(`✓ Converted: ${path.relative(rootDir, sourcePath)} → ${path.relative(rootDir, destPath)}`);
    return true;
  } catch (error) {
    console.error(`✗ Error converting ${sourcePath}:`, error.message);
    return false;
  }
}

// Convert all files in a directory recursively
async function convertDirectory(sourceDir, destDir) {
  const entries = await fs.readdir(sourceDir, { withFileTypes: true });
  let converted = 0;
  let failed = 0;

  for (const entry of entries) {
    const sourcePath = path.join(sourceDir, entry.name);
    const destPath = path.join(destDir, entry.name);

    if (entry.isDirectory()) {
      const result = await convertDirectory(sourcePath, destPath);
      converted += result.converted;
      failed += result.failed;
    } else if (entry.name.endsWith('.md')) {
      const success = await convertFile(sourcePath, destPath);
      if (success) {
        converted++;
      } else {
        failed++;
      }
    }
  }

  return { converted, failed };
}

// CLI usage
const args = process.argv.slice(2);
if (args.length !== 2) {
  console.log('Usage: node convert-markdown.js <source-dir> <dest-dir>');
  console.log('Example: node convert-markdown.js docs/series/php-basics src/content/docs/series/php-basics');
  process.exit(1);
}

const sourceDir = path.resolve(rootDir, args[0]);
const destDir = path.resolve(rootDir, args[1]);

console.log(`Converting markdown files from ${sourceDir} to ${destDir}...\n`);

const { converted, failed } = await convertDirectory(sourceDir, destDir);

console.log(`\n✓ Conversion complete!`);
console.log(`  Converted: ${converted} files`);
if (failed > 0) {
  console.log(`  Failed: ${failed} files`);
}
