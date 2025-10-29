#!/usr/bin/env node

/**
 * AI-Powered Social Share Image Generator
 * 
 * Generates 1200x630px social share images with AI-generated backgrounds
 * and programmatic text overlays for Open Graph and Twitter Card meta tags.
 * 
 * Features:
 * - AI-generated contextual backgrounds via Gemini Imagen
 * - Sharp-based image compositing with text overlays
 * - Background caching for performance
 * - High-quality text rendering with proper contrast
 * 
 * Usage: node scripts/generate-social-images.js [--force-regenerate]
 */

import dotenv from 'dotenv'
import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'
import sharp from 'sharp'
import crypto from 'crypto'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)

// Load environment variables from .env files
// Try root .env first, then imagen/.env as fallback
dotenv.config({ path: path.join(__dirname, '../.env') })
dotenv.config({ path: path.join(__dirname, '../imagen/.env') })

// Try to import imagen generator (optional dependency)
let ImageGenerator = null
try {
  const imagenPath = path.join(__dirname, '../imagen/src/generator.js')
  if (fs.existsSync(imagenPath)) {
    const module = await import(imagenPath)
    ImageGenerator = module.ImageGenerator
  }
} catch (error) {
  console.warn('⚠️  Imagen generator not available, will use fallback backgrounds')
}

// Constants
const IMAGE_WIDTH = 1200
const IMAGE_HEIGHT = 630
const DOCS_DIR = path.join(__dirname, '../docs')
const OUTPUT_DIR = path.join(__dirname, '../docs/public/social')
const SERIES_DIR = path.join(DOCS_DIR, 'series')
const CACHE_DIR = path.join(__dirname, '../docs/public/social/.cache')
const CACHE_MANIFEST = path.join(CACHE_DIR, 'manifest.json')

// Background generation dimensions (3:2 ratio, then cropped)
const BG_WIDTH = 1800
const BG_HEIGHT = 1200

// Command line args
const FORCE_REGENERATE = process.argv.includes('--force-regenerate')

// Series-specific color schemes (for overlays and text)
const SERIES_COLORS = {
  'php-basics': {
    primary: '#7C7EAF',
    secondary: '#4F5887',
    text: '#FFFFFF',
    overlay: 'rgba(79, 88, 135, 0.7)'
  },
  'ai-ml-php-developers': {
    primary: '#4A90E2',
    secondary: '#2E5C8A',
    text: '#FFFFFF',
    overlay: 'rgba(46, 92, 138, 0.7)'
  }
}

// Visual style options for variety
const VISUAL_STYLES = {
  'kawaii': {
    description: 'Kawaii/cute style with bold black outlines, simple cel-shading, happy faces, and vibrant colors',
    suffix: 'with adorable expressions and cheerful personality'
  },
  'isometric': {
    description: 'Isometric 3D perspective with clean geometric shapes, flat colors, and modern tech aesthetic',
    suffix: 'in isometric view with depth and dimension'
  },
  'flat': {
    description: 'Flat design with bold solid colors, minimal shadows, clean lines, and modern simplicity',
    suffix: 'in flat design style with geometric simplicity'
  },
  'comic': {
    description: 'Comic book style with dynamic angles, bold outlines, halftone dots, action lines, and vibrant pop art colors',
    suffix: 'in dynamic comic book style with energy and movement'
  },
  'neon': {
    description: 'Neon cyberpunk style with glowing edges, electric colors, dark background, and futuristic vibes',
    suffix: 'with glowing neon outlines and cyberpunk aesthetic'
  },
  'minimalist': {
    description: 'Minimalist line art with single continuous lines, negative space, and sophisticated simplicity',
    suffix: 'in elegant minimalist style with essential details only'
  },
  'retro': {
    description: 'Retro 80s/90s style with pixel art influences, limited color palette, and nostalgic computing aesthetic',
    suffix: 'in retro pixel-inspired style with vintage computing vibes'
  },
  'hand-drawn': {
    description: 'Hand-drawn sketch style with loose pencil lines, watercolor washes, and artistic imperfection',
    suffix: 'in hand-drawn sketch style with artistic charm'
  }
}

// Illustrative character/object themes for each chapter topic
const ILLUSTRATION_THEMES = {
  // Programming concepts
  'variables': 'A container box or jar holding colorful data cubes labeled with $ symbols, showing storage and organization',
  'functions': 'Interconnected gears and cogs working together in harmony, showing input-output flow and modular mechanics',
  'arrays': 'An organized filing system or grid of sorted items, showcasing structure and indexed organization',
  'strings': 'Flowing text ribbons, letters, and quotation marks forming dynamic patterns and typography',
  'loops': 'A circular mechanism or wheel showing continuous motion, repetition, and cycles',
  'control': 'Traffic signals, arrows, or directional pathways showing decision-making and flow control',
  'conditions': 'A fork in the road or branching paths with clear different directions and choices',
  'classes': 'Architectural blueprints unrolling with building blocks or construction elements assembling',
  'objects': 'Three-dimensional geometric shapes or building blocks stacking and combining into structures',
  'oop': 'A construction site with building materials, tools, and modular components being assembled',
  'inheritance': 'A family tree or hierarchical diagram showing parent-child relationships and trait passing',
  'traits': 'Modular power-up badges, mix-and-match components, or LEGO-like connectable pieces',
  'namespace': 'Organized folder system, nested containers, or library shelving with clear categorization',
  
  // Technical topics
  'database': 'Filing cabinets, organized data tables, or storage vaults with categorized information cards and records',
  'api': 'Two systems or robots shaking hands, exchanging data packages, showing connectivity and communication',
  'security': 'A shield, lock and key, or protective barrier guarding valuable data and assets',
  'session': 'A cookie jar or time-based tokens showing temporary storage and state management',
  'error': 'A bug or debugging magnifying glass examining code, detective-style investigation tools',
  'testing': 'A magnifying glass inspecting code with checkmarks, quality control stamps, and validation tools',
  'composer': 'A conductor orchestrating packages, or a package manager organizing dependencies like sheet music',
  'filesystem': 'Nested folders, document stacks, or file trees showing hierarchical organization',
  'form': 'Input fields, text boxes, checkboxes, and submit buttons arranged as a structured interface',
  'router': 'A mail sorting system, traffic director, or GPS navigation showing request routing and destinations',
  'psr': 'A ruler, measuring tape, or standard template showing consistency and code standards',
  'laravel': 'The Laravel octopus mascot with eight arms juggling framework tools and features',
  'symfony': 'Musical instruments or orchestra elements representing the Symfony framework\'s harmony',
  'json': 'Curly braces { } containing nested key-value pairs in a structured tree format',
  
  // AI/ML topics
  'neural': 'A brain with glowing interconnected neural pathways, synapses firing, showing network connections',
  'machine learning': 'A robot or AI studying from data books, with pattern recognition symbols and learning graphs',
  'ai': 'An artificial intelligence robot with thinking indicators, light bulb above head, processing information',
  'tensor': 'Multi-dimensional cubes or matrices floating in space, showing complex data structures',
  'classification': 'Items being sorted into labeled categories, bins, or classification boxes with tags',
  'prediction': 'A crystal ball or forecast chart showing future trends, predictions, and data patterns',
  'vision': 'A camera lens or eye analyzing and recognizing images, with focus indicators and scan lines',
  'language': 'Open books with speech bubbles, translations, and word clouds showing text processing',
  
  // Web development
  'web': 'Browser windows with tabs, internet connectivity symbols, global network connections',
  'php': 'The PHP elephant mascot (elePHPant) in various poses, representing the language',
  
  // Default fallback
  'default': 'A code editor or terminal window with colorful syntax highlighting and programming symbols'
}

/**
 * Select a visual style based on chapter and add variety
 */
function selectVisualStyle(title, chapter) {
  // Create deterministic but varied style selection based on chapter
  const chapterNum = typeof chapter === 'string' ? parseInt(chapter) || 0 : chapter
  const styles = Object.keys(VISUAL_STYLES)
  
  // Use chapter number to select style deterministically
  // This ensures same chapter always gets same style, but variety across chapters
  const styleIndex = chapterNum % styles.length
  return styles[styleIndex]
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
 * Extract first code snippet from markdown content
 */
function extractCodeSnippet(content) {
  // Remove frontmatter
  const withoutFrontmatter = content.replace(/^---\n[\s\S]*?\n---\n/, '')
  
  // Find first PHP code block
  const phpMatch = withoutFrontmatter.match(/```php\n([\s\S]*?)\n```/)
  if (phpMatch) {
    const code = phpMatch[1].trim()
    // Return first 8-10 lines for expanded social image code box
    const lines = code.split('\n').slice(1, 11)
    return lines.join('\n')
  }
  
  // Fallback: find any code block
  const codeMatch = withoutFrontmatter.match(/```[\w]*\n([\s\S]*?)\n```/)
  if (codeMatch) {
    const code = codeMatch[1].trim()
    const lines = code.split('\n').slice(0, 10)
    return lines.join('\n')
  }
  
  return null
}

/**
 * Simple PHP syntax highlighter for SVG (returns styled text elements)
 */
function highlightPHPLine(code, x, y, fontSize) {
  // For simplicity, we'll use a monochrome approach with semantic colors
  // This avoids complex tspan nesting issues
  
  const escaped = escapeXml(code)
  
  // Determine primary color based on content
  let fill = '#ABB2BF' // default light gray
  
  if (code.trim().startsWith('//')) {
    fill = '#5C6370' // comment gray
  } else if (code.includes('$')) {
    fill = '#56B6C2' // variable cyan
  } else if (code.match(/\b(function|class|echo|return|if|else|public|private)\b/)) {
    fill = '#C678DD' // keyword purple
  }
  
  return `<text x="${x}" y="${y}" font-size="${fontSize}" font-family="'Monaco', 'Menlo', 'Courier New', monospace" fill="${fill}">${escaped}</text>`
}

/**
 * Create code snippet overlay
 */
function createCodeSnippet(code, series) {
  if (!code) return null
  
  const lines = code.split('\n') // Show all extracted lines (up to 10)
  
  // Position code box to extend to bottom of image
  const bottomMargin = 60  // Space for branding text below code box
  const lineHeight = 26
  const fontSize = 18
  const topPadding = 45  // Space for terminal dots
  const bottomPadding = 20
  
  // Calculate box height to fit all lines or max available space
  const maxBoxHeight = IMAGE_HEIGHT - 330  // Leave room for title at top
  const calculatedBoxHeight = (lines.length * lineHeight) + topPadding + bottomPadding
  const boxHeight = Math.min(calculatedBoxHeight, maxBoxHeight)
  
  const boxY = IMAGE_HEIGHT - boxHeight - bottomMargin
  const codeStartY = boxY + topPadding
  
  const highlightedLines = lines.map((line, index) => {
    const y = codeStartY + (index * lineHeight)
    return highlightPHPLine(line, 80, y, fontSize)
  }).join('\n')
  
  return `
    <!-- Code snippet background (expanded to bottom) -->
    <rect x="40" y="${boxY}" width="${IMAGE_WIDTH - 80}" height="${boxHeight}" 
          rx="8" fill="#282C34" opacity="0.95" />
    
    <!-- Terminal dots -->
    <circle cx="60" cy="${boxY + 20}" r="6" fill="#FF5F56" />
    <circle cx="80" cy="${boxY + 20}" r="6" fill="#FFBD2E" />
    <circle cx="100" cy="${boxY + 20}" r="6" fill="#27C93F" />
    
    <!-- Code lines -->
    ${highlightedLines}
  `
}

/**
 * Load cache manifest
 */
function loadCacheManifest() {
  if (!fs.existsSync(CACHE_MANIFEST)) {
    return {}
  }
  
  try {
    return JSON.parse(fs.readFileSync(CACHE_MANIFEST, 'utf-8'))
  } catch (error) {
    console.warn('⚠️  Failed to load cache manifest, starting fresh')
    return {}
  }
}

/**
 * Save cache manifest
 */
function saveCacheManifest(manifest) {
  if (!fs.existsSync(CACHE_DIR)) {
    fs.mkdirSync(CACHE_DIR, { recursive: true })
  }
  
  fs.writeFileSync(CACHE_MANIFEST, JSON.stringify(manifest, null, 2))
}

/**
 * Generate cache key from content
 */
function generateCacheKey(title, series, chapter) {
  const content = `${title}-${series}-${chapter}`
  return crypto.createHash('md5').update(content).digest('hex')
}

/**
 * Get cached background path if exists
 */
function getCachedBackground(cacheKey) {
  const manifest = loadCacheManifest()
  
  if (manifest[cacheKey]) {
    const bgPath = path.join(CACHE_DIR, manifest[cacheKey])
    if (fs.existsSync(bgPath)) {
      return bgPath
    }
  }
  
  return null
}

/**
 * Save background to cache
 */
function cacheBackground(cacheKey, buffer) {
  const filename = `bg-${cacheKey}.webp`
  const bgPath = path.join(CACHE_DIR, filename)
  
  if (!fs.existsSync(CACHE_DIR)) {
    fs.mkdirSync(CACHE_DIR, { recursive: true })
  }
  
  fs.writeFileSync(bgPath, buffer)
  
  // Update manifest
  const manifest = loadCacheManifest()
  manifest[cacheKey] = filename
  saveCacheManifest(manifest)
  
  return bgPath
}

/**
 * Extract theme keywords from chapter title
 */
function extractThemeKeywords(title) {
  const lowercaseTitle = title.toLowerCase()
  
  // Find matching theme
  for (const [keyword, theme] of Object.entries(ILLUSTRATION_THEMES)) {
    if (lowercaseTitle.includes(keyword)) {
      return theme
    }
  }
  
  // If no specific match, return default
  return ILLUSTRATION_THEMES.default
}

/**
 * Generate illustrative character/object prompt for AI with varied styles
 */
function generateIllustrationPrompt(title, series, chapter) {
  const illustration = extractThemeKeywords(title)
  const styleName = selectVisualStyle(title, chapter)
  const style = VISUAL_STYLES[styleName]
  
  const colorHint = series === 'php-basics' 
    ? 'with purple and blue accent colors' 
    : 'with blue and cyan accent colors'
  
  return `${illustration} ${style.suffix}. ${style.description}. ${colorHint}. The illustration should be positioned in the bottom-right area of the image, taking up about 40-50% of the space. The background should be a clean gradient (top-left to bottom-right) that complements the illustration colors but keeps the upper-left area VERY LIGHT and clear for prominent text overlay. Composition: 3:2 landscape orientation with the main subject in bottom-right, leaving the entire upper-left quadrant open and light for title text.`
}

/**
 * Generate fallback gradient background
 */
async function generateFallbackBackground(series) {
  const svg = `
    <svg width="${BG_WIDTH}" height="${BG_HEIGHT}" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" style="stop-color:${SERIES_COLORS[series]?.primary || '#667EEA'};stop-opacity:1" />
          <stop offset="100%" style="stop-color:${SERIES_COLORS[series]?.secondary || '#764BA2'};stop-opacity:1" />
        </linearGradient>
        <radialGradient id="noise" cx="50%" cy="50%" r="50%">
          <stop offset="0%" style="stop-color:#ffffff;stop-opacity:0.1" />
          <stop offset="100%" style="stop-color:#000000;stop-opacity:0.1" />
        </radialGradient>
      </defs>
      <rect width="${BG_WIDTH}" height="${BG_HEIGHT}" fill="url(#grad)" />
      <rect width="${BG_WIDTH}" height="${BG_HEIGHT}" fill="url(#noise)" />
      <circle cx="${BG_WIDTH * 0.2}" cy="${BG_HEIGHT * 0.3}" r="${BG_WIDTH * 0.3}" fill="${SERIES_COLORS[series]?.secondary || '#4A5568'}" opacity="0.2" />
      <circle cx="${BG_WIDTH * 0.8}" cy="${BG_HEIGHT * 0.7}" r="${BG_WIDTH * 0.25}" fill="${SERIES_COLORS[series]?.secondary || '#4A5568'}" opacity="0.15" />
    </svg>
  `
  
  return await sharp(Buffer.from(svg)).png().toBuffer()
}

/**
 * Generate AI illustration using imagen generator
 */
async function generateAIBackground(title, series, chapter) {
  const cacheKey = generateCacheKey(title, series, chapter)
  
  // Check cache first (unless force regenerate)
  if (!FORCE_REGENERATE) {
    const cachedPath = getCachedBackground(cacheKey)
    if (cachedPath) {
      const styleName = selectVisualStyle(title, chapter)
      console.log(`  📦 Using cached illustration (${styleName} style)`)
      return cachedPath
    }
  }
  
  const styleName = selectVisualStyle(title, chapter)
  console.log(`  🎨 Generating AI illustration (${styleName} style)...`)
  
  try {
    let buffer
    
    // Try to use Imagen generator if available
    if (ImageGenerator && process.env.GEMINI_API_KEY) {
      try {
        const prompt = generateIllustrationPrompt(title, series, chapter)
        const generator = new ImageGenerator()
        
        // Generate image at exact size for better quality
        buffer = await generator.generateSingle(prompt)
        
        // Resize to our dimensions, keeping bottom-right focus
        buffer = await sharp(buffer)
          .resize(BG_WIDTH, BG_HEIGHT, {
            fit: 'cover',
            position: 'right bottom' // Keep illustration in bottom-right
          })
          .png()
          .toBuffer()
        
        console.log(`  ✨ AI illustration generated successfully`)
      } catch (aiError) {
        console.warn(`  ⚠️  AI generation failed: ${aiError.message}`)
        console.log(`  🔄 Falling back to gradient background`)
        buffer = await generateFallbackBackground(series)
      }
    } else {
      // Use fallback gradient background
      if (!ImageGenerator) {
        console.log(`  ℹ️  Using fallback gradient (imagen not available)`)
      } else if (!process.env.GEMINI_API_KEY) {
        console.log(`  ℹ️  Using fallback gradient (GEMINI_API_KEY not set)`)
      }
      buffer = await generateFallbackBackground(series)
    }
    
    // Cache the illustration
    const cachedPath = cacheBackground(cacheKey, buffer)
    
    return cachedPath
  } catch (error) {
    console.error(`  ✗ Error generating illustration: ${error.message}`)
    throw error
  }
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
  const charWidth = fontSize * 0.55
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
 * Create SVG text layer with proper styling and optional code snippet (top-left positioning)
 */
function createTextLayer(title, series, chapterNum, codeSnippet = null) {
  // Calculate text wrapping for left-aligned text (less width for left alignment)
  const maxTextWidth = IMAGE_WIDTH * 0.55 // Use ~55% of width for text
  const wrappedLines = wrapText(title, maxTextWidth, 62)
  const lineHeight = 72
  const titleHeight = wrappedLines.length * lineHeight
  
  // Position text in top-left corner
  const hasCode = codeSnippet !== null
  const leftMargin = 60
  const topMargin = 180  // Increased spacing between badge and title
  const titleStartY = topMargin
  
  const colors = SERIES_COLORS[series] || SERIES_COLORS['php-basics']
  const seriesLabel = series === 'php-basics' ? 'PHP Basics' : 'AI/ML for PHP Developers'
  const chapterLabel = chapterNum !== null ? `Chapter ${chapterNum}` : ''
  
  // Build text elements (left-aligned)
  const titleLines = wrappedLines
    .map((line, index) => {
      const y = titleStartY + (index * lineHeight)
      return `
        <!-- Text shadow for readability -->
        <text x="${leftMargin}" y="${y}" text-anchor="start" 
              font-size="62" font-weight="900" 
              fill="#000000" opacity="0.35" 
              style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif">
          ${escapeXml(line)}
        </text>
        <!-- Main text -->
        <text x="${leftMargin}" y="${y - 3}" text-anchor="start" 
              font-size="62" font-weight="900" 
              fill="${colors.text}"
              style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif">
          ${escapeXml(line)}
        </text>`
    })
    .join('\n')
  
  // Add code snippet if provided (positioned lower-left)
  const codeSnippetSVG = hasCode ? createCodeSnippet(codeSnippet, series) : ''
  
  const svg = `
    <svg width="${IMAGE_WIDTH}" height="${IMAGE_HEIGHT}" xmlns="http://www.w3.org/2000/svg">
      <!-- Series badge (top-left corner) -->
      <rect x="${leftMargin - 10}" y="40" width="${seriesLabel.length * 14 + 40}" height="46" 
            rx="23" fill="#000000" opacity="0.6" />
      
      <!-- Series label -->
      <text x="${leftMargin + 10}" y="70" text-anchor="start" 
            font-size="24" font-weight="700" 
            fill="${colors.text}"
            style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif">
        ${escapeXml(seriesLabel)}
      </text>
      
      <!-- Main title with shadow (top-left) -->
      ${titleLines}
      
      <!-- Code snippet (if provided, lower-left) -->
      ${codeSnippetSVG}
      
      <!-- Branding (bottom-left) -->
      <text x="${leftMargin}" y="${IMAGE_HEIGHT - 25}" text-anchor="start" 
            font-size="20" font-weight="600"
            fill="${colors.text}" opacity="0.80"
            style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif">
        codewithphp.com
      </text>
    </svg>
  `
  
  return svg.trim()
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
 * Composite illustration and text into final social image
 */
async function compositeImage(backgroundPath, title, series, chapterNum, outputPath, codeSnippet = null) {
  try {
    // Load illustration/background
    let background = sharp(backgroundPath)
    
    // Resize and crop to exact dimensions, keeping bottom-right focus
    background = background
      .resize(IMAGE_WIDTH, IMAGE_HEIGHT, {
        fit: 'cover',
        position: 'right bottom' // Keep illustration in bottom-right
      })
    
    const colors = SERIES_COLORS[series] || SERIES_COLORS['php-basics']
    
    // Create strategic overlay: lighter in upper-left for text, darker in bottom-right for illustration balance
    const overlayGradient = Buffer.from(`
      <svg width="${IMAGE_WIDTH}" height="${IMAGE_HEIGHT}" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <!-- Radial gradient from upper-left corner -->
          <radialGradient id="overlay" cx="20%" cy="20%" r="100%">
            <stop offset="0%" style="stop-color:#FFFFFF;stop-opacity:0.6" />
            <stop offset="40%" style="stop-color:${colors.primary};stop-opacity:0.2" />
            <stop offset="100%" style="stop-color:#000000;stop-opacity:0.3" />
          </radialGradient>
        </defs>
        <rect width="${IMAGE_WIDTH}" height="${IMAGE_HEIGHT}" fill="url(#overlay)" />
      </svg>
    `)
    
    // Create text layer with optional code snippet
    const textSvg = createTextLayer(title, series, chapterNum, codeSnippet)
    const textBuffer = Buffer.from(textSvg)
    
    // Composite all layers
    await background
      .composite([
        { input: overlayGradient, blend: 'over' },
        { input: textBuffer, blend: 'over' }
      ])
      .jpeg({ quality: 90 })
      .toFile(outputPath)
    
    return true
  } catch (error) {
    console.error(`  ✗ Error compositing image: ${error.message}`)
    throw error
  }
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
  
  // Code snippets disabled - cleaner social images
  const codeSnippet = null
  
  const outputFilename = `${series}-chapter-${chapter}.jpg`
  const outputPath = path.join(OUTPUT_DIR, outputFilename)
  
  try {
    console.log(`\n📄 ${outputFilename}`)
    
    // Generate AI background
    const backgroundPath = await generateAIBackground(displayTitle, series, chapter)
    
    // Composite with text overlay and code snippet
    if (codeSnippet) {
      console.log(`  💻 Adding code snippet...`)
    }
    console.log(`  ✍️  Adding text overlay...`)
    await compositeImage(backgroundPath, displayTitle, series, frontmatter.chapter, outputPath, codeSnippet)
    
    console.log(`  ✅ Generated successfully`)
    return outputFilename
  } catch (error) {
    console.error(`  ✗ Error generating ${outputFilename}:`, error.message)
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
  const outputFilename = `${seriesSlug}-overview.jpg`
  const outputPath = path.join(OUTPUT_DIR, outputFilename)
  
  try {
    console.log(`\n📚 ${outputFilename}`)
    
    // Generate AI background
    const backgroundPath = await generateAIBackground(title, seriesSlug, 'overview')
    
    // Composite with text overlay
    console.log(`  ✍️  Adding text overlay...`)
    await compositeImage(backgroundPath, title, seriesSlug, null, outputPath)
    
    console.log(`  ✅ Generated successfully`)
    return outputFilename
  } catch (error) {
    console.error(`  ✗ Error generating ${outputFilename}:`, error.message)
    return null
  }
}

/**
 * Generate homepage social image
 */
async function generateHomepageImage() {
  const title = 'Learn PHP from First Principles'
  const outputPath = path.join(OUTPUT_DIR, 'homepage.jpg')
  
  try {
    console.log(`\n🏠 homepage.jpg`)
    
    // Generate AI background (using php-basics series style)
    const backgroundPath = await generateAIBackground(title, 'php-basics', 'homepage')
    
    // Composite with text overlay (no series label for homepage)
    console.log(`  ✍️  Adding text overlay...`)
    await compositeImage(backgroundPath, title, 'php-basics', null, outputPath)
    
    console.log(`  ✅ Generated successfully`)
  } catch (error) {
    console.error(`  ✗ Error generating homepage.jpg:`, error.message)
  }
}

/**
 * Main execution
 */
async function main() {
  console.log('🎨 AI-Powered Social Image Generator\n')
  
  if (FORCE_REGENERATE) {
    console.log('🔄 Force regenerate mode: Ignoring cache\n')
  }
  
  // Ensure output and cache directories exist
  if (!fs.existsSync(OUTPUT_DIR)) {
    fs.mkdirSync(OUTPUT_DIR, { recursive: true })
  }
  if (!fs.existsSync(CACHE_DIR)) {
    fs.mkdirSync(CACHE_DIR, { recursive: true })
  }
  
  let generatedCount = 0
  let cachedCount = 0
  
  console.log('═══════════════════════════════════════')
  console.log('  GENERATING SITE-WIDE IMAGES')
  console.log('═══════════════════════════════════════')
  
  // Generate homepage image
  await generateHomepageImage()
  generatedCount++
  
  // Generate series overview images
  const seriesDirs = fs.readdirSync(SERIES_DIR, { withFileTypes: true })
    .filter(item => item.isDirectory())
    .map(item => item.name)
  
  for (const seriesSlug of seriesDirs) {
    const result = await generateSeriesImage(seriesSlug)
    if (result) generatedCount++
  }
  
  console.log('\n═══════════════════════════════════════')
  console.log('  GENERATING CHAPTER IMAGES')
  console.log('═══════════════════════════════════════')
  
  // Generate chapter images
  const markdownFiles = getMarkdownFiles(SERIES_DIR)
  const chapterFiles = markdownFiles.filter(file => {
    const content = fs.readFileSync(file, 'utf-8')
    const frontmatter = parseFrontmatter(content)
    return frontmatter.series && frontmatter.chapter !== undefined
  })
  
  for (const filePath of chapterFiles) {
    const result = await generateChapterImage(filePath)
    if (result) generatedCount++
  }
  
  console.log('\n═══════════════════════════════════════')
  console.log('  SUMMARY')
  console.log('═══════════════════════════════════════')
  console.log(`✨ Generated ${generatedCount} social images`)
  console.log(`📁 Output: ${OUTPUT_DIR}`)
  console.log(`💾 Cache: ${CACHE_DIR}`)
  
  // Display cache stats
  const manifest = loadCacheManifest()
  const cacheKeys = Object.keys(manifest)
  if (cacheKeys.length > 0) {
    console.log(`📦 Cached backgrounds: ${cacheKeys.length}`)
  }
  
  console.log('\n✅ Done!')
}

// Run
main().catch(error => {
  console.error('\n❌ Fatal error:', error)
  console.error(error.stack)
  process.exit(1)
})


