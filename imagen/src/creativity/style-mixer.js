/**
 * Style Mixing Engine
 * Intelligently combines multiple visual styles for unprecedented variety
 * Supports weighted combinations, conflict resolution, and compatibility matrix
 */

import { getStyle, getStyleKeywords, ALL_STYLES } from '../styles.js';

/**
 * Style compatibility matrix
 * Defines which styles work well together and which conflict
 */
const COMPATIBILITY_MATRIX = {
  // High compatibility (work well together)
  high: [
    ['cyberpunk', 'sciFi'],
    ['steampunk', 'retro'],
    ['minimalist', 'abstract'],
    ['popArt', 'comicBook'],
    ['noir', 'gothic'],
    ['romantic', 'whimsical'],
    ['realistic', 'portrait'],
    ['impressionist', 'romantic'],
    ['surreal', 'abstract'],
    ['kawaii', 'cartoon'],
    ['manga', 'anime'],
    ['graffiti', 'popArt'],
    ['artDeco', 'retro'],
    ['boho', 'retro'],
    ['cinematic', 'realistic'],
    ['diagram', 'isometric'],
    ['infographic', 'diagram']
  ],
  
  // Medium compatibility (can work with adjustments)
  medium: [
    ['cyberpunk', 'minimalist'],
    ['steampunk', 'fantasy'],
    ['realistic', 'surreal'],
    ['popArt', 'minimalist'],
    ['noir', 'realistic'],
    ['romantic', 'realistic'],
    ['abstract', 'minimalist'],
    ['cartoon', 'realistic'],
    ['comicBook', 'realistic'],
    ['retro', 'modern']
  ],
  
  // Low compatibility (conflict, need resolution)
  low: [
    ['realistic', 'abstract'],
    ['minimalist', 'popArt'],
    ['noir', 'kawaii'],
    ['gothic', 'whimsical'],
    ['cyberpunk', 'romantic'],
    ['realistic', 'cartoon']
  ]
};

/**
 * Style conflict resolution strategies
 */
const CONFLICT_RESOLUTION = {
  // Blend: Mix both styles evenly
  blend: 'blend',
  // Dominant: One style takes precedence
  dominant: 'dominant',
  // Sequential: Apply styles in layers
  sequential: 'sequential',
  // Hybrid: Create new hybrid style
  hybrid: 'hybrid',
  // Avoid: Skip conflicting combination
  avoid: 'avoid'
};

/**
 * Get compatibility level between two styles
 * @param {string} style1 - First style name
 * @param {string} style2 - Second style name
 * @returns {string} - 'high', 'medium', 'low', or 'unknown'
 */
export function getCompatibility(style1, style2) {
  const s1 = style1.toLowerCase();
  const s2 = style2.toLowerCase();
  
  // Check high compatibility
  for (const pair of COMPATIBILITY_MATRIX.high) {
    if ((pair[0] === s1 && pair[1] === s2) || (pair[0] === s2 && pair[1] === s1)) {
      return 'high';
    }
  }
  
  // Check medium compatibility
  for (const pair of COMPATIBILITY_MATRIX.medium) {
    if ((pair[0] === s1 && pair[1] === s2) || (pair[0] === s2 && pair[1] === s1)) {
      return 'medium';
    }
  }
  
  // Check low compatibility
  for (const pair of COMPATIBILITY_MATRIX.low) {
    if ((pair[0] === s1 && pair[1] === s2) || (pair[0] === s2 && pair[1] === s1)) {
      return 'low';
    }
  }
  
  return 'unknown';
}

/**
 * Resolve style conflicts
 * @param {string} style1 - First style name
 * @param {string} style2 - Second style name
 * @param {Object} weights - Weight object {style1: 0.7, style2: 0.3}
 * @returns {Object} - Resolution strategy and adjusted weights
 */
export function resolveConflict(style1, style2, weights = { style1: 0.5, style2: 0.5 }) {
  const compatibility = getCompatibility(style1, style2);
  
  if (compatibility === 'high') {
    return {
      strategy: CONFLICT_RESOLUTION.blend,
      weights: weights,
      description: `Blend ${style1} and ${style2} styles harmoniously`
    };
  }
  
  if (compatibility === 'medium') {
    // Use dominant style based on weights
    const dominant = weights.style1 > weights.style2 ? style1 : style2;
    return {
      strategy: CONFLICT_RESOLUTION.dominant,
      weights: weights,
      description: `Apply ${dominant} as primary style with ${dominant === style1 ? style2 : style1} influences`
    };
  }
  
  if (compatibility === 'low') {
    // Use sequential layering
    return {
      strategy: CONFLICT_RESOLUTION.sequential,
      weights: weights,
      description: `Layer ${style1} base with ${style2} accents`
    };
  }
  
  // Unknown compatibility - use blend
  return {
    strategy: CONFLICT_RESOLUTION.blend,
    weights: weights,
    description: `Experimentally blend ${style1} and ${style2} styles`
  };
}

/**
 * Mix multiple styles with weights
 * @param {Array<{style: string, weight: number}>} styles - Array of style objects with weights
 * @returns {string} - Combined style description
 */
export function mixStyles(styles) {
  if (!styles || styles.length === 0) {
    return null;
  }
  
  if (styles.length === 1) {
    const styleObj = getStyle(styles[0].style);
    return styleObj ? styleObj.description : styles[0].style;
  }
  
  // Normalize weights
  const totalWeight = styles.reduce((sum, s) => sum + (s.weight || 1), 0);
  const normalizedStyles = styles.map(s => ({
    ...s,
    weight: (s.weight || 1) / totalWeight
  }));
  
  // Sort by weight (descending)
  normalizedStyles.sort((a, b) => b.weight - a.weight);
  
  // Check for conflicts and resolve
  const resolvedStyles = [];
  for (let i = 0; i < normalizedStyles.length; i++) {
    for (let j = i + 1; j < normalizedStyles.length; j++) {
      const style1 = normalizedStyles[i].style;
      const style2 = normalizedStyles[j].style;
      const compatibility = getCompatibility(style1, style2);
      
      if (compatibility === 'low') {
        const resolution = resolveConflict(style1, style2, {
          style1: normalizedStyles[i].weight,
          style2: normalizedStyles[j].weight
        });
        resolvedStyles.push({
          ...normalizedStyles[i],
          resolution: resolution
        });
        break;
      }
    }
    if (!resolvedStyles.find(r => r.style === normalizedStyles[i].style)) {
      resolvedStyles.push(normalizedStyles[i]);
    }
  }
  
  // Build combined description
  const parts = [];
  const primaryStyle = resolvedStyles[0];
  const primaryStyleObj = getStyle(primaryStyle.style);
  
  if (primaryStyleObj) {
    parts.push(`${Math.round(primaryStyle.weight * 100)}% ${primaryStyleObj.name}`);
  } else {
    parts.push(`${Math.round(primaryStyle.weight * 100)}% ${primaryStyle.style}`);
  }
  
  // Add secondary styles
  for (let i = 1; i < Math.min(resolvedStyles.length, 3); i++) {
    const style = resolvedStyles[i];
    const styleObj = getStyle(style.style);
    const percentage = Math.round(style.weight * 100);
    
    if (styleObj) {
      parts.push(`${percentage}% ${styleObj.name}`);
    } else {
      parts.push(`${percentage}% ${style.style}`);
    }
  }
  
  // Add keywords from all styles
  const allKeywords = new Set();
  resolvedStyles.forEach(s => {
    const styleObj = getStyle(s.style);
    if (styleObj && styleObj.keywords) {
      styleObj.keywords.forEach(kw => allKeywords.add(kw));
    }
  });
  
  const keywordsArray = Array.from(allKeywords).slice(0, 5);
  if (keywordsArray.length > 0) {
    parts.push(`Key elements: ${keywordsArray.join(', ')}`);
  }
  
  return parts.join(' + ');
}

/**
 * Generate random style combination
 * @param {number} count - Number of styles to combine (2-4)
 * @returns {Array<{style: string, weight: number}>} - Random style combination
 */
export function generateRandomStyleMix(count = 2) {
  const styleCount = Math.min(Math.max(2, count), 4);
  const styleKeys = Object.keys(ALL_STYLES);
  const selected = [];
  
  // Select random styles
  while (selected.length < styleCount) {
    const randomStyle = styleKeys[Math.floor(Math.random() * styleKeys.length)];
    if (!selected.find(s => s.style === randomStyle)) {
      selected.push({ style: randomStyle, weight: 1 });
    }
  }
  
  // Normalize weights
  const totalWeight = selected.reduce((sum, s) => sum + s.weight, 0);
  selected.forEach(s => {
    s.weight = s.weight / totalWeight;
  });
  
  return selected;
}

/**
 * Mix styles from style names (simple API)
 * @param {string|Array<string>} styles - Style name(s) or array of style names
 * @param {Object} weights - Optional weights object {styleName: 0.7}
 * @returns {string} - Combined style description
 */
export function mixStyleNames(styles, weights = {}) {
  if (typeof styles === 'string') {
    const styleObj = getStyle(styles);
    return styleObj ? styleObj.description : styles;
  }
  
  if (!Array.isArray(styles) || styles.length === 0) {
    return null;
  }
  
  // Convert to style objects with weights
  const totalWeight = styles.reduce((sum, s) => {
    return sum + (weights[s] || 1);
  }, 0);
  
  const styleObjects = styles.map(style => ({
    style: style,
    weight: (weights[style] || 1) / totalWeight
  }));
  
  return mixStyles(styleObjects);
}

/**
 * Get style intensity modifier
 * @param {string} intensity - 'subtle', 'moderate', 'strong', 'extreme'
 * @returns {string} - Intensity modifier text
 */
export function getIntensityModifier(intensity = 'moderate') {
  const modifiers = {
    subtle: 'subtle hints of',
    moderate: 'moderate',
    strong: 'strong',
    extreme: 'extreme, highly stylized'
  };
  
  return modifiers[intensity] || modifiers.moderate;
}

export default {
  mixStyles,
  mixStyleNames,
  getCompatibility,
  resolveConflict,
  generateRandomStyleMix,
  getIntensityModifier
};



