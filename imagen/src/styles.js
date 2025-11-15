/**
 * Comprehensive visual style definitions for image generation
 * Provides rich style vocabulary for diverse artistic expressions
 */

/**
 * Artistic & Aesthetic Styles
 */
export const ARTISTIC_STYLES = {
  realistic: {
    name: 'Realistic / Photorealistic',
    description: 'Lifelike details, true-to-life textures, photographic quality',
    keywords: ['photorealistic', 'lifelike', 'true-to-life', 'detailed textures', 'realistic lighting'],
  },
  impressionist: {
    name: 'Impressionist',
    description: 'Soft, blurred edges, visible brush strokes, Monet-like aesthetic',
    keywords: ['impressionist', 'soft edges', 'visible brush strokes', 'loose brushwork', 'atmospheric'],
  },
  abstract: {
    name: 'Abstract',
    description: 'Non-representational shapes, color, and form',
    keywords: ['abstract', 'geometric shapes', 'non-representational', 'color fields', 'form over content'],
  },
  surreal: {
    name: 'Surreal',
    description: 'Dreamlike, strange, or fantastical imagery (e.g., Salvador Dalí)',
    keywords: ['surreal', 'dreamlike', 'fantastical', 'impossible perspectives', 'symbolic imagery'],
  },
  minimalist: {
    name: 'Minimalist',
    description: 'Clean lines, limited colors, lots of negative space',
    keywords: ['minimalist', 'clean lines', 'negative space', 'simple forms', 'reduced palette'],
  },
  popArt: {
    name: 'Pop Art',
    description: 'Bold colors, mass culture imagery, Warhol-style',
    keywords: ['pop art', 'bold colors', 'mass culture', 'halftone dots', 'vibrant contrast'],
  },
  cubist: {
    name: 'Cubist',
    description: 'Geometric forms and fragmented perspectives',
    keywords: ['cubist', 'geometric forms', 'fragmented', 'multiple perspectives', 'angular'],
  },
  graffiti: {
    name: 'Graffiti / Street Art',
    description: 'Bold outlines, spray-paint texture, urban vibe',
    keywords: ['graffiti', 'street art', 'bold outlines', 'spray paint', 'urban', 'tag style'],
  },
};

/**
 * Cute, Fun, and Playful Themes
 */
export const PLAYFUL_STYLES = {
  kawaii: {
    name: 'Kawaii',
    description: 'Japanese cute style; big eyes, pastel colors, round shapes',
    keywords: ['kawaii', 'cute', 'big eyes', 'pastel colors', 'round shapes', 'adorable'],
  },
  chibi: {
    name: 'Chibi',
    description: 'Tiny, exaggerated versions of characters',
    keywords: ['chibi', 'exaggerated', 'tiny', 'super deformed', 'cute proportions'],
  },
  cartoon: {
    name: 'Cartoon',
    description: 'Bold outlines, simplified forms, colorful and expressive',
    keywords: ['cartoon', 'bold outlines', 'simplified', 'expressive', 'colorful'],
  },
  comicBook: {
    name: 'Comic Book',
    description: 'Panel layouts, halftone dots, speech bubbles',
    keywords: ['comic book', 'panel layout', 'halftone dots', 'speech bubbles', 'dynamic action'],
  },
  manga: {
    name: 'Manga / Anime',
    description: 'Stylized Japanese comic or animation aesthetics',
    keywords: ['manga', 'anime', 'stylized', 'dramatic expressions', 'speed lines'],
  },
  whimsical: {
    name: 'Whimsical',
    description: 'Playful, fantastical, often slightly surreal',
    keywords: ['whimsical', 'playful', 'fantastical', 'charming', 'lighthearted'],
  },
};

/**
 * Conceptual & Mood-Based Themes
 */
export const CONCEPTUAL_STYLES = {
  cyberpunk: {
    name: 'Cyberpunk',
    description: 'Neon lights, dystopian tech cityscapes',
    keywords: ['cyberpunk', 'neon lights', 'dystopian', 'futuristic tech', 'dark urban'],
  },
  steampunk: {
    name: 'Steampunk',
    description: 'Victorian style mixed with retro-futuristic machinery',
    keywords: ['steampunk', 'victorian', 'retro-futuristic', 'brass gears', 'steam-powered'],
  },
  fantasy: {
    name: 'Fantasy',
    description: 'Dragons, magic, mythical settings',
    keywords: ['fantasy', 'magical', 'mythical', 'enchanted', 'otherworldly'],
  },
  sciFi: {
    name: 'Sci-Fi',
    description: 'Futuristic tech, outer space, advanced civilizations',
    keywords: ['sci-fi', 'futuristic', 'space', 'advanced tech', 'alien worlds'],
  },
  noir: {
    name: 'Noir',
    description: 'Moody, high-contrast black-and-white, mysterious atmosphere',
    keywords: ['noir', 'high contrast', 'black and white', 'mysterious', 'shadowy'],
  },
  romantic: {
    name: 'Romantic',
    description: 'Soft light, warm tones, emotional imagery',
    keywords: ['romantic', 'soft light', 'warm tones', 'emotional', 'intimate'],
  },
  gothic: {
    name: 'Dark / Gothic',
    description: 'Dramatic lighting, dark tones, macabre mood',
    keywords: ['gothic', 'dark', 'dramatic lighting', 'macabre', 'moody'],
  },
};

/**
 * Cultural & Design-Influenced Themes
 */
export const CULTURAL_STYLES = {
  ukiyoe: {
    name: 'Japanese Ukiyo-e',
    description: 'Traditional woodblock print style',
    keywords: ['ukiyo-e', 'woodblock print', 'traditional japanese', 'flat colors', 'bold outlines'],
  },
  boho: {
    name: 'Boho (Bohemian)',
    description: 'Earthy tones, eclectic patterns, vintage vibe',
    keywords: ['bohemian', 'boho', 'earthy tones', 'eclectic', 'vintage', 'free-spirited'],
  },
  retro: {
    name: 'Retro / Vintage',
    description: 'Nostalgic colors and design (1950s–1990s)',
    keywords: ['retro', 'vintage', 'nostalgic', 'mid-century', 'classic'],
  },
  artDeco: {
    name: 'Art Deco',
    description: 'Elegant geometric patterns, golds, and blacks',
    keywords: ['art deco', 'geometric patterns', 'elegant', 'golds and blacks', 'luxurious'],
  },
  psychedelic: {
    name: 'Psychedelic',
    description: 'Vivid colors, fluid patterns, surreal imagery',
    keywords: ['psychedelic', 'vivid colors', 'fluid patterns', 'trippy', 'swirling'],
  },
  propaganda: {
    name: 'Propaganda Poster',
    description: 'Bold typography, strong imagery, persuasive composition',
    keywords: ['propaganda poster', 'bold typography', 'strong imagery', 'persuasive', 'vintage poster'],
  },
  pinUp: {
    name: 'Pin-Up',
    description: 'Vintage glamour, playful poses, retro aesthetic',
    keywords: ['pin-up', 'vintage glamour', 'playful', 'retro', 'glamorous'],
  },
};

/**
 * Photography Styles (for reference and realistic rendering)
 */
export const PHOTOGRAPHY_STYLES = {
  portrait: {
    name: 'Portrait',
    description: 'Focus on faces or expressions',
    keywords: ['portrait', 'face focus', 'expressive', 'close-up', 'character study'],
  },
  landscape: {
    name: 'Landscape',
    description: 'Nature or scenery shots',
    keywords: ['landscape', 'nature', 'scenery', 'wide view', 'environmental'],
  },
  street: {
    name: 'Street Photography',
    description: 'Candid urban moments',
    keywords: ['street photography', 'candid', 'urban', 'documentary', 'real moments'],
  },
  macro: {
    name: 'Macro',
    description: 'Extreme close-ups',
    keywords: ['macro', 'extreme close-up', 'detailed', 'texture focus', 'intimate'],
  },
  cinematic: {
    name: 'Cinematic',
    description: 'Wide aspect ratio, dramatic lighting, movie-like tone',
    keywords: ['cinematic', 'wide aspect', 'dramatic lighting', 'film-like', 'cinematic composition'],
  },
};

/**
 * Technical & Diagram Styles
 */
export const TECHNICAL_STYLES = {
  diagram: {
    name: 'Technical Diagram',
    description: 'Clean, structured, informational graphics',
    keywords: ['diagram', 'technical', 'clean', 'structured', 'informational', 'schematic'],
  },
  infographic: {
    name: 'Infographic',
    description: 'Data visualization, charts, and information design',
    keywords: ['infographic', 'data visualization', 'charts', 'information design', 'educational'],
  },
  blueprint: {
    name: 'Blueprint',
    description: 'Technical drawings, architectural plans',
    keywords: ['blueprint', 'technical drawing', 'architectural', 'precise', 'measured'],
  },
  isometric: {
    name: 'Isometric',
    description: '3D isometric projection, technical illustration',
    keywords: ['isometric', '3D projection', 'technical illustration', 'geometric', 'precise'],
  },
};

/**
 * All styles combined for easy access
 */
export const ALL_STYLES = {
  ...ARTISTIC_STYLES,
  ...PLAYFUL_STYLES,
  ...CONCEPTUAL_STYLES,
  ...CULTURAL_STYLES,
  ...PHOTOGRAPHY_STYLES,
  ...TECHNICAL_STYLES,
};

/**
 * Style categories for organization
 */
export const STYLE_CATEGORIES = {
  artistic: ARTISTIC_STYLES,
  playful: PLAYFUL_STYLES,
  conceptual: CONCEPTUAL_STYLES,
  cultural: CULTURAL_STYLES,
  photography: PHOTOGRAPHY_STYLES,
  technical: TECHNICAL_STYLES,
};

/**
 * Get style by name (case-insensitive)
 * @param {string} styleName - Name of the style
 * @returns {Object|null} Style object or null if not found
 */
export function getStyle(styleName) {
  if (!styleName) return null;
  
  const normalized = styleName.toLowerCase().replace(/[-\s_]/g, '');
  
  for (const [key, style] of Object.entries(ALL_STYLES)) {
    const keyNormalized = key.toLowerCase().replace(/[-\s_]/g, '');
    const nameNormalized = style.name.toLowerCase().replace(/[-\s_]/g, '');
    
    if (
      keyNormalized === normalized ||
      nameNormalized === normalized ||
      keyNormalized.includes(normalized) ||
      nameNormalized.includes(normalized)
    ) {
      return style;
    }
  }
  
  return null;
}

/**
 * Get style description string for prompts
 * @param {string|Object} style - Style name or style object
 * @returns {string} Style description for use in prompts
 */
export function getStyleDescription(style) {
  if (!style) return null;
  
  const styleObj = typeof style === 'string' ? getStyle(style) : style;
  if (!styleObj) return null;
  
  return `${styleObj.name} style: ${styleObj.description}`;
}

/**
 * Get style keywords for prompt enhancement
 * @param {string|Object} style - Style name or style object
 * @returns {Array<string>} Array of keywords
 */
export function getStyleKeywords(style) {
  if (!style) return [];
  
  const styleObj = typeof style === 'string' ? getStyle(style) : style;
  if (!styleObj) return [];
  
  return styleObj.keywords || [];
}

/**
 * Build comprehensive style prompt from style name
 * @param {string} styleName - Name of the style
 * @param {Object} options - Additional options
 * @param {boolean} options.includeKeywords - Include keywords in prompt (default: true)
 * @param {boolean} options.includeDescription - Include full description (default: true)
 * @returns {string} Formatted style prompt
 */
export function buildStylePrompt(styleName, options = {}) {
  const {
    includeKeywords = true,
    includeDescription = true,
  } = options;
  
  const style = getStyle(styleName);
  if (!style) {
    // If style not found, return as-is (might be custom description)
    return styleName;
  }
  
  const parts = [];
  
  if (includeDescription) {
    parts.push(`${style.name} style: ${style.description}`);
  } else {
    parts.push(style.name);
  }
  
  if (includeKeywords && style.keywords && style.keywords.length > 0) {
    // Include 2-3 most relevant keywords
    const selectedKeywords = style.keywords.slice(0, 3).join(', ');
    parts.push(`Key characteristics: ${selectedKeywords}`);
  }
  
  return parts.join('. ');
}

/**
 * List all available styles grouped by category
 * @returns {Object} Styles organized by category
 */
export function listAllStyles() {
  return STYLE_CATEGORIES;
}

/**
 * Search styles by keyword
 * @param {string} keyword - Keyword to search for
 * @returns {Array<Object>} Matching styles
 */
export function searchStyles(keyword) {
  if (!keyword) return [];
  
  const normalized = keyword.toLowerCase();
  const matches = [];
  
  for (const [key, style] of Object.entries(ALL_STYLES)) {
    if (
      key.toLowerCase().includes(normalized) ||
      style.name.toLowerCase().includes(normalized) ||
      style.description.toLowerCase().includes(normalized) ||
      style.keywords.some(kw => kw.toLowerCase().includes(normalized))
    ) {
      matches.push({ key, ...style });
    }
  }
  
  return matches;
}

export default {
  ARTISTIC_STYLES,
  PLAYFUL_STYLES,
  CONCEPTUAL_STYLES,
  CULTURAL_STYLES,
  PHOTOGRAPHY_STYLES,
  TECHNICAL_STYLES,
  ALL_STYLES,
  STYLE_CATEGORIES,
  getStyle,
  getStyleDescription,
  getStyleKeywords,
  buildStylePrompt,
  listAllStyles,
  searchStyles,
};



