/**
 * Workflow Automation System
 * Predefined workflows and custom workflow definitions
 */

import { generateBatch } from './batch-generator.js';
import { shouldUseCache, addToCache } from './cache-manager.js';
import { fillTemplate } from '../templates/template-loader.js';
import { generatePrompt } from '../prompt-generator.js';
import { ImageGenerator } from '../generator.js';
import { ImageProcessor } from '../image-processor.js';
import { enforceConsistency, trackElement } from '../intelligence/consistency-engine.js';

/**
 * Predefined workflows
 */
const WORKFLOWS = {
  /**
   * Generate hero images for all chapters in a series
   */
  async generateSeriesHeroes(series, chapters, options = {}) {
    const {
      maintainConsistency = true,
      useCache = true,
      template = null
    } = options;
    
    const results = [];
    
    for (const chapter of chapters) {
      try {
        // Enforce consistency if enabled
        let genOptions = {
          series: chapter.series || series,
          chapter: chapter.chapter,
          slug: chapter.slug || 'hero',
          title: chapter.title,
          content: chapter.content || chapter.description,
          creative: true
        };
        
        if (maintainConsistency) {
          genOptions = enforceConsistency(series, genOptions);
        }
        
        // Check cache
        if (useCache) {
          const prompt = template 
            ? fillTemplate(template, genOptions)
            : `${genOptions.title} - ${genOptions.content}`;
          
          const cached = shouldUseCache(prompt);
          if (cached) {
            results.push({
              chapter: chapter.chapter,
              cached: true,
              path: cached.imagePath
            });
            continue;
          }
        }
        
        // Generate prompt
        const prompt = await generatePrompt({
          prompt: genOptions.title,
          creative: genOptions.creative,
          title: genOptions.title,
          content: genOptions.content,
          style: genOptions.style,
          apiKey: process.env.GEMINI_API_KEY
        });
        
        // Generate image
        const generator = new ImageGenerator();
        const imageBuffer = await generator.generateSingle(prompt);
        
        // Process and save
        const processor = new ImageProcessor();
        const baseDir = options.outputDir || '/docs/public/images';
        const outputPath = `${baseDir}/${series}/chapter-${genOptions.chapter}-${genOptions.slug}`;
        const processed = await processor.processAndSave(imageBuffer, outputPath, ['full', 'thumbnail']);
        
        // Track for consistency
        if (maintainConsistency) {
          trackElement(series, 'characters', genOptions.consistentCharacter, chapter.chapter);
          trackElement(series, 'colorPalettes', genOptions.consistentColors, chapter.chapter);
          trackElement(series, 'styles', genOptions.consistentStyle, chapter.chapter);
        }
        
        // Add to cache
        if (useCache) {
          addToCache(prompt, processed[0].path, {
            series,
            chapter: chapter.chapter
          });
        }
        
        results.push({
          chapter: chapter.chapter,
          success: true,
          files: processed
        });
        
      } catch (error) {
        results.push({
          chapter: chapter.chapter,
          success: false,
          error: error.message
        });
      }
    }
    
    return {
      series,
      total: chapters.length,
      successful: results.filter(r => r.success).length,
      failed: results.filter(r => !r.success).length,
      results
    };
  },
  
  /**
   * Generate images with A/B testing
   */
  async generateWithABTesting(prompt, options = {}) {
    const { testVariants } = await import('../refinement/variant-tester.js');
    
    const results = await testVariants(prompt, {
      variantCount: options.variantCount || 4,
      expectedContent: options.expectedContent,
      expectedStyle: options.expectedStyle,
      apiKey: process.env.GEMINI_API_KEY
    });
    
    return results;
  },
  
  /**
   * Generate with refinement
   */
  async generateWithRefinement(prompt, options = {}) {
    const { refineImage } = await import('../refinement/refiner.js');
    
    const results = await refineImage(prompt, {
      maxIterations: options.maxIterations || 3,
      qualityThreshold: options.qualityThreshold || 35,
      expectedContent: options.expectedContent,
      expectedStyle: options.expectedStyle,
      apiKey: process.env.GEMINI_API_KEY
    });
    
    return results;
  }
};

/**
 * Run workflow by name
 * @param {string} workflowName - Workflow name
 * @param {...any} args - Workflow arguments
 * @returns {Promise<any>} - Workflow results
 */
export async function runWorkflow(workflowName, ...args) {
  const workflow = WORKFLOWS[workflowName];
  
  if (!workflow) {
    throw new Error(`Workflow not found: ${workflowName}`);
  }
  
  if (typeof workflow !== 'function') {
    throw new Error(`Workflow ${workflowName} is not a function`);
  }
  
  return workflow(...args);
}

/**
 * List available workflows
 * @returns {Array<string>} - Workflow names
 */
export function listWorkflows() {
  return Object.keys(WORKFLOWS);
}

/**
 * Register custom workflow
 * @param {string} name - Workflow name
 * @param {Function} workflow - Workflow function
 */
export function registerWorkflow(name, workflow) {
  if (typeof workflow !== 'function') {
    throw new Error('Workflow must be a function');
  }
  
  WORKFLOWS[name] = workflow;
}

export default {
  runWorkflow,
  listWorkflows,
  registerWorkflow,
  WORKFLOWS
};

