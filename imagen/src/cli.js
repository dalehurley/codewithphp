#!/usr/bin/env node

/**
 * CLI interface for Imagen tool
 */

import 'dotenv/config';
import { Command } from 'commander';
import path from 'path';
import { fileURLToPath } from 'url';
import { ImageGenerator } from './generator.js';
import { ImageProcessor } from './image-processor.js';
import { generatePrompt } from './prompt-generator.js';
import config from '../config/default.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const program = new Command();

program
  .name('imagen')
  .description('Generate images using Gemini 2.5 Flash Image API')
  .version('1.0.0');

program
  .command('generate')
  .description('Generate an image from a prompt')
  .argument('<prompt>', 'Image generation prompt')
  .option('-s, --series <series>', 'Tutorial series (php-basics, ai-ml-php-developers)')
  .option('-c, --chapter <chapter>', 'Chapter number (e.g., 01, 15b)')
  .option('-l, --slug <slug>', 'Image identifier slug')
  .option('--creative', 'Use creative meta-prompt generator', false)
  .option('--title <title>', 'Blog post title (for creative mode)')
  .option('--content <content>', 'Blog post content (for creative mode)')
  .option('--style <style>', 'Style descriptor (for simple mode)')
  .option('--sizes <sizes>', 'Comma-separated sizes (full,medium,thumbnail)', 'full,thumbnail')
  .option('-o, --output <path>', 'Custom output path')
  .option('-n, --count <number>', 'Number of variations to generate', '1')
  .action(async (promptText, options) => {
    try {
      console.log('🎨 Imagen - Image Generation Tool\n');

      // Parse sizes
      const sizes = options.sizes.split(',').map(s => s.trim());

      // Validate sizes
      const validSizes = ['full', 'medium', 'thumbnail'];
      for (const size of sizes) {
        if (!validSizes.includes(size)) {
          console.error(`❌ Invalid size: ${size}. Valid sizes: ${validSizes.join(', ')}`);
          process.exit(1);
        }
      }

      // Generate final prompt
      let finalPrompt;
      if (options.creative) {
        console.log('🎭 Using creative two-step prompt generation...\n');
        finalPrompt = await generatePrompt({
          prompt: promptText,
          creative: true,
          title: options.title || promptText,
          content: options.content || '',
          apiKey: process.env.GEMINI_API_KEY
        });
      } else {
        finalPrompt = await generatePrompt({
          prompt: promptText,
          creative: false,
          style: options.style
        });
        console.log(`Prompt: ${finalPrompt}\n`);
      }

      // Initialize generator
      const generator = new ImageGenerator();

      // Generate image(s)
      const count = parseInt(options.count, 10);
      console.log(`Generating ${count} image(s)...\n`);
      
      const imageBuffers = count === 1
        ? [await generator.generateSingle(finalPrompt)]
        : await generator.generateMultiple(finalPrompt, count);

      console.log(`✅ Generated ${imageBuffers.length} image(s)\n`);

      // Determine output path
      let outputPath;
      if (options.output) {
        outputPath = options.output;
      } else if (options.series && options.chapter && options.slug) {
        // Use absolute path if provided, otherwise make it relative to cwd
        const baseDir = path.isAbsolute(config.output.baseDir)
          ? path.join(config.output.baseDir, options.series)
          : path.join(process.cwd(), config.output.baseDir, options.series);
        const filename = `chapter-${options.chapter}-${options.slug}`;
        outputPath = path.join(baseDir, filename);
      } else {
        // Default to temp directory
        const baseDir = path.isAbsolute(config.output.tempDir)
          ? config.output.tempDir
          : path.join(process.cwd(), config.output.tempDir);
        const timestamp = Date.now();
        outputPath = path.join(baseDir, `image-${timestamp}`);
      }

      // Process and save images
      const processor = new ImageProcessor();
      
      for (let i = 0; i < imageBuffers.length; i++) {
        const imgBuffer = imageBuffers[i];
        const suffix = count > 1 ? `-${i + 1}` : '';
        const currentOutputPath = outputPath.replace(/(\.[^.]*)?$/, `${suffix}$1`);
        
        console.log(`Processing image ${i + 1}/${imageBuffers.length}...`);
        const results = await processor.processAndSave(imgBuffer, currentOutputPath, sizes);
        
        console.log('\n📁 Saved files:');
        for (const result of results) {
          const relPath = path.relative(process.cwd(), result.path);
          console.log(`  ${result.size}: ${relPath} (${result.width}×${result.height}, ${ImageProcessor.formatFileSize(result.bytes)})`);
        }
        console.log('');
      }

      // Show VitePress markdown reference
      if (options.series) {
        const relPathFull = `/images/${options.series}/chapter-${options.chapter}-${options.slug}-full.webp`;
        console.log('📝 VitePress markdown reference:');
        console.log(`![${options.slug}](${relPathFull})\n`);
      }

      console.log('✨ Done!\n');

    } catch (error) {
      console.error('\n❌ Error:', error.message);
      if (error.stack) {
        console.error('\nStack trace:', error.stack);
      }
      process.exit(1);
    }
  });

program
  .command('test')
  .description('Test API connection')
  .action(async () => {
    try {
      console.log('🧪 Testing Gemini API connection...\n');
      
      const generator = new ImageGenerator();
      console.log('Generating test image...\n');
      
      const buffer = await generator.generateSingle('A simple blue circle on white background');
      
      console.log('✅ API connection successful!');
      console.log(`Generated image: ${ImageProcessor.formatFileSize(buffer.length)}\n`);
      
    } catch (error) {
      console.error('❌ API test failed:', error.message);
      process.exit(1);
    }
  });

program.parse();

