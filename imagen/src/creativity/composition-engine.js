/**
 * Composition Intelligence Engine
 * Advanced composition rules and dynamic selection for visual variety
 */

/**
 * Composition types with descriptions and rules
 */
export const COMPOSITION_TYPES = {
  // Rule of Thirds
  ruleOfThirds: {
    name: 'Rule of Thirds',
    description: 'Subject placed at intersection points of 3x3 grid',
    keywords: ['off-center', 'grid alignment', 'visual balance'],
    score: 8
  },
  ruleOfThirdsPortrait: {
    name: 'Rule of Thirds Portrait',
    description: 'Portrait orientation with subject on thirds line',
    keywords: ['vertical thirds', 'portrait format'],
    score: 8
  },
  ruleOfThirdsLandscape: {
    name: 'Rule of Thirds Landscape',
    description: 'Landscape orientation with horizon on thirds line',
    keywords: ['horizontal thirds', 'landscape format'],
    score: 8
  },
  
  // Golden Ratio
  goldenSpiral: {
    name: 'Golden Spiral',
    description: 'Composition following Fibonacci spiral',
    keywords: ['spiral', 'fibonacci', 'divine proportion'],
    score: 9
  },
  goldenRatio: {
    name: 'Golden Ratio',
    description: 'Elements aligned to golden ratio grid',
    keywords: ['1.618 ratio', 'harmonious proportions'],
    score: 9
  },
  
  // Symmetry
  perfectSymmetry: {
    name: 'Perfect Symmetry',
    description: 'Mirror image composition',
    keywords: ['mirror', 'reflection', 'balance'],
    score: 7
  },
  radialSymmetry: {
    name: 'Radial Symmetry',
    description: 'Elements radiating from center',
    keywords: ['radial', 'circular', 'center focus'],
    score: 8
  },
  bilateralSymmetry: {
    name: 'Bilateral Symmetry',
    description: 'Symmetrical split down middle',
    keywords: ['split', 'mirror', 'balanced'],
    score: 7
  },
  
  // Leading Lines
  convergingLines: {
    name: 'Converging Lines',
    description: 'Lines converging to focal point',
    keywords: ['convergence', 'focal point', 'depth'],
    score: 9
  },
  parallelLines: {
    name: 'Parallel Lines',
    description: 'Parallel lines creating depth',
    keywords: ['parallel', 'depth', 'perspective'],
    score: 7
  },
  curvedLines: {
    name: 'Curved Lines',
    description: 'S-curve guiding the eye',
    keywords: ['S-curve', 'flow', 'movement'],
    score: 8
  },
  diagonalLines: {
    name: 'Diagonal Lines',
    description: 'Diagonal lines creating energy',
    keywords: ['diagonal', 'energy', 'dynamic'],
    score: 8
  },
  
  // Framing
  frameWithinFrame: {
    name: 'Frame Within Frame',
    description: 'Using elements to frame subject',
    keywords: ['framing', 'layers', 'depth'],
    score: 8
  },
  foregroundFraming: {
    name: 'Foreground Framing',
    description: 'Foreground elements frame background',
    keywords: ['foreground', 'depth', 'layering'],
    score: 8
  },
  
  // Depth
  layeredDepth: {
    name: 'Layered Depth',
    description: 'Multiple planes creating depth',
    keywords: ['layers', 'foreground', 'background', 'depth'],
    score: 8
  },
  shallowDepth: {
    name: 'Shallow Depth of Field',
    description: 'Subject sharp, background blurred',
    keywords: ['bokeh', 'focus', 'isolation'],
    score: 7
  },
  deepFocus: {
    name: 'Deep Focus',
    description: 'Everything in sharp focus',
    keywords: ['sharp', 'detailed', 'comprehensive'],
    score: 7
  },
  
  // Dynamic
  frozenAction: {
    name: 'Frozen Action',
    description: 'Captured moment of action',
    keywords: ['action', 'moment', 'frozen'],
    score: 8
  },
  impliedMovement: {
    name: 'Implied Movement',
    description: 'Suggestion of motion',
    keywords: ['movement', 'direction', 'flow'],
    score: 7
  },
  motionBlur: {
    name: 'Motion Blur',
    description: 'Blur suggesting speed',
    keywords: ['speed', 'motion', 'blur'],
    score: 7
  },
  
  // Negative Space
  minimalistNegative: {
    name: 'Minimalist Negative Space',
    description: 'Lots of empty space emphasizing subject',
    keywords: ['minimal', 'space', 'isolation'],
    score: 8
  },
  negativeSpaceShape: {
    name: 'Negative Space Shape',
    description: 'Empty space creates shape',
    keywords: ['shape', 'silhouette', 'space'],
    score: 9
  },
  balancedSpace: {
    name: 'Balanced Space',
    description: 'Equal positive and negative space',
    keywords: ['balance', 'harmony', 'equilibrium'],
    score: 8
  },
  
  // Advanced
  triangularComposition: {
    name: 'Triangular Composition',
    description: 'Elements form triangle',
    keywords: ['triangle', 'stability', 'power'],
    score: 8
  },
  circularComposition: {
    name: 'Circular Composition',
    description: 'Elements arranged in circle',
    keywords: ['circle', 'unity', 'wholeness'],
    score: 7
  },
  vortexComposition: {
    name: 'Vortex Composition',
    description: 'Spiral drawing eye inward',
    keywords: ['vortex', 'spiral', 'focus'],
    score: 8
  },
  asymmetricBalance: {
    name: 'Asymmetric Balance',
    description: 'Unbalanced but visually harmonious',
    keywords: ['asymmetric', 'balance', 'harmony'],
    score: 8
  }
};

/**
 * Composition history for variety tracking
 */
const compositionHistory = [];

/**
 * Get random composition
 * @param {boolean} avoidRecent - Avoid recently used compositions
 * @returns {Object} - Composition object
 */
export function getRandomComposition(avoidRecent = true) {
  const types = Object.values(COMPOSITION_TYPES);
  
  if (avoidRecent && compositionHistory.length > 0) {
    // Filter out recent compositions
    const recent = compositionHistory.slice(-5);
    const available = types.filter(c => 
      !recent.find(r => r.name === c.name)
    );
    
    if (available.length > 0) {
      const selected = available[Math.floor(Math.random() * available.length)];
      compositionHistory.push(selected);
      return selected;
    }
  }
  
  const selected = types[Math.floor(Math.random() * types.length)];
  compositionHistory.push(selected);
  return selected;
}

/**
 * Get composition by name
 * @param {string} name - Composition name
 * @returns {Object|null} - Composition object or null
 */
export function getComposition(name) {
  const found = Object.values(COMPOSITION_TYPES).find(
    c => c.name.toLowerCase() === name.toLowerCase()
  );
  return found || null;
}

/**
 * Score composition for content match
 * @param {Object} composition - Composition object
 * @param {string} content - Content context
 * @returns {number} - Score (0-10)
 */
export function scoreComposition(composition, content = '') {
  let score = composition.score || 5;
  
  // Boost score if keywords match content
  if (content) {
    const contentLower = content.toLowerCase();
    composition.keywords.forEach(keyword => {
      if (contentLower.includes(keyword)) {
        score += 0.5;
      }
    });
  }
  
  return Math.min(score, 10);
}

/**
 * Get best composition for content
 * @param {string} content - Content context
 * @returns {Object} - Best matching composition
 */
export function getBestComposition(content = '') {
  const types = Object.values(COMPOSITION_TYPES);
  
  // Score all compositions
  const scored = types.map(c => ({
    composition: c,
    score: scoreComposition(c, content)
  }));
  
  // Sort by score
  scored.sort((a, b) => b.score - a.score);
  
  // Return top 3 and pick randomly from them (adds variety)
  const topThree = scored.slice(0, 3);
  const selected = topThree[Math.floor(Math.random() * topThree.length)];
  
  compositionHistory.push(selected.composition);
  return selected.composition;
}

/**
 * Clear composition history
 */
export function clearCompositionHistory() {
  compositionHistory.length = 0;
}

/**
 * Get composition history
 * @returns {Array<Object>} - Recent compositions
 */
export function getCompositionHistory() {
  return [...compositionHistory];
}

export default {
  COMPOSITION_TYPES,
  getRandomComposition,
  getComposition,
  scoreComposition,
  getBestComposition,
  clearCompositionHistory,
  getCompositionHistory
};










