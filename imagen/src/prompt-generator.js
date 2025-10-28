/**
 * Creative prompt generator for unique, varied images
 * Ports the Laravel meta-prompt system to JavaScript
 */

import { GoogleGenAI } from '@google/genai';
import config from '../config/default.js';

// Large arrays of creative elements for maximum variation
const CHARACTERS = [
  'Confident brunette with vintage glasses',
  'Athletic blonde with determined expression',
  'Elegant redhead with mysterious smile',
  'Distinguished silver-haired professional',
  'Charismatic dark-haired innovator',
  'Dynamic duo of tech partners',
  'Diverse team of three AI pioneers',
  'Rugged masculine inventor',
  'Graceful feminine scientist',
  'Non-binary tech visionary',
  'Scholarly professor with bow tie',
  'Adventurous explorer with hiking gear',
  'Sophisticated businesswoman with briefcase',
  'Young prodigy with creative energy',
  'Seasoned veteran with wise eyes'
];

const OUTFITS = [
  'lab coat and safety goggles',
  'mechanic coveralls and tool belt',
  'pilot jacket and aviator glasses',
  'sharp business suit and briefcase',
  'nurse uniform and stethoscope',
  'cowboy boots and ranch wear',
  'astronaut suit and helmet',
  'teacher cardigan and glasses',
  'construction hard hat and vest',
  'vintage swimwear and sunglasses',
  'scientist lab attire and clipboard',
  'engineer hard hat and blueprints',
  'chef whites and neckerchief',
  'photographer vest with camera straps',
  'musician formal attire and bow tie'
];

const SETTINGS = [
  'retro laboratory with bubbling beakers',
  'vintage garage with classic cars',
  '1950s diner with neon signs',
  'classic library with towering shelves',
  'antique observatory with telescope',
  'retro train station with steam engine',
  'vintage market with fresh produce',
  'classic theater with spotlights',
  'old factory with massive machinery',
  'retro space station with control panels',
  'art deco skyscraper with geometric patterns',
  'vintage record studio with analog equipment',
  'classic soda fountain with chrome stools',
  'retro bowling alley with pin setters',
  'old-fashioned pharmacy with medicine bottles',
  'vintage radio station with broadcasting booth',
  'classic dance hall with mirror ball',
  'retro drive-in movie theater'
];

const ACTIONS = [
  'operating complex machinery with precision',
  'conducting scientific experiments with curiosity',
  'building architectural marvels with determination',
  'performing on stage with passion',
  'racing vintage vehicles at breakneck speed',
  'solving intricate puzzles with focused intensity',
  'teaching revolutionary concepts to eager students',
  'exploring uncharted territories with courage',
  'creating artistic masterpieces with flowing creativity',
  'leading innovative teams toward breakthrough solutions',
  'inventing revolutionary devices in cluttered workshops',
  'debugging complex code with laser focus',
  'analyzing data patterns on multiple screens',
  'assembling robotic components with steady hands',
  'calibrating sensitive instruments with care'
];

const COLOR_PALETTES = [
  'sunset orange, deep purple, and gold',
  'electric blue, hot pink, and silver',
  'forest green, burgundy, and copper',
  'coral, navy, and cream',
  'turquoise, rust, and ivory',
  'violet, amber, and charcoal',
  'crimson, sage, and bronze',
  'magenta, teal, and pearl',
  'ruby, mint, and graphite'
];

const PROPS = [
  'vintage computers and calculators',
  'classic tools and instruments',
  'retro vehicles and transportation',
  'scientific apparatus and gadgets',
  'art supplies and creative tools',
  'musical instruments and equipment',
  'communication devices and radios',
  'vintage cameras and photography equipment',
  'antique typewriters and printing machines',
  'classic laboratory beakers and test tubes',
  'retro robots and mechanical automatons',
  'vintage medical equipment and stethoscopes',
  'classic aviation instruments and propellers',
  'antique telescopes and navigational tools'
];

const COMPOSITIONS = [
  'dynamic action shot with motion blur',
  'intimate close-up with intense focus',
  'wide establishing shot with epic scope',
  'symmetrical balanced composition',
  'asymmetrical dynamic arrangement',
  'layered depth with foreground/background',
  'circular vortex composition',
  'diagonal leading lines',
  'triangular power pose'
];

/**
 * Get random element from array
 */
function randomElement(array) {
  return array[Math.floor(Math.random() * array.length)];
}

/**
 * Generate creative prompt using meta-prompt system
 * 
 * @param {Object} options - Prompt generation options
 * @param {string} options.title - Blog post title
 * @param {string} options.content - Blog post content (optional)
 * @param {boolean} options.includeBanner - Include text banner (50% default)
 * @returns {string} - Creative image generation prompt
 */
export function generateCreativePrompt(options = {}) {
  const {
    title = 'Untitled',
    content = '',
    includeBanner = Math.random() > 0.5
  } = options;

  // Randomly select creative elements
  const character = randomElement(CHARACTERS);
  const outfit = randomElement(OUTFITS);
  const setting = randomElement(SETTINGS);
  const action = randomElement(ACTIONS);
  const colors = randomElement(COLOR_PALETTES);
  const props = randomElement(PROPS);
  const composition = randomElement(COMPOSITIONS);

  // Optional wildcards (30% chance each)
  const wildcards = [];
  if (Math.random() < 0.3) wildcards.push('AI-powered robotic companion');
  if (Math.random() < 0.3) wildcards.push('dramatic weather effects');
  if (Math.random() < 0.3) wildcards.push('retro-futuristic technology blend');
  if (Math.random() < 0.3) wildcards.push('environmental storytelling details');

  const wildcardText = wildcards.length > 0 ? `Include: ${wildcards.join(', ')}. ` : '';
  const bannerText = includeBanner ? 'Include an attention-grabbing banner with ≤ 8 words that captures the essence. ' : '';

  // Build the meta-prompt
  const metaPrompt = `You are an elite visual prompt engineer with a flair for mid-century advertising art. Create a SINGLE, production-ready prompt that an AI image generator can use to produce a striking hero/featured image for the following blog post title: "${title}".

${content ? `Use the following blog post content for inspiration:\n<blog-post-content>\n${content}\n</blog-post-content>\n` : ''}

The finished artwork must feel like a 1950s comic-style propaganda/pin-up poster while representing ${config.prompt.creative.brand.name}'s brand promise—delivering ${config.prompt.creative.brand.identity}.

**Creative Framework for Maximum Variation:**

${bannerText}Create a ${composition} featuring an extremely good-looking ${character} wearing ${outfit} in a ${setting}, ${action}. Use color palette of ${colors}. Include ${props} as key visual elements. ${wildcardText}

**Art Direction:**
• Style: 1950s propaganda/pin-up poster/comic book advertisement with thick black outlines, vintage advertising/poster aesthetic, halftone shading, slight paper texture overlay.
• Mood: Confident empowerment, playful innovation, retro glamour meets high-tech revolution, explosive energy, empowering transformation, playful innovation, confident optimism.
• Aspect Ratio: Landscape (3:2) optimized for hero images
• Avoid: Generic corporate aesthetics, predictable compositions

**Creative Mandate:** This must feel completely unique and reflect the blog post's content and ${config.prompt.creative.brand.name}'s brand identity as a leader in AI innovation. The image should be visually striking, memorable, and perfectly suited for a blog post header.

### Prompt Output Rules
• Start with a direct instruction to the generator (e.g. "Create a vibrant 1950s propaganda pin-up poster…")
• Include all stylistic directives above in concise, generator-friendly syntax
• Return ONLY the prompt—no commentary, pre-amble, or closing remarks
• Ensure composition works well in 3:2 aspect ratio with room for headline and tagline placement
• Make sure the image captures the blog post essence with maximum visual impact and fun factor

Generate the image prompt now.`;

  return metaPrompt;
}

/**
 * Generate a simple, direct prompt without meta-prompt layer
 * 
 * @param {string} description - Direct image description
 * @param {Object} options - Additional options
 * @param {string} options.style - Style descriptor (optional)
 * @returns {string} - Image generation prompt
 */
export function generateSimplePrompt(description, options = {}) {
  const { style = null } = options;
  
  if (style) {
    return `${style}: ${description}`;
  }
  
  return description;
}

/**
 * Generate final image prompt from meta-prompt using Gemini Pro
 * 
 * @param {string} metaPrompt - The meta-prompt to process
 * @param {string} apiKey - Gemini API key
 * @returns {Promise<string>} - The generated image prompt
 */
async function generateFinalPromptFromMeta(metaPrompt, apiKey) {
  try {
    const client = new GoogleGenAI({ apiKey });
    
    const response = await client.models.generateContent({
      model: 'gemini-2.0-flash-exp',
      contents: [
        {
          role: 'user',
          parts: [{ text: metaPrompt }]
        }
      ]
    });

    // Extract the generated text
    if (response.candidates && response.candidates.length > 0) {
      const candidate = response.candidates[0];
      if (candidate.content && candidate.content.parts) {
        const textParts = candidate.content.parts
          .filter(part => part.text)
          .map(part => part.text)
          .join('');
        
        return textParts.trim();
      }
    }

    throw new Error('No text generated from meta-prompt');
  } catch (error) {
    console.error('Error generating prompt from meta-prompt:', error.message);
    throw new Error(`Failed to generate prompt: ${error.message}`);
  }
}

/**
 * Main prompt generator function
 * Decides whether to use creative or simple prompt
 * 
 * @param {Object} options - Prompt options
 * @param {string} options.prompt - Base prompt/description
 * @param {boolean} options.creative - Use creative meta-prompt system
 * @param {string} options.title - Blog post title (for creative mode)
 * @param {string} options.content - Blog post content (for creative mode)
 * @param {string} options.style - Style descriptor (for simple mode)
 * @param {string} options.apiKey - Gemini API key (for creative mode)
 * @returns {Promise<string>} - Final image generation prompt
 */
export async function generatePrompt(options = {}) {
  const {
    prompt,
    creative = false,
    title = null,
    content = null,
    style = null,
    apiKey = null
  } = options;

  if (creative) {
    // Generate meta-prompt
    const metaPrompt = generateCreativePrompt({
      title: title || prompt,
      content: content || '',
      includeBanner: options.includeBanner
    });
    
    console.log('🎨 Generated meta-prompt, now asking Gemini to create final image prompt...\n');
    
    // Use Gemini to generate the actual image prompt from the meta-prompt
    const finalPrompt = await generateFinalPromptFromMeta(
      metaPrompt, 
      apiKey || process.env.GEMINI_API_KEY
    );
    
    console.log('✨ Final image prompt generated:\n');
    console.log(finalPrompt.substring(0, 300) + '...\n');
    
    return finalPrompt;
  } else {
    // Use simple direct prompt
    return generateSimplePrompt(prompt, { style });
  }
}

export default {
  generatePrompt,
  generateCreativePrompt,
  generateSimplePrompt
};

