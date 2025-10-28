/**
 * Core image generation using Gemini 2.5 Flash Image API
 */

import { GoogleGenAI } from '@google/genai';
import config from '../config/default.js';

export class ImageGenerator {
  constructor(apiKey = null) {
    this.apiKey = apiKey || config.gemini.apiKey || process.env.GEMINI_API_KEY;
    
    if (!this.apiKey) {
      throw new Error('GEMINI_API_KEY is required. Set it in environment or .env file.');
    }
    
    // Initialize Google GenAI client with API key
    this.client = new GoogleGenAI({
      apiKey: this.apiKey
    });
    this.model = config.gemini.model;
  }

  /**
   * Generate images from a prompt
   * 
   * @param {string} prompt - The image generation prompt
   * @param {Object} options - Generation options
   * @param {number} options.numberOfImages - Number of images to generate (default: 1)
   * @param {string} options.aspectRatio - Aspect ratio (default: '3:2')
   * @returns {Promise<Array<{imageBytes: string, mimeType: string}>>}
   */
  async generate(prompt, options = {}) {
    const {
      numberOfImages = config.images.numberOfImages,
      aspectRatio = config.prompt.aspectRatio
    } = options;

    try {
      console.log(`Generating ${numberOfImages} image(s) with prompt: "${prompt.substring(0, 100)}..."`);
      
      // Use generateContent with IMAGE response modality for Gemini 2.5 Flash Image
      const response = await this.client.models.generateContent({
        model: this.model,
        contents: [
          {
            role: 'user',
            parts: [
              {
                text: prompt
              }
            ]
          }
        ],
        generationConfig: {
          responseModalities: ['IMAGE'],
          responseImageCount: numberOfImages,
        }
      });

      // Extract images from response
      const images = [];
      
      if (response.candidates && response.candidates.length > 0) {
        for (const candidate of response.candidates) {
          if (candidate.content && candidate.content.parts) {
            for (const part of candidate.content.parts) {
              if (part.inlineData && part.inlineData.data) {
                images.push({
                  imageBytes: part.inlineData.data,
                  mimeType: part.inlineData.mimeType || 'image/png'
                });
              }
            }
          }
        }
      }

      if (images.length === 0) {
        throw new Error('No images were generated in response');
      }

      console.log(`Successfully generated ${images.length} image(s)`);
      return images;
      
    } catch (error) {
      console.error('Error generating image:', error.message);
      throw new Error(`Image generation failed: ${error.message}`);
    }
  }

  /**
   * Generate a single image and return as Buffer
   * 
   * @param {string} prompt - The image generation prompt
   * @param {Object} options - Generation options
   * @returns {Promise<Buffer>} - Image data as Buffer
   */
  async generateSingle(prompt, options = {}) {
    const images = await this.generate(prompt, { ...options, numberOfImages: 1 });
    
    if (images.length === 0) {
      throw new Error('No image was generated');
    }
    
    // Convert base64 to Buffer
    return Buffer.from(images[0].imageBytes, 'base64');
  }

  /**
   * Generate multiple images with different variations
   * 
   * @param {string} prompt - The image generation prompt
   * @param {number} count - Number of variations to generate
   * @param {Object} options - Generation options
   * @returns {Promise<Array<Buffer>>} - Array of image Buffers
   */
  async generateMultiple(prompt, count = 4, options = {}) {
    const images = await this.generate(prompt, { ...options, numberOfImages: count });
    
    // Convert all to Buffers
    return images.map(img => Buffer.from(img.imageBytes, 'base64'));
  }
}

export default ImageGenerator;

