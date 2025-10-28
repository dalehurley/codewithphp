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
                enum: ['php-basics', 'ai-ml-php-developers'],
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
                description: 'Style descriptor for simple mode (e.g., "photorealistic", "illustration", "diagram")',
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
      ],
    }));

    // Handle tool calls
    this.server.setRequestHandler(CallToolRequestSchema, async (request) => {
      if (request.params.name !== 'generate_image') {
        throw new Error(`Unknown tool: ${request.params.name}`);
      }

      return await this.handleGenerateImage(request.params.arguments);
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
      const validSeries = ['php-basics', 'ai-ml-php-developers'];
      if (!validSeries.includes(series)) {
        throw new Error(`Invalid series: ${series}. Must be one of: ${validSeries.join(', ')}`);
      }

      // Generate final prompt
      const finalPrompt = await generatePrompt({
        prompt,
        creative: creative_mode,
        title: title || prompt,
        content: content || '',
        style,
        apiKey: process.env.GEMINI_API_KEY,
      });

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

