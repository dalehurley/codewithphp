/**
 * Surprise Element Injector
 * Add unexpected creative elements to break patterns and maintain interest
 * Prevents visual fatigue through controlled randomness
 */

/**
 * Surprise element categories
 */
const SURPRISE_ELEMENTS = {
  unexpectedProps: [
    'vintage typewriter in modern setting',
    'retro robot companion',
    'mysterious glowing object',
    'unexpected animal cameo',
    'hidden easter egg detail',
    'surreal floating element',
    'anachronistic technology',
    'unexpected color accent',
    'hidden message or symbol',
    'playful visual pun'
  ],
  
  weatherEffects: [
    'dramatic storm clouds',
    'gentle rain creating atmosphere',
    'sunbeams breaking through',
    'snowflakes drifting',
    'fog creating mystery',
    'wind effects',
    'lightning in background',
    'rainbow accent',
    'aurora-like effects',
    'particle effects'
  ],
  
  lightingSurprises: [
    'dramatic spotlight',
    'neon glow accents',
    'candlelight atmosphere',
    'strobe-like effects',
    'colorful shadows',
    'backlighting creating silhouette',
    'rim lighting',
    'volumetric lighting',
    'god rays',
    'light leaks'
  ],
  
  compositionSurprises: [
    'unexpected angle',
    'reflection in surface',
    'mirror effect',
    'unusual perspective',
    'split screen composition',
    'overlay effect',
    'double exposure',
    'motion trails',
    'time-lapse effect',
    'cinematic framing'
  ],
  
  colorSurprises: [
    'unexpected color pop',
    'monochrome with single accent',
    'color grading effect',
    'vintage color wash',
    'high contrast colors',
    'desaturated with accent',
    'neon color accents',
    'pastel explosion',
    'moody color palette',
    'vibrant color burst'
  ],
  
  textureSurprises: [
    'paper texture overlay',
    'grainy film effect',
    'halftone pattern',
    'vintage texture',
    'glitch effect',
    'watercolor bleed',
    'ink splatter',
    'brush stroke texture',
    'digital noise',
    'vintage poster texture'
  ],
  
  narrativeSurprises: [
    'hidden story element',
    'visual narrative detail',
    'environmental storytelling',
    'character interaction detail',
    'background narrative',
    'symbolic element',
    'metaphorical detail',
    'storytelling prop',
    'narrative easter egg',
    'visual subplot'
  ]
};

/**
 * Surprise injection probability (default 30%)
 */
const DEFAULT_PROBABILITY = 0.3;

/**
 * Inject surprise element into prompt
 * @param {string} prompt - Base prompt
 * @param {Object} options - Injection options
 * @param {number} options.probability - Probability of injection (0-1)
 * @param {Array<string>} options.categories - Categories to use (default: all)
 * @returns {string} - Prompt with surprise element
 */
export function injectSurprise(prompt, options = {}) {
  const {
    probability = DEFAULT_PROBABILITY,
    categories = Object.keys(SURPRISE_ELEMENTS)
  } = options;
  
  // Random chance
  if (Math.random() > probability) {
    return prompt;
  }
  
  // Select random category
  const category = categories[Math.floor(Math.random() * categories.length)];
  const elements = SURPRISE_ELEMENTS[category];
  
  if (!elements || elements.length === 0) {
    return prompt;
  }
  
  // Select random element
  const element = elements[Math.floor(Math.random() * elements.length)];
  
  // Inject into prompt
  return `${prompt}. Include unexpected element: ${element}`;
}

/**
 * Inject multiple surprise elements
 * @param {string} prompt - Base prompt
 * @param {number} count - Number of surprises to inject
 * @returns {string} - Prompt with surprises
 */
export function injectMultipleSurprises(prompt, count = 2) {
  let result = prompt;
  const categories = Object.keys(SURPRISE_ELEMENTS);
  const usedCategories = [];
  
  for (let i = 0; i < count; i++) {
    // Avoid repeating categories
    const available = categories.filter(c => !usedCategories.includes(c));
    if (available.length === 0) break;
    
    const category = available[Math.floor(Math.random() * available.length)];
    usedCategories.push(category);
    
    const elements = SURPRISE_ELEMENTS[category];
    const element = elements[Math.floor(Math.random() * elements.length)];
    
    result += `. Include: ${element}`;
  }
  
  return result;
}

/**
 * Get surprise element by category
 * @param {string} category - Category name
 * @returns {string|null} - Surprise element or null
 */
export function getSurpriseElement(category) {
  const elements = SURPRISE_ELEMENTS[category];
  if (!elements || elements.length === 0) {
    return null;
  }
  
  return elements[Math.floor(Math.random() * elements.length)];
}

/**
 * Get all available surprise categories
 * @returns {Array<string>} - Category names
 */
export function getSurpriseCategories() {
  return Object.keys(SURPRISE_ELEMENTS);
}

/**
 * Check if prompt needs surprise injection (based on pattern detection)
 * @param {string} prompt - Prompt to check
 * @param {Array<string>} recentPrompts - Recent prompts for pattern detection
 * @returns {boolean} - True if surprise needed
 */
export function needsSurprise(prompt, recentPrompts = []) {
  if (recentPrompts.length < 3) {
    return Math.random() < DEFAULT_PROBABILITY;
  }
  
  // Check for repetitive patterns
  const promptWords = prompt.toLowerCase().split(/\s+/);
  let patternCount = 0;
  
  recentPrompts.forEach(recent => {
    const recentWords = recent.toLowerCase().split(/\s+/);
    const commonWords = promptWords.filter(w => recentWords.includes(w));
    if (commonWords.length > 5) {
      patternCount++;
    }
  });
  
  // If pattern detected, increase surprise probability
  if (patternCount >= 2) {
    return true;
  }
  
  return Math.random() < DEFAULT_PROBABILITY;
}

export default {
  injectSurprise,
  injectMultipleSurprises,
  getSurpriseElement,
  getSurpriseCategories,
  needsSurprise
};

