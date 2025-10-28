/**
 * Image processing: resize, optimize, and convert to WebP
 */

import sharp from 'sharp';
import { promises as fs } from 'fs';
import path from 'path';
import config from '../config/default.js';

export class ImageProcessor {
  constructor(options = {}) {
    this.format = options.format || config.images.format;
    this.quality = options.quality || config.images.quality;
    this.sizes = options.sizes || config.images.defaultSizes;
  }

  /**
   * Process an image buffer: resize, optimize, and convert
   * 
   * @param {Buffer} imageBuffer - Raw image data
   * @param {string} sizeName - Size name (full, medium, thumbnail)
   * @returns {Promise<{buffer: Buffer, width: number, height: number, size: number}>}
   */
  async processImage(imageBuffer, sizeName = 'full') {
    const sizeConfig = this.sizes[sizeName];
    
    if (!sizeConfig) {
      throw new Error(`Unknown size: ${sizeName}. Available: ${Object.keys(this.sizes).join(', ')}`);
    }

    try {
      const { width, height } = sizeConfig;
      
      // Process image with sharp
      const processed = await sharp(imageBuffer)
        .resize(width, height, {
          fit: 'cover',
          position: 'center'
        })
        .webp({ quality: this.quality })
        .toBuffer();

      // Get metadata
      const metadata = await sharp(processed).metadata();

      return {
        buffer: processed,
        width: metadata.width,
        height: metadata.height,
        size: processed.length
      };
      
    } catch (error) {
      throw new Error(`Image processing failed: ${error.message}`);
    }
  }

  /**
   * Process and save image to multiple sizes
   * 
   * @param {Buffer} imageBuffer - Raw image data
   * @param {string} outputPath - Output file path (without size suffix and extension)
   * @param {Array<string>} sizes - Array of size names to generate
   * @returns {Promise<Array<{size: string, path: string, width: number, height: number, bytes: number}>>}
   */
  async processAndSave(imageBuffer, outputPath, sizes = ['full', 'thumbnail']) {
    // Ensure output directory exists
    const outputDir = path.dirname(outputPath);
    await fs.mkdir(outputDir, { recursive: true });

    const results = [];

    for (const sizeName of sizes) {
      try {
        console.log(`Processing ${sizeName} size...`);
        
        const processed = await this.processImage(imageBuffer, sizeName);
        
        // Build output filename with size suffix
        const ext = `.${this.format}`;
        const baseName = path.basename(outputPath, path.extname(outputPath));
        const dir = path.dirname(outputPath);
        const filename = `${baseName}-${sizeName}${ext}`;
        const fullPath = path.join(dir, filename);
        
        // Save to disk
        await fs.writeFile(fullPath, processed.buffer);
        
        console.log(`Saved ${sizeName}: ${filename} (${Math.round(processed.size / 1024)}KB)`);
        
        results.push({
          size: sizeName,
          path: fullPath,
          width: processed.width,
          height: processed.height,
          bytes: processed.size
        });
        
      } catch (error) {
        console.error(`Failed to process ${sizeName}:`, error.message);
        // Continue with other sizes
      }
    }

    return results;
  }

  /**
   * Get file size in human-readable format
   * 
   * @param {number} bytes - Size in bytes
   * @returns {string} - Formatted size
   */
  static formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
  }
}

export default ImageProcessor;

