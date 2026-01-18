#!/usr/bin/env node

/**
 * Data Science Hero Image Generator
 * 
 * Generates unique, high-quality hero images for the Data Science series.
 * Scans markdown files for hero image references and regenerates them using Imagen.
 * 
 * Usage: node scripts/generate-data-science-hero-images.js
 */

import dotenv from 'dotenv'
import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'
import sharp from 'sharp'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)

// Load environment variables
dotenv.config({ path: path.join(__dirname, '../.env') })
dotenv.config({ path: path.join(__dirname, '../imagen/.env') })

// Import Imagen generator
let ImageGenerator = null
try {
  const imagenPath = path.join(__dirname, '../imagen/src/generator.js')
  if (fs.existsSync(imagenPath)) {
    const module = await import(imagenPath)
    ImageGenerator = module.ImageGenerator
  }
} catch (error) {
  console.warn('⚠️  Imagen generator not available')
  process.exit(1)
}

if (!ImageGenerator || !process.env.GEMINI_API_KEY) {
  console.error('❌ Error: ImageGenerator or GEMINI_API_KEY missing')
  process.exit(1)
}

const DOCS_DIR = path.join(__dirname, '../docs')
const PUBLIC_DIR = path.join(DOCS_DIR, 'public')
const SERIES_PATH = path.join(DOCS_DIR, 'series/data-science-php-developers/chapters')

// Visual styles for Data Science
const DS_STYLES = [
  {
    name: 'cyber-grid',
    prompt: 'A futuristic 3D data grid visualization, glowing blue and purple neon lines, dark background, depth of field, cinematic lighting, 8k render, unreal engine 5 style. Abstract representation of data flow and structure.',
  },
  {
    name: 'glass-analytics',
    prompt: 'Glassmorphism style data visualization, floating translucent charts and graphs, clean white and soft blue color palette, soft shadows, high end tech aesthetic, photorealistic 3D render.',
  },
  {
    name: 'neural-network',
    prompt: 'Abstract neural network visualization, interconnected glowing nodes and synapses, deep learning aesthetic, detailed particle effects, data science concept art, dark background with vibrant connections.',
  },
  {
    name: 'isometric-data',
    prompt: 'Isometric 3D illustration of a modern data center or data pipeline, clean geometric shapes, pastel and neon colors, high quality digital art, detailed and organized structure.',
  }
]

// Topic mappings for specific prompts
const TOPIC_PROMPTS = {
  'pandas': 'A stylized 3D representation of a panda character analyzing data tables, digital art, friendly tech aesthetic',
  'python': 'A 3D intertwining of blue and yellow python-like cables forming a data structure, clean and modern',
  'cleaning': 'A futuristic data purification facility, filtering raw chaotic particles into clean organized geometric shapes',
  'visualization': 'A spectacular 3D dashboard interface with floating holograms of bar charts and scatter plots',
  'machine': 'A robotic hand interacting with a glowing data cube, symbolizing machine learning and automation',
  'deployment': 'A digital rocket launch or package delivery drone carrying a glowing data container to a cloud structure',
  'statistics': 'Beautiful 3D mathematical surfaces and bell curves, glowing gaussian distributions, elegant data art'
}

function getMarkdownFiles(dir) {
  return fs.readdirSync(dir)
    .filter(file => file.endsWith('.md'))
    .map(file => path.join(dir, file))
}

function extractHeroImage(content) {
  // Match ![Alt](/path/to/image)
  const match = content.match(/^!\[.*?\]\((.*?)\)/m)
  if (match) {
    return match[1]
  }
  return null
}

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

function getPrompts(title, chapterStr) {
  const chapter = parseInt(chapterStr) || 0
  
  // Deterministic style selection
  const styleIndex = chapter % DS_STYLES.length
  const style = DS_STYLES[styleIndex]
  
  // Check for topic specific overrides
  let specificPrompt = ''
  const lowerTitle = title.toLowerCase()
  for (const [key, prompt] of Object.entries(TOPIC_PROMPTS)) {
    if (lowerTitle.includes(key)) {
      specificPrompt = prompt
      break
    }
  }

  const mainPrompt = specificPrompt || style.prompt
  
  // Construct final prompt
  return `${mainPrompt}. The image should verify subtly represent "${title}". High quality, 8k resolution, suitable for a professional tech blog hero image. No text overlay. Wide 16:9 composition.`
}

async function main() {
  console.log('🚀 Generating Data Science Hero Images...\n')

  const generator = new ImageGenerator()
  const files = getMarkdownFiles(SERIES_PATH)

  for (const filePath of files) {
    const content = fs.readFileSync(filePath, 'utf-8')
    const frontmatter = parseFrontmatter(content)
    const heroPathRel = extractHeroImage(content)

    if (!heroPathRel) {
      console.log(`⚠️  No hero image found in ${path.basename(filePath)}`)
      continue
    }

    const title = frontmatter.title || path.basename(filePath)
    const chapter = frontmatter.chapter || '0'
    
    // Construct absolute path
    // heroPathRel is like /images/data-science-php-developers/...
    const heroPathAbs = path.join(PUBLIC_DIR, heroPathRel.replace(/^\//, ''))
    
    // Ensure directory exists
    const dir = path.dirname(heroPathAbs)
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true })

    console.log(`\n🎨 Processing: ${title}`)
    console.log(`   Target: ${heroPathRel}`)

    // Generate prompt
    const prompt = getPrompts(title, chapter)
    console.log(`   Prompt: ${prompt.substring(0, 100)}...`)

    try {
      // Generate Image
      const buffer = await generator.generateSingle(prompt)
      
      // Save Full Version (Optimized WebP)
      await sharp(buffer)
        .resize(1280, 720, { fit: 'cover' })
        .webp({ quality: 90 })
        .toFile(heroPathAbs)
      
      console.log('   ✅ Saved Full Resolution')

      // Generate Thumbnail if needed
      // Check if there is a corresponding thumbnail file in the dir
      const thumbnailPath = heroPathAbs.replace('hero-full', 'hero-thumbnail')
      
      await sharp(buffer)
        .resize(600, 338, { fit: 'cover' })
        .webp({ quality: 80 })
        .toFile(thumbnailPath)
        
      console.log(`   ✅ Saved Thumbnail: ${path.basename(thumbnailPath)}`)

    } catch (error) {
      console.error(`   ❌ Failed: ${error.message}`)
    }
  }

  console.log('\n✨ Done!')
}

main()
