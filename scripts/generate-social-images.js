#!/usr/bin/env node

/**
 * Social Share Image Generator
 * 
 * Generates 1200x630px social share images with chapter titles
 * for Open Graph and Twitter Card meta tags.
 * 
 * Usage: node scripts/generate-social-images.js
 */

import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'
import sharp from 'sharp'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)

// Constants
const IMAGE_WIDTH = 1200
const IMAGE_HEIGHT = 630
const DOCS_DIR = path.join(__dirname, '../docs')
const OUTPUT_DIR = path.join(__dirname, '../docs/public/social')
const SERIES_DIR = path.join(DOCS_DIR, 'series')

// Series-specific color schemes
const SERIES_COLORS = {
  'php-basics': {
    background: '#7C7EAF',
    accent: '#4F5887',
    text: '#FFFFFF'
  },
  'ai-ml-php-developers': {
    background: '#4A90E2',
    accent: '#2E5C8A',
    text: '#FFFFFF'
  }
}

/**
 * Parse frontmatter from markdown file
 */
function parseFrontmatter(content) {
  const match = content.match(/^---\n([\s\S]*?)\n---/)
  if (!match) return {}
  
  const frontmatter = {}
  const lines = match[1].split('\n')
  
  for (const line of lines) {
    const [key, ...valueParts] = line.split(':')
    if (key && valueParts.length > 0) {
      const value = valueParts.join(':').trim().replace(/^["']|["']$/g, '')
      frontmatter[key.trim()] = value
    }
  }
  
  return frontmatter
}

/**
 * Get all markdown files in a directory
 */
function getMarkdownFiles(dir) {
  const files = []
  
  if (!fs.existsSync(dir)) return files
  
  const items = fs.readdirSync(dir, { withFileTypes: true })
  
  for (const item of items) {
    const fullPath = path.join(dir, item.name)
    
    if (item.isDirectory()) {
      files.push(...getMarkdownFiles(fullPath))
    } else if (item.name.endsWith('.md')) {
      files.push(fullPath)
    }
  }
  
  return files
}

/**
 * Wrap text to fit within width
 */
function wrapText(text, maxWidth, fontSize) {
  const words = text.split(' ')
  const lines = []
  let currentLine = ''
  
  // Approximate character width (adjust based on font)
  const charWidth = fontSize * 0.6
  const maxChars = Math.floor(maxWidth / charWidth)
  
  for (const word of words) {
    const testLine = currentLine ? `${currentLine} ${word}` : word
    
    if (testLine.length <= maxChars) {
      currentLine = testLine
    } else {
      if (currentLine) lines.push(currentLine)
      currentLine = word
    }
  }
  
  if (currentLine) lines.push(currentLine)
  
  return lines
}

/**
 * Generate SVG for social image
 */
function generateSVG(title, series, chapterNum, colors) {
  const wrappedLines = wrapText(title, IMAGE_WIDTH - 200, 60)
  const titleHeight = wrappedLines.length * 80
  const titleStartY = (IMAGE_HEIGHT - titleHeight) / 2 + 50
  
  const titleSVG = wrappedLines
    .map((line, index) => {
      const y = titleStartY + (index * 80)
      return `<text x="${IMAGE_WIDTH / 2}" y="${y}" text-anchor="middle" font-size="60" font-weight="bold" fill="${colors.text}">${escapeXml(line)}</text>`
    })
    .join('\n')
  
  const seriesLabel = series === 'php-basics' ? 'PHP Basics' : 'AI/ML for PHP Developers'
  const chapterLabel = chapterNum !== null ? `Chapter ${chapterNum}` : ''
  
  return `
    <svg width="${IMAGE_WIDTH}" height="${IMAGE_HEIGHT}" xmlns="http://www.w3.org/2000/svg">
      <!-- Background gradient -->
      <defs>
        <linearGradient id="bgGradient" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" style="stop-color:${colors.background};stop-opacity:1" />
          <stop offset="100%" style="stop-color:${colors.accent};stop-opacity:1" />
        </linearGradient>
      </defs>
      
      <!-- Background -->
      <rect width="${IMAGE_WIDTH}" height="${IMAGE_HEIGHT}" fill="url(#bgGradient)" />
      
      <!-- Decorative elements -->
      <circle cx="100" cy="100" r="150" fill="${colors.accent}" opacity="0.2" />
      <circle cx="${IMAGE_WIDTH - 100}" cy="${IMAGE_HEIGHT - 100}" r="180" fill="${colors.accent}" opacity="0.2" />
      
      <!-- Title -->
      ${titleSVG}
      
      <!-- Series label -->
      <text x="${IMAGE_WIDTH / 2}" y="80" text-anchor="middle" font-size="28" font-weight="600" fill="${colors.text}" opacity="0.9">
        ${escapeXml(seriesLabel)}
      </text>
      
      <!-- Chapter number -->
      ${chapterLabel ? `<text x="${IMAGE_WIDTH / 2}" y="${IMAGE_HEIGHT - 50}" text-anchor="middle" font-size="32" font-weight="600" fill="${colors.text}" opacity="0.8">${escapeXml(chapterLabel)}</text>` : ''}
      
      <!-- Branding -->
      <text x="${IMAGE_WIDTH / 2}" y="${IMAGE_HEIGHT - 80}" text-anchor="middle" font-size="24" fill="${colors.text}" opacity="0.7">
        codewithphp.com
      </text>
    </svg>
  `.trim()
}

/**
 * Escape XML special characters
 */
function escapeXml(text) {
  return text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&apos;')
}

/**
 * Generate social image for a chapter
 */
async function generateChapterImage(filePath) {
  const content = fs.readFileSync(filePath, 'utf-8')
  const frontmatter = parseFrontmatter(content)
  
  if (!frontmatter.series || frontmatter.chapter === undefined) {
    return null
  }
  
  const series = frontmatter.series
  const chapter = String(frontmatter.chapter).padStart(2, '0')
  const title = frontmatter.title || `Chapter ${chapter}`
  
  // Remove chapter prefix from title for better display
  const displayTitle = title.replace(/^\d+[ab]?:\s*/, '')
  
  const colors = SERIES_COLORS[series] || SERIES_COLORS['php-basics']
  const svg = generateSVG(displayTitle, series, frontmatter.chapter, colors)
  
  const outputFilename = `${series}-chapter-${chapter}.jpg`
  const outputPath = path.join(OUTPUT_DIR, outputFilename)
  
  try {
    await sharp(Buffer.from(svg))
      .jpeg({ quality: 90 })
      .toFile(outputPath)
    
    console.log(`✓ Generated: ${outputFilename}`)
    return outputFilename
  } catch (error) {
    console.error(`✗ Error generating ${outputFilename}:`, error.message)
    return null
  }
}

/**
 * Generate social image for series overview
 */
async function generateSeriesImage(seriesSlug) {
  const indexPath = path.join(SERIES_DIR, seriesSlug, 'index.md')
  
  if (!fs.existsSync(indexPath)) {
    return null
  }
  
  const content = fs.readFileSync(indexPath, 'utf-8')
  const frontmatter = parseFrontmatter(content)
  
  const title = frontmatter.title || seriesSlug
  const colors = SERIES_COLORS[seriesSlug] || SERIES_COLORS['php-basics']
  const svg = generateSVG(title, seriesSlug, null, colors)
  
  const outputFilename = `${seriesSlug}-overview.jpg`
  const outputPath = path.join(OUTPUT_DIR, outputFilename)
  
  try {
    await sharp(Buffer.from(svg))
      .jpeg({ quality: 90 })
      .toFile(outputPath)
    
    console.log(`✓ Generated: ${outputFilename}`)
    return outputFilename
  } catch (error) {
    console.error(`✗ Error generating ${outputFilename}:`, error.message)
    return null
  }
}

/**
 * Generate homepage social image
 */
async function generateHomepageImage() {
  const colors = SERIES_COLORS['php-basics']
  const svg = generateSVG('Learn PHP from First Principles', 'homepage', null, {
    ...colors,
    background: '#667EEA',
    accent: '#764BA2'
  })
  
  const outputPath = path.join(OUTPUT_DIR, 'homepage.jpg')
  
  try {
    await sharp(Buffer.from(svg))
      .jpeg({ quality: 90 })
      .toFile(outputPath)
    
    console.log(`✓ Generated: homepage.jpg`)
  } catch (error) {
    console.error(`✗ Error generating homepage.jpg:`, error.message)
  }
}

/**
 * Main execution
 */
async function main() {
  console.log('🎨 Social Image Generator\n')
  
  // Ensure output directory exists
  if (!fs.existsSync(OUTPUT_DIR)) {
    fs.mkdirSync(OUTPUT_DIR, { recursive: true })
  }
  
  let generatedCount = 0
  
  // Generate homepage image
  await generateHomepageImage()
  generatedCount++
  
  // Generate series overview images
  const seriesDirs = fs.readdirSync(SERIES_DIR, { withFileTypes: true })
    .filter(item => item.isDirectory())
    .map(item => item.name)
  
  for (const seriesSlug of seriesDirs) {
    await generateSeriesImage(seriesSlug)
    generatedCount++
  }
  
  // Generate chapter images
  const markdownFiles = getMarkdownFiles(SERIES_DIR)
  
  for (const filePath of markdownFiles) {
    const result = await generateChapterImage(filePath)
    if (result) generatedCount++
  }
  
  console.log(`\n✨ Generated ${generatedCount} social images`)
  console.log(`📁 Output directory: ${OUTPUT_DIR}`)
}

// Run
main().catch(error => {
  console.error('Fatal error:', error)
  process.exit(1)
})

