import fs from 'fs/promises';
import path from 'path';
import { fileURLToPath } from 'url';
import yaml from 'js-yaml';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const rootDir = path.join(__dirname, '..');

async function extractFrontmatter(filePath) {
  const content = await fs.readFile(filePath, 'utf-8');
  const match = content.match(/^---\n([\s\S]*?)\n---/);
  if (!match) return null;
  
  try {
    return yaml.load(match[1]);
  } catch (e) {
    console.error(`Error parsing ${filePath}: ${e.message}`);
    return null;
  }
}

async function getSeriesChapters(seriesPath) {
  const chapters = [];
  const chaptersDir = path.join(seriesPath, 'chapters');
  
  try {
    const files = await fs.readdir(chaptersDir);
    
    for (const file of files.sort()) {
      if (!file.endsWith('.md') && !file.endsWith('.mdx')) continue;
      
      const filePath = path.join(chaptersDir, file);
      const frontmatter = await extractFrontmatter(filePath);
      
      if (frontmatter) {
        const slug = file.replace(/\.(md|mdx)$/, '');
        chapters.push({
          label: frontmatter.sidebar?.label || frontmatter.title,
          link: `/series/${path.basename(seriesPath)}/chapters/${slug}/`,
          badge: frontmatter.sidebar?.badge,
          order: frontmatter.sidebar?.order
        });
      }
    }
  } catch (e) {
    // No chapters directory
  }
  
  // Check for appendices
  const appendicesDir = path.join(seriesPath, 'appendices');
  try {
    const files = await fs.readdir(appendicesDir);
    
    for (const file of files.sort()) {
      if (!file.endsWith('.md') && !file.endsWith('.mdx')) continue;
      
      const filePath = path.join(appendicesDir, file);
      const frontmatter = await extractFrontmatter(filePath);
      
      if (frontmatter) {
        const slug = file.replace(/\.(md|mdx)$/, '');
        chapters.push({
          label: frontmatter.sidebar?.label || frontmatter.title,
          link: `/series/${path.basename(seriesPath)}/appendices/${slug}/`,
          badge: frontmatter.sidebar?.badge,
          order: frontmatter.sidebar?.order || 999
        });
      }
    }
  } catch (e) {
    // No appendices directory
  }
  
  // Sort by order
  chapters.sort((a, b) => (a.order || 0) - (b.order || 0));
  
  return chapters;
}

async function generateSidebarConfig() {
  const contentDir = path.join(rootDir, 'src/content/docs/series');
  const seriesDirs = await fs.readdir(contentDir);
  
  const seriesConfig = [];
  
  const seriesNames = {
    'php-basics': 'PHP Basics',
    'ai-ml-php-developers': 'AI/ML for PHP Developers',
    'php-algorithms': 'Algorithms for PHP Developers',
    'php-for-java-developers': 'PHP for Java Developers',
    'data-science-php-developers': 'Data Science for PHP Developers',
    'claude-php-developers': 'Claude for PHP Developers',
    'php-typescript-developers': 'PHP for TypeScript Developers',
    'python-developers-love-php-laravel': 'Why Python Developers Will Love PHP and Laravel',
    'build-crm-laravel-12': 'Build a CRM with Laravel 12',
    'rails-developers-love-laravel': 'Why Ruby on Rails Developers Will Love Laravel'
  };
  
  for (const seriesDir of seriesDirs.sort()) {
    const seriesPath = path.join(contentDir, seriesDir);
    const stat = await fs.stat(seriesPath);
    
    if (!stat.isDirectory()) continue;
    
    const chapters = await getSeriesChapters(seriesPath);
    const seriesName = seriesNames[seriesDir] || seriesDir;
    
    const items = [
      {
        label: 'Overview',
        link: `/series/${seriesDir}/`
      }
    ];
    
    // Add chapters
    for (const chapter of chapters) {
      const item = {
        label: chapter.label,
        link: chapter.link
      };
      
      if (chapter.badge) {
        item.badge = chapter.badge;
      }
      
      items.push(item);
    }
    
    seriesConfig.push({
      label: seriesName,
      items: items
    });
    
    console.log(`✓ ${seriesName}: ${chapters.length} items`);
  }
  
  return seriesConfig;
}

console.log('Generating sidebar configuration...\n');

const config = await generateSidebarConfig();

console.log('\n' + '='.repeat(60));
console.log('SIDEBAR CONFIGURATION');
console.log('='.repeat(60));
console.log('\nPaste this into your astro.config.mjs sidebar array:\n');
console.log('sidebar: [');
console.log('  { label: \'Home\', link: \'/\' },');

for (const series of config) {
  console.log('  {');
  console.log(`    label: '${series.label}',`);
  console.log('    items: [');
  
  for (const item of series.items) {
    console.log('      {');
    console.log(`        label: '${item.label.replace(/'/g, "\\'")}',`);
    console.log(`        link: '${item.link}',`);
    if (item.badge) {
      console.log(`        badge: {`);
      console.log(`          text: '${item.badge.text}',`);
      console.log(`          variant: '${item.badge.variant}'`);
      console.log(`        }`);
    }
    console.log('      },');
  }
  
  console.log('    ]');
  console.log('  },');
}

console.log('],');
console.log('\n' + '='.repeat(60));
