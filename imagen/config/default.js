/**
 * Default configuration for Imagen tool
 */

export default {
  gemini: {
    apiKey: process.env.GEMINI_API_KEY,
    model: 'gemini-2.5-flash-image',
  },
  
  images: {
    defaultSizes: {
      full: { width: 1536, height: 1024 },
      medium: { width: 768, height: 512 },
      thumbnail: { width: 384, height: 256 }
    },
    format: 'webp',
    quality: 90,
    numberOfImages: 1
  },
  
  output: {
    // Use absolute path to ensure images go to correct location
    // Note: This is project-specific. For portability, set via environment variable
    baseDir: process.env.IMAGEN_OUTPUT_DIR || '/Users/dalehurley/Code/PHP-From-Scratch/docs/public/images',
    tempDir: process.env.IMAGEN_TEMP_DIR || '/Users/dalehurley/Code/PHP-From-Scratch/imagen/output'
  },
  
  prompt: {
    // Default aspect ratio for image generation
    aspectRatio: '3:2', // Landscape for hero images
    
    // Creative prompt configuration
    creative: {
      // Brand identity
      brand: {
        name: 'Code with PHP',
        identity: 'innovative AI solutions and cutting-edge technology',
        theme: '1950s comic-style propaganda/pin-up poster'
      }
    }
  }
};

