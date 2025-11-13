#!/usr/bin/env node

/**
 * MCP Server for Imagen tool
 * Provides image generation capabilities to LLMs via Model Context Protocol
 */

import 'dotenv/config';
import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
} from '@modelcontextprotocol/sdk/types.js';
import path from 'path';
import { ImageGenerator } from './generator.js';
import { ImageProcessor } from './image-processor.js';
import { generatePrompt } from './prompt-generator.js';
import { listAllStyles, getStyle } from './styles.js';
import config from '../config/default.js';

/**
 * MCP Server for image generation
 */
class ImagenMCPServer {
  constructor() {
    this.server = new Server(
      {
        name: 'imagen-server',
        version: '1.0.0',
      },
      {
        capabilities: {
          tools: {},
        },
      }
    );

    this.setupToolHandlers();
    this.setupErrorHandling();
  }

  /**
   * Setup tool handlers
   */
  setupToolHandlers() {
    // List available tools
    this.server.setRequestHandler(ListToolsRequestSchema, async () => ({
      tools: [
        {
          name: 'generate_image',
          description: 'Generate images using Gemini 2.5 Flash Image API for Code with PHP tutorials. Supports both simple prompts and creative meta-prompt generation for unique, vintage-styled hero images.',
          inputSchema: {
            type: 'object',
            properties: {
              prompt: {
                type: 'string',
                description: 'Image generation prompt. For creative mode, this can be the blog post title or description.',
              },
              series: {
                type: 'string',
                enum: ['php-basics', 'ai-ml-php-developers', 'python-developers-love-php-laravel', 'build-crm-laravel-12'],
                description: 'Tutorial series for organizing images (required)',
              },
              chapter: {
                type: 'string',
                description: 'Chapter number (e.g., "01", "15b") (required)',
              },
              slug: {
                type: 'string',
                description: 'Image identifier slug (e.g., "hero", "diagram", "screenshot") (required)',
              },
              creative_mode: {
                type: 'boolean',
                description: 'Use creative meta-prompt generator for unique 1950s-style poster art (default: false)',
                default: false,
              },
              title: {
                type: 'string',
                description: 'Blog post title (used in creative mode for context)',
              },
              content: {
                type: 'string',
                description: 'Blog post content excerpt (used in creative mode for context)',
              },
              style: {
                type: 'string',
                description: 'Visual style name or custom description. Available styles include: realistic, impressionist, abstract, surreal, minimalist, popArt, cubist, graffiti, kawaii, chibi, cartoon, comicBook, manga, whimsical, cyberpunk, steampunk, fantasy, sciFi, noir, romantic, gothic, ukiyoe, boho, retro, artDeco, psychedelic, propaganda, pinUp, portrait, landscape, street, macro, cinematic, diagram, infographic, blueprint, isometric. Can also use custom style descriptions.',
              },
              sizes: {
                type: 'array',
                items: {
                  type: 'string',
                  enum: ['full', 'medium', 'thumbnail'],
                },
                description: 'Image sizes to generate (default: ["full", "thumbnail"])',
                default: ['full', 'thumbnail'],
              },
              count: {
                type: 'number',
                description: 'Number of variations to generate (default: 1)',
                default: 1,
                minimum: 1,
                maximum: 4,
              },
            },
            required: ['prompt', 'series', 'chapter', 'slug'],
          },
        },
        {
          name: 'generate_image_series',
          description: 'Generate a series of images with a consistent theme for multiple chapters. Maintains visual consistency (character, color palette, style) while allowing variation per chapter.',
          inputSchema: {
            type: 'object',
            properties: {
              series: {
                type: 'string',
                enum: ['php-basics', 'ai-ml-php-developers', 'python-developers-love-php-laravel', 'build-crm-laravel-12'],
                description: 'Tutorial series for organizing images (required)',
              },
              theme: {
                type: 'string',
                description: 'Consistent theme description for all images (e.g., "comic book superhero", "vintage poster", "modern illustration") (required)',
              },
              chapters: {
                type: 'array',
                description: 'Array of chapter objects with chapter number, title, and description (required)',
                items: {
                  type: 'object',
                  properties: {
                    chapter: {
                      type: 'string',
                      description: 'Chapter number (e.g., "00", "01", "15b")',
                    },
                    slug: {
                      type: 'string',
                      description: 'Image identifier slug (e.g., "hero", "introduction-hero")',
                    },
                    title: {
                      type: 'string',
                      description: 'Chapter title',
                    },
                    description: {
                      type: 'string',
                      description: 'Chapter description or content excerpt',
                    },
                    prompt: {
                      type: 'string',
                      description: 'Optional specific prompt for this chapter (overrides theme-based generation)',
                    },
                  },
                  required: ['chapter', 'slug', 'title'],
                },
              },
              consistencyOptions: {
                type: 'object',
                description: 'Options for maintaining visual consistency across the series',
                properties: {
                  maintainCharacter: {
                    type: 'boolean',
                    description: 'Use the same character across all images (default: true)',
                    default: true,
                  },
                  maintainColorPalette: {
                    type: 'boolean',
                    description: 'Use the same color palette across all images (default: true)',
                    default: true,
                  },
                  maintainStyle: {
                    type: 'boolean',
                    description: 'Use the same artistic style across all images (default: true)',
                    default: true,
                  },
                  characterDescription: {
                    type: 'string',
                    description: 'Specific character description to use consistently (optional)',
                  },
                  colorPalette: {
                    type: 'string',
                    description: 'Specific color palette to use consistently (optional)',
                  },
                  styleDescription: {
                    type: 'string',
                    description: 'Specific style name or description to use consistently. Available styles: realistic, impressionist, abstract, surreal, minimalist, popArt, cubist, graffiti, kawaii, chibi, cartoon, comicBook, manga, whimsical, cyberpunk, steampunk, fantasy, sciFi, noir, romantic, gothic, ukiyoe, boho, retro, artDeco, psychedelic, propaganda, pinUp, portrait, landscape, street, macro, cinematic, diagram, infographic, blueprint, isometric. Can also use custom descriptions.',
                  },
                },
              },
              creative_mode: {
                type: 'boolean',
                description: 'Use creative meta-prompt generator (default: true)',
                default: true,
              },
              sizes: {
                type: 'array',
                items: {
                  type: 'string',
                  enum: ['full', 'medium', 'thumbnail'],
                },
                description: 'Image sizes to generate (default: ["full", "thumbnail"])',
                default: ['full', 'thumbnail'],
              },
            },
            required: ['series', 'theme', 'chapters'],
          },
        },
        {
          name: 'analyze_image',
          description: 'Analyze generated image for quality, content relevance, and style adherence',
          inputSchema: {
            type: 'object',
            properties: {
              imagePath: {
                type: 'string',
                description: 'Path to image file to analyze',
              },
              expectedContent: {
                type: 'string',
                description: 'Expected content description for relevance scoring',
              },
              expectedStyle: {
                type: 'string',
                description: 'Expected style for adherence verification',
              },
            },
            required: ['imagePath'],
          },
        },
        {
          name: 'batch_generate',
          description: 'Generate multiple images from batch input (CSV/JSON or array)',
          inputSchema: {
            type: 'object',
            properties: {
              input: {
                oneOf: [
                  { type: 'string', description: 'Path to CSV/JSON file' },
                  { type: 'array', description: 'Array of generation configs' }
                ],
                description: 'Batch input - file path or array of configs',
              },
              outputDir: {
                type: 'string',
                description: 'Output directory (optional)',
              },
              continueOnError: {
                type: 'boolean',
                description: 'Continue processing on error (default: true)',
                default: true,
              },
            },
            required: ['input'],
          },
        },
        {
          name: 'test_variants',
          description: 'Generate multiple variants and compare for best selection (A/B testing)',
          inputSchema: {
            type: 'object',
            properties: {
              prompt: {
                type: 'string',
                description: 'Base prompt for variants',
              },
              variantCount: {
                type: 'number',
                description: 'Number of variants to generate (default: 4)',
                default: 4,
                minimum: 2,
                maximum: 8,
              },
              expectedContent: {
                type: 'string',
                description: 'Expected content for analysis',
              },
              expectedStyle: {
                type: 'string',
                description: 'Expected style for analysis',
              },
            },
            required: ['prompt'],
          },
        },
      ],
    }));

    // Handle tool calls
    this.server.setRequestHandler(CallToolRequestSchema, async (request) => {
      if (request.params.name === 'generate_image') {
        return await this.handleGenerateImage(request.params.arguments);
      } else if (request.params.name === 'generate_image_series') {
        return await this.handleGenerateImageSeries(request.params.arguments);
      } else if (request.params.name === 'analyze_image') {
        return await this.handleAnalyzeImage(request.params.arguments);
      } else if (request.params.name === 'batch_generate') {
        return await this.handleBatchGenerate(request.params.arguments);
      } else if (request.params.name === 'test_variants') {
        return await this.handleTestVariants(request.params.arguments);
      } else {
        throw new Error(`Unknown tool: ${request.params.name}`);
      }
    });
  }

  /**
   * Handle generate_image tool call
   */
  async handleGenerateImage(args) {
    try {
      const {
        prompt,
        series,
        chapter,
        slug,
        creative_mode = false,
        title = null,
        content = null,
        style = null,
        sizes = ['full', 'thumbnail'],
        count = 1,
      } = args;

      // Validate required fields
      if (!prompt || !series || !chapter || !slug) {
        throw new Error('Missing required fields: prompt, series, chapter, slug');
      }

      // Validate series
      const validSeries = ['php-basics', 'ai-ml-php-developers', 'python-developers-love-php-laravel', 'build-crm-laravel-12'];
      if (!validSeries.includes(series)) {
        throw new Error(`Invalid series: ${series}. Must be one of: ${validSeries.join(', ')}`);
      }

      // Generate final prompt
      let finalPrompt = await generatePrompt({
        prompt,
        creative: creative_mode,
        title: title || prompt,
        content: content || '',
        style,
        apiKey: process.env.GEMINI_API_KEY,
      });
      
      // Ensure dimension requirements are included for full coverage
      if (!finalPrompt.includes('1536x1024') && !finalPrompt.includes('1536 pixels')) {
        finalPrompt += ' The image must be exactly 1536 pixels wide by 1024 pixels tall and fill the entire frame edge-to-edge with no empty space, borders, or margins. All visual elements should extend to the edges.';
      }

      // Initialize generator
      const generator = new ImageGenerator();

      // Generate image(s)
      const imageBuffers = count === 1
        ? [await generator.generateSingle(finalPrompt)]
        : await generator.generateMultiple(finalPrompt, count);

      // Build output path
      const baseDir = path.isAbsolute(config.output.baseDir)
        ? path.join(config.output.baseDir, series)
        : path.join(process.cwd(), config.output.baseDir, series);
      const filename = `chapter-${chapter}-${slug}`;
      const outputPath = path.join(baseDir, filename);

      // Process and save images
      const processor = new ImageProcessor();
      const allResults = [];

      for (let i = 0; i < imageBuffers.length; i++) {
        const imgBuffer = imageBuffers[i];
        const suffix = count > 1 ? `-${i + 1}` : '';
        const currentOutputPath = outputPath.replace(/(\.[^.]*)?$/, `${suffix}$1`);

        const results = await processor.processAndSave(imgBuffer, currentOutputPath, sizes);
        allResults.push(...results);
      }

      // Build response with relative paths for VitePress
      const files = allResults.map((result) => {
        const relPath = `/images/${series}/${path.basename(result.path)}`;
        return {
          size: result.size,
          path: relPath,
          absolutePath: result.path,
          width: result.width,
          height: result.height,
          bytes: result.bytes,
          formatted: ImageProcessor.formatFileSize(result.bytes),
        };
      });

      // Build markdown reference for the full size image
      const fullImage = files.find((f) => f.size === 'full');
      const markdownRef = fullImage
        ? `![${slug}](${fullImage.path})`
        : '';

      return {
        content: [
          {
            type: 'text',
            text: JSON.stringify(
              {
                success: true,
                prompt: creative_mode ? 'Creative meta-prompt generated' : prompt,
                generatedCount: imageBuffers.length,
                files,
                markdown: markdownRef,
                message: `Successfully generated ${imageBuffers.length} image(s) with ${files.length} size variant(s)`,
              },
              null,
              2
            ),
          },
        ],
      };
    } catch (error) {
      return {
        content: [
          {
            type: 'text',
            text: JSON.stringify(
              {
                success: false,
                error: error.message,
                stack: error.stack,
              },
              null,
              2
            ),
          },
        ],
        isError: true,
      };
    }
  }

  /**
   * Handle generate_image_series tool call
   */
  async handleGenerateImageSeries(args) {
    try {
      const {
        series,
        theme,
        chapters,
        consistencyOptions = {},
        creative_mode = true,
        sizes = ['full', 'thumbnail'],
      } = args;

      // Validate required fields
      if (!series || !theme || !chapters || !Array.isArray(chapters) || chapters.length === 0) {
        throw new Error('Missing required fields: series, theme, chapters (non-empty array)');
      }

      // Validate series
      const validSeries = ['php-basics', 'ai-ml-php-developers', 'python-developers-love-php-laravel', 'build-crm-laravel-12'];
      if (!validSeries.includes(series)) {
        throw new Error(`Invalid series: ${series}. Must be one of: ${validSeries.join(', ')}`);
      }

      // Extract consistency options
      const {
        maintainCharacter = true,
        maintainColorPalette = true,
        maintainStyle = true,
        characterDescription = null,
        colorPalette = null,
        styleDescription = null,
      } = consistencyOptions;

      // Generate consistent elements if needed
      let consistentCharacter = characterDescription;
      let consistentColors = colorPalette;
      let consistentStyle = styleDescription || theme;

      // If maintaining consistency but not provided, generate them once
      if (maintainCharacter && !consistentCharacter) {
        // Use theme to generate a character description
        consistentCharacter = `A ${theme} character`;
      }
      if (maintainColorPalette && !consistentColors) {
        // Generate a color palette based on theme
        consistentColors = this.generateColorPaletteForTheme(theme);
      }

      // Initialize generator
      const generator = new ImageGenerator();
      const processor = new ImageProcessor();
      const allResults = [];
      const chapterResults = [];

      // Process each chapter
      for (const chapterData of chapters) {
        const { chapter, slug, title, description = '', prompt: customPrompt = null } = chapterData;

        if (!chapter || !slug || !title) {
          console.warn(`Skipping invalid chapter data: ${JSON.stringify(chapterData)}`);
          continue;
        }

        // Build prompt for this chapter
        let chapterPrompt;
        if (customPrompt) {
          chapterPrompt = customPrompt;
        } else {
          // Build consistent prompt with theme
          const promptParts = [];
          
          if (maintainStyle) {
            promptParts.push(`A ${consistentStyle} scene`);
          }
          
          if (maintainCharacter && consistentCharacter) {
            promptParts.push(`featuring ${consistentCharacter}`);
          }
          
          // Add chapter-specific content
          promptParts.push(`showing: ${title}`);
          if (description) {
            promptParts.push(`(${description.substring(0, 200)})`);
          }
          
          // Add consistent color palette if maintaining
          if (maintainColorPalette && consistentColors) {
            promptParts.push(`using color palette: ${consistentColors}`);
          }
          
          chapterPrompt = promptParts.join('. ') + '.';
        }

      // Generate final prompt
      let finalPrompt = await generatePrompt({
        prompt: chapterPrompt,
        creative: creative_mode,
        title: title,
        content: description,
        style: maintainStyle ? consistentStyle : null,
        apiKey: process.env.GEMINI_API_KEY,
        consistentCharacter: maintainCharacter ? consistentCharacter : null,
        consistentColors: maintainColorPalette ? consistentColors : null,
        consistentStyle: maintainStyle ? consistentStyle : null,
      });
      
      // Ensure dimension requirements are included for full coverage
      if (!finalPrompt.includes('1536x1024') && !finalPrompt.includes('1536 pixels')) {
        finalPrompt += ' The image must be exactly 1536 pixels wide by 1024 pixels tall and fill the entire frame edge-to-edge with no empty space, borders, or margins. All visual elements should extend to the edges.';
      }

        // Generate image
        const imageBuffer = await generator.generateSingle(finalPrompt);

        // Build output path
        const baseDir = path.isAbsolute(config.output.baseDir)
          ? path.join(config.output.baseDir, series)
          : path.join(process.cwd(), config.output.baseDir, series);
        const filename = `chapter-${chapter}-${slug}`;
        const outputPath = path.join(baseDir, filename);

        // Process and save images
        const results = await processor.processAndSave(imageBuffer, outputPath, sizes);
        
        // Build file info
        const files = results.map((result) => {
          const relPath = `/images/${series}/${path.basename(result.path)}`;
          return {
            size: result.size,
            path: relPath,
            absolutePath: result.path,
            width: result.width,
            height: result.height,
            bytes: result.bytes,
            formatted: ImageProcessor.formatFileSize(result.bytes),
          };
        });

        const fullImage = files.find((f) => f.size === 'full');
        const markdownRef = fullImage ? `![${title}](${fullImage.path})` : '';

        chapterResults.push({
          chapter,
          title,
          files,
          markdown: markdownRef,
          prompt: finalPrompt.substring(0, 200) + '...',
        });

        allResults.push(...files);
      }

      return {
        content: [
          {
            type: 'text',
            text: JSON.stringify(
              {
                success: true,
                theme,
                consistencyOptions: {
                  maintainCharacter,
                  maintainColorPalette,
                  maintainStyle,
                  characterDescription: consistentCharacter,
                  colorPalette: consistentColors,
                  styleDescription: consistentStyle,
                },
                generatedCount: chapterResults.length,
                chapters: chapterResults,
                totalFiles: allResults.length,
                message: `Successfully generated ${chapterResults.length} images with consistent theme: ${theme}`,
              },
              null,
              2
            ),
          },
        ],
      };
    } catch (error) {
      return {
        content: [
          {
            type: 'text',
            text: JSON.stringify(
              {
                success: false,
                error: error.message,
                stack: error.stack,
              },
              null,
              2
            ),
          },
        ],
        isError: true,
      };
    }
  }

  /**
   * Handle analyze_image tool call
   */
  async handleAnalyzeImage(args) {
    try {
      const { imagePath, expectedContent = '', expectedStyle = '' } = args;
      
      if (!imagePath) {
        throw new Error('Missing required field: imagePath');
      }
      
      const { promises: fs } = await import('fs');
      const imageBuffer = await fs.readFile(imagePath);
      
      const { analyzeImage } = await import('./intelligence/image-analyzer.js');
      const analysis = await analyzeImage(
        imageBuffer,
        expectedContent,
        expectedStyle,
        process.env.GEMINI_API_KEY
      );
      
      return {
        content: [
          {
            type: 'text',
            text: JSON.stringify({
              success: true,
              analysis,
              message: 'Image analyzed successfully'
            }, null, 2)
          }
        ]
      };
    } catch (error) {
      return {
        content: [
          {
            type: 'text',
            text: JSON.stringify({
              success: false,
              error: error.message
            }, null, 2)
          }
        ],
        isError: true
      };
    }
  }

  /**
   * Handle batch_generate tool call
   */
  async handleBatchGenerate(args) {
    try {
      const { input, outputDir, continueOnError = true } = args;
      
      if (!input) {
        throw new Error('Missing required field: input');
      }
      
      const { generateBatch } = await import('./workflows/batch-generator.js');
      const results = await generateBatch(input, {
        outputDir,
        continueOnError
      });
      
      return {
        content: [
          {
            type: 'text',
            text: JSON.stringify({
              success: true,
              results,
              message: `Batch generation complete: ${results.successful}/${results.total} successful`
            }, null, 2)
          }
        ]
      };
    } catch (error) {
      return {
        content: [
          {
            type: 'text',
            text: JSON.stringify({
              success: false,
              error: error.message
            }, null, 2)
          }
        ],
        isError: true
      };
    }
  }

  /**
   * Handle test_variants tool call
   */
  async handleTestVariants(args) {
    try {
      const {
        prompt,
        variantCount = 4,
        expectedContent = '',
        expectedStyle = ''
      } = args;
      
      if (!prompt) {
        throw new Error('Missing required field: prompt');
      }
      
      const { testVariants } = await import('./refinement/variant-tester.js');
      const results = await testVariants(prompt, {
        variantCount,
        expectedContent,
        expectedStyle,
        apiKey: process.env.GEMINI_API_KEY
      });
      
      return {
        content: [
          {
            type: 'text',
            text: JSON.stringify({
              success: true,
              results,
              message: `Generated ${variantCount} variants. Best: variant #${results.bestVariant.variant} (score: ${results.bestVariant.score})`
            }, null, 2)
          }
        ]
      };
    } catch (error) {
      return {
        content: [
          {
            type: 'text',
            text: JSON.stringify({
              success: false,
              error: error.message
            }, null, 2)
          }
        ],
        isError: true
      };
    }
  }

  /**
   * Generate color palette based on theme
   * Uses styles system to get appropriate color palettes
   */
  generateColorPaletteForTheme(theme) {
    // Try to match theme to a known style
    const style = getStyle(theme);
    if (style) {
      // Use style keywords to infer color palette
      const keywords = style.keywords || [];
      if (keywords.includes('neon') || keywords.includes('cyberpunk')) {
        return 'neon colors (cyan, magenta, electric blue), dark backgrounds, glowing accents';
      }
      if (keywords.includes('pastel') || keywords.includes('kawaii')) {
        return 'soft pastel colors (pink, lavender, mint), gentle tones, light backgrounds';
      }
      if (keywords.includes('minimalist') || keywords.includes('clean')) {
        return 'monochrome with single accent color, clean whites, subtle grays';
      }
      if (keywords.includes('vintage') || keywords.includes('retro')) {
        return 'warm earth tones (orange, brown, cream), muted secondary colors, sepia accents';
      }
      if (keywords.includes('gothic') || keywords.includes('dark')) {
        return 'deep dark tones (black, deep purple, burgundy), dramatic shadows, high contrast';
      }
    }

    // Theme-specific palettes
    const themePalettes = {
      'comic book': 'vibrant primary colors (red, blue, yellow), bold black outlines, bright highlights',
      'comic book superhero': 'vibrant primary colors (red, blue, yellow), bold black outlines, bright highlights',
      'vintage poster': 'warm earth tones (orange, brown, cream), muted secondary colors, sepia accents',
      'modern illustration': 'saturated colors with high contrast, clean gradients, contemporary palette',
      'retro futuristic': 'neon colors (cyan, magenta, electric blue), dark backgrounds, glowing accents',
      'minimalist': 'monochrome with single accent color, clean whites, subtle grays',
      'cyberpunk': 'neon colors (cyan, magenta, electric blue), dark backgrounds, glowing accents',
      'steampunk': 'warm brass and copper tones, sepia, dark browns, aged textures',
      'fantasy': 'magical colors (purple, gold, emerald), ethereal glows, mystical atmosphere',
      'sci-fi': 'cool tech colors (blue, silver, white), futuristic lighting, metallic surfaces',
      'noir': 'high contrast black and white, dramatic shadows, moody atmosphere',
      'pop art': 'bold vibrant colors (red, yellow, blue), high saturation, graphic contrast',
    };

    // Check for theme matches
    const lowerTheme = theme.toLowerCase();
    for (const [key, palette] of Object.entries(themePalettes)) {
      if (lowerTheme.includes(key)) {
        return palette;
      }
    }

    // Default palette
    return 'vibrant colors with good contrast, professional color scheme';
  }

  /**
   * Setup error handling
   */
  setupErrorHandling() {
    this.server.onerror = (error) => {
      console.error('[MCP Error]', error);
    };

    process.on('SIGINT', async () => {
      await this.server.close();
      process.exit(0);
    });
  }

  /**
   * Start the server
   */
  async start() {
    const transport = new StdioServerTransport();
    await this.server.connect(transport);
    console.error('Imagen MCP server running on stdio');
  }
}

// Start server
const server = new ImagenMCPServer();
server.start().catch(console.error);

