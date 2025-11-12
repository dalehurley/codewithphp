#!/usr/bin/env node

/**
 * Update Code References Script
 * 
 * This script updates all markdown references from relative paths (../code/...)
 * to full GitHub blob URLs for code examples that have been moved to /code
 * at the project root.
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const GITHUB_BASE_URL = 'https://github.com/dalehurley/codewithphp/blob/main/code';

// Track statistics
const stats = {
  filesProcessed: 0,
  filesModified: 0,
  referencesUpdated: 0,
  errors: []
};

/**
 * Get all markdown files recursively from a directory
 */
function getMarkdownFiles(dir, fileList = []) {
  const files = fs.readdirSync(dir);
  
  files.forEach(file => {
    const filePath = path.join(dir, file);
    const stat = fs.statSync(filePath);
    
    if (stat.isDirectory()) {
      getMarkdownFiles(filePath, fileList);
    } else if (file.endsWith('.md')) {
      fileList.push(filePath);
    }
  });
  
  return fileList;
}

/**
 * Extract series name from file path
 * e.g., /docs/series/php-basics/chapters/01-file.md -> php-basics
 */
function extractSeriesFromPath(filePath) {
  const match = filePath.match(/\/series\/([^\/]+)\//);
  return match ? match[1] : null;
}

/**
 * Update code references in markdown content
 */
function updateReferences(content, filePath) {
  const series = extractSeriesFromPath(filePath);
  
  if (!series) {
    console.warn(`⚠️  Could not extract series from path: ${filePath}`);
    return { content, updated: 0 };
  }
  
  let updated = 0;
  let newContent = content;
  
  // Pattern 1: ../code/ references (most common)
  // Matches: [text](../code/path/to/file.ext)
  const relativeCodePattern = /\[([^\]]+)\]\(\.\.\/code\/([^\)]+)\)/g;
  newContent = newContent.replace(relativeCodePattern, (match, linkText, codePath) => {
    updated++;
    return `[${linkText}](${GITHUB_BASE_URL}/${series}/${codePath})`;
  });
  
  // Pattern 2: Old GitHub URLs with docs/series/*/code/ structure
  // Matches: [text](https://github.com/.../docs/series/*/code/...)
  const oldGithubPattern = /\[([^\]]+)\]\(https:\/\/github\.com\/dalehurley\/codewithphp\/blob\/main\/docs\/series\/[^\/]+\/code\/([^\)]+)\)/g;
  newContent = newContent.replace(oldGithubPattern, (match, linkText, codePath) => {
    updated++;
    return `[${linkText}](${GITHUB_BASE_URL}/${series}/${codePath})`;
  });
  
  // Pattern 3: Root-relative references without leading slash
  // Matches: [text](code/path/to/file.ext) in code README files
  if (filePath.includes('/code/')) {
    const rootRelativePattern = /\[([^\]]+)\]\((?!http|\.\.\/|\/)(code\/[^\)]+)\)/g;
    newContent = newContent.replace(rootRelativePattern, (match, linkText, codePath) => {
      updated++;
      // Extract just the path after 'code/'
      const pathAfterCode = codePath.replace(/^code\//, '');
      return `[${linkText}](${GITHUB_BASE_URL}/${series}/${pathAfterCode})`;
    });
  }
  
  return { content: newContent, updated };
}

/**
 * Process a single markdown file
 */
function processFile(filePath) {
  stats.filesProcessed++;
  
  try {
    const content = fs.readFileSync(filePath, 'utf8');
    const result = updateReferences(content, filePath);
    
    if (result.updated > 0) {
      fs.writeFileSync(filePath, result.content, 'utf8');
      stats.filesModified++;
      stats.referencesUpdated += result.updated;
      console.log(`✓ ${path.relative(process.cwd(), filePath)} - ${result.updated} reference(s) updated`);
    }
  } catch (error) {
    stats.errors.push({ file: filePath, error: error.message });
    console.error(`✗ Error processing ${filePath}: ${error.message}`);
  }
}

/**
 * Main execution
 */
function main() {
  console.log('🔧 Starting code reference update...\n');
  
  const docsSeriesDir = path.join(process.cwd(), 'docs', 'series');
  const codeDir = path.join(process.cwd(), 'code');
  
  if (!fs.existsSync(docsSeriesDir)) {
    console.error('❌ docs/series directory not found!');
    process.exit(1);
  }
  
  if (!fs.existsSync(codeDir)) {
    console.error('❌ /code directory not found! Please run the move operation first.');
    process.exit(1);
  }
  
  // Get all markdown files from docs/series and code directories
  console.log('📁 Finding markdown files...\n');
  const seriesFiles = getMarkdownFiles(docsSeriesDir);
  const codeFiles = getMarkdownFiles(codeDir);
  const allFiles = [...seriesFiles, ...codeFiles];
  
  console.log(`Found ${allFiles.length} markdown files\n`);
  console.log('📝 Processing files...\n');
  
  // Process each file
  allFiles.forEach(processFile);
  
  // Print summary
  console.log('\n' + '='.repeat(60));
  console.log('📊 Summary');
  console.log('='.repeat(60));
  console.log(`Files processed: ${stats.filesProcessed}`);
  console.log(`Files modified: ${stats.filesModified}`);
  console.log(`References updated: ${stats.referencesUpdated}`);
  
  if (stats.errors.length > 0) {
    console.log(`\n⚠️  Errors encountered: ${stats.errors.length}`);
    stats.errors.forEach(({ file, error }) => {
      console.log(`  - ${path.relative(process.cwd(), file)}: ${error}`);
    });
  }
  
  // Check for any remaining ../code/ references
  console.log('\n🔍 Checking for remaining ../code/ references...\n');
  
  let remainingReferences = 0;
  allFiles.forEach(filePath => {
    try {
      const content = fs.readFileSync(filePath, 'utf8');
      const matches = content.match(/\]\(\.\.\/code\//g);
      if (matches) {
        remainingReferences += matches.length;
        console.log(`⚠️  ${path.relative(process.cwd(), filePath)} still has ${matches.length} ../code/ reference(s)`);
      }
    } catch (error) {
      // Skip files we can't read
    }
  });
  
  if (remainingReferences === 0) {
    console.log('✅ No remaining ../code/ references found!');
  } else {
    console.log(`\n⚠️  Found ${remainingReferences} remaining ../code/ references`);
  }
  
  console.log('\n✨ Done!\n');
}

// Run the script
main();

