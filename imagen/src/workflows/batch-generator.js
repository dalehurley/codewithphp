/**
 * Batch Generation System
 * Generate multiple images from CSV/JSON input
 */

import { promises as fs } from 'fs';
import path from 'path';
import { generatePrompt } from '../prompt-generator.js';
import { ImageGenerator } from '../generator.js';
import { ImageProcessor } from '../image-processor.js';
import config from '../../config/default.js';

/**
 * Generate images from batch input
 * @param {Array<Object>|string} input - Array of generation configs or path to CSV/JSON file
 * @param {Object} options - Batch options
 * @param {string} options.outputDir - Output directory
 * @param {boolean} options.continueOnError - Continue on error (default: true)
 * @param {Function} options.progressCallback - Progress callback (current, total)
 * @returns {Promise<Object>} - Batch results
 */
export async function generateBatch(input, options = {}) {
  const {
    outputDir = config.output.baseDir,
    continueOnError = true,
    progressCallback = null
  } = options;
  
  // Load input data
  let batchItems = [];
  if (typeof input === 'string') {
    batchItems = await loadBatchFile(input);
  } else if (Array.isArray(input)) {
    batchItems = input;
  } else {
    throw new Error('Input must be array or file path');
  }
  
  const results = {
    total: batchItems.length,
    successful: 0,
    failed: 0,
    items: []
  };
  
  const generator = new ImageGenerator();
  const processor = new ImageProcessor();
  
  // Process each item
  for (let i = 0; i < batchItems.length; i++) {
    const item = batchItems[i];
    
    try {
      if (progressCallback) {
        progressCallback(i + 1, batchItems.length, item);
      }
      
      // Generate prompt
      const prompt = await generatePrompt({
        prompt: item.prompt || item.description,
        creative: item.creative || false,
        title: item.title,
        content: item.content,
        style: item.style,
        apiKey: process.env.GEMINI_API_KEY
      });
      
      // Generate image
      const imageBuffer = await generator.generateSingle(prompt);
      
      // Determine output path
      const series = item.series || 'batch';
      const chapter = item.chapter || String(i + 1).padStart(2, '0');
      const slug = item.slug || 'image';
      const sizes = item.sizes || ['full', 'thumbnail'];
      
      const baseDir = path.isAbsolute(outputDir)
        ? path.join(outputDir, series)
        : path.join(process.cwd(), outputDir, series);
      
      const filename = `chapter-${chapter}-${slug}`;
      const outputPath = path.join(baseDir, filename);
      
      // Process and save
      const processed = await processor.processAndSave(imageBuffer, outputPath, sizes);
      
      results.successful++;
      results.items.push({
        item: i + 1,
        success: true,
        prompt: item.prompt || item.description,
        files: processed.map(p => ({
          size: p.size,
          path: p.path,
          width: p.width,
          height: p.height,
          bytes: p.bytes
        }))
      });
      
    } catch (error) {
      results.failed++;
      results.items.push({
        item: i + 1,
        success: false,
        error: error.message,
        prompt: item.prompt || item.description
      });
      
      if (!continueOnError) {
        throw error;
      }
      
      console.error(`Error processing item ${i + 1}:`, error.message);
    }
  }
  
  return results;
}

/**
 * Load batch file (CSV or JSON)
 * @param {string} filePath - Path to file
 * @returns {Promise<Array<Object>>} - Parsed batch items
 */
async function loadBatchFile(filePath) {
  const ext = path.extname(filePath).toLowerCase();
  const content = await fs.readFile(filePath, 'utf-8');
  
  if (ext === '.json') {
    return JSON.parse(content);
  } else if (ext === '.csv') {
    return parseCSV(content);
  } else {
    throw new Error('Unsupported file format. Use CSV or JSON.');
  }
}

/**
 * Parse CSV content
 * @param {string} csvContent - CSV content
 * @returns {Array<Object>} - Parsed items
 */
function parseCSV(csvContent) {
  const lines = csvContent.split('\n').filter(line => line.trim());
  if (lines.length === 0) return [];
  
  // Parse header
  const headers = lines[0].split(',').map(h => h.trim());
  const items = [];
  
  // Parse rows
  for (let i = 1; i < lines.length; i++) {
    const values = lines[i].split(',').map(v => v.trim());
    const item = {};
    
    headers.forEach((header, index) => {
      item[header] = values[index] || '';
    });
    
    items.push(item);
  }
  
  return items;
}

/**
 * Generate batch from template
 * @param {string} templateName - Template name
 * @param {Array<Object>} data - Data to fill template
 * @param {Object} options - Options
 * @returns {Promise<Object>} - Batch results
 */
export async function generateBatchFromTemplate(templateName, data, options = {}) {
  // Load template (would be implemented with template system)
  const template = {
    prompt: '${title} - ${description}',
    creative: true,
    style: 'cyberpunk'
  };
  
  // Fill template with data
  const batchItems = data.map(item => ({
    ...template,
    prompt: template.prompt.replace(/\$\{(\w+)\}/g, (match, key) => item[key] || match),
    title: item.title,
    description: item.description,
    content: item.content,
    series: item.series,
    chapter: item.chapter,
    slug: item.slug
  }));
  
  return generateBatch(batchItems, options);
}

export default {
  generateBatch,
  generateBatchFromTemplate
};



