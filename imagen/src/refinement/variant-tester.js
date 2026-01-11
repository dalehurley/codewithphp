/**
 * A/B Testing System
 * Generate multiple variants and compare for best selection
 */

import { ImageGenerator } from '../generator.js';
import { analyzeImage } from '../intelligence/image-analyzer.js';
import { mutatePrompt } from '../creativity/prompt-builder.js';
import { generateRandomStyleMix } from '../creativity/style-mixer.js';
import { getRandomMood } from '../creativity/mood-engine.js';

/**
 * Generate and test variants
 * @param {string} basePrompt - Base prompt
 * @param {Object} options - Testing options
 * @param {number} options.variantCount - Number of variants to generate (default: 4)
 * @param {string} options.expectedContent - Expected content
 * @param {string} options.expectedStyle - Expected style
 * @param {string} options.apiKey - Gemini API key
 * @param {boolean} options.selectBest - Automatically select best (default: true)
 * @returns {Promise<Object>} - Test results
 */
export async function testVariants(basePrompt, options = {}) {
  const {
    variantCount = 4,
    expectedContent = '',
    expectedStyle = '',
    apiKey,
    selectBest = true
  } = options;
  
  const generator = new ImageGenerator(apiKey);
  const variants = [];
  
  console.log(`Generating ${variantCount} variants...`);
  
  // Generate variants with different mutations
  for (let i = 0; i < variantCount; i++) {
    const variantPrompt = createVariantPrompt(basePrompt, i);
    
    console.log(`Generating variant ${i + 1}/${variantCount}...`);
    const imageBuffer = await generator.generateSingle(variantPrompt);
    
    // Analyze variant
    const analysis = await analyzeImage(
      imageBuffer,
      expectedContent,
      expectedStyle,
      apiKey
    );
    
    variants.push({
      variant: i + 1,
      prompt: variantPrompt,
      imageBuffer,
      score: analysis.totalScore,
      analysis
    });
  }
  
  // Sort by score (descending)
  variants.sort((a, b) => b.score - a.score);
  
  const results = {
    variants,
    bestVariant: variants[0],
    worstVariant: variants[variants.length - 1],
    averageScore: variants.reduce((sum, v) => sum + v.score, 0) / variants.length,
    scoreRange: variants[0].score - variants[variants.length - 1].score
  };
  
  if (selectBest) {
    console.log(`Best variant: #${results.bestVariant.variant} (score: ${results.bestVariant.score})`);
  }
  
  return results;
}

/**
 * Create variant prompt with different mutations
 * @param {string} basePrompt - Base prompt
 * @param {number} variantIndex - Variant index
 * @returns {string} - Variant prompt
 */
function createVariantPrompt(basePrompt, variantIndex) {
  const mutations = [
    // Style variation
    () => {
      const randomMix = generateRandomStyleMix(2);
      return mutatePrompt(basePrompt, {
        addStyle: randomMix.map(s => s.style).join(' + ')
      });
    },
    
    // Mood variation
    () => {
      const mood = getRandomMood();
      return mutatePrompt(basePrompt, {
        addMood: mood.name
      });
    },
    
    // Intensity variation
    () => {
      const intensities = ['subtle', 'moderate', 'strong', 'extreme'];
      const intensity = intensities[variantIndex % intensities.length];
      return mutatePrompt(basePrompt, {
        intensity: intensity
      });
    },
    
    // Composition variation
    () => {
      return mutatePrompt(basePrompt, {
        addComposition: 'alternative composition'
      });
    }
  ];
  
  const mutation = mutations[variantIndex % mutations.length];
  return mutation();
}

/**
 * Compare two variants side by side
 * @param {Object} variant1 - First variant
 * @param {Object} variant2 - Second variant
 * @returns {Object} - Comparison results
 */
export function compareVariants(variant1, variant2) {
  const comparison = {
    variant1: {
      score: variant1.score,
      strengths: [],
      weaknesses: []
    },
    variant2: {
      score: variant2.score,
      strengths: [],
      weaknesses: []
    },
    winner: variant1.score > variant2.score ? 1 : 2,
    scoreDifference: Math.abs(variant1.score - variant2.score)
  };
  
  // Compare individual metrics
  const metrics = ['visualQuality', 'contentRelevance', 'styleAdherence', 'technicalQuality', 'overallAppeal'];
  
  metrics.forEach(metric => {
    const score1 = variant1.analysis[metric]?.score || 0;
    const score2 = variant2.analysis[metric]?.score || 0;
    
    if (score1 > score2) {
      comparison.variant1.strengths.push(metric);
      comparison.variant2.weaknesses.push(metric);
    } else if (score2 > score1) {
      comparison.variant2.strengths.push(metric);
      comparison.variant1.weaknesses.push(metric);
    }
  });
  
  return comparison;
}

/**
 * Select best variant based on criteria
 * @param {Array<Object>} variants - Variants array
 * @param {Object} criteria - Selection criteria
 * @param {number} criteria.minScore - Minimum score threshold
 * @param {Array<string>} criteria.prioritizeMetrics - Metrics to prioritize
 * @returns {Object|null} - Best variant or null
 */
export function selectBestVariant(variants, criteria = {}) {
  const {
    minScore = 0,
    prioritizeMetrics = []
  } = criteria;
  
  // Filter by minimum score
  let filtered = variants.filter(v => v.score >= minScore);
  
  if (filtered.length === 0) {
    return variants[0] || null;
  }
  
  // If prioritizing specific metrics, recalculate scores
  if (prioritizeMetrics.length > 0) {
    filtered = filtered.map(v => {
      let weightedScore = 0;
      let totalWeight = 0;
      
      prioritizeMetrics.forEach(metric => {
        const weight = 2; // Double weight for prioritized metrics
        const score = v.analysis[metric]?.score || 0;
        weightedScore += score * weight;
        totalWeight += weight;
      });
      
      // Add other metrics with normal weight
      Object.keys(v.analysis).forEach(metric => {
        if (!prioritizeMetrics.includes(metric)) {
          const score = v.analysis[metric]?.score || 0;
          weightedScore += score;
          totalWeight += 1;
        }
      });
      
      return {
        ...v,
        weightedScore: weightedScore / totalWeight
      };
    });
    
    // Sort by weighted score
    filtered.sort((a, b) => (b.weightedScore || b.score) - (a.weightedScore || a.score));
  } else {
    // Sort by total score
    filtered.sort((a, b) => b.score - a.score);
  }
  
  return filtered[0];
}

export default {
  testVariants,
  compareVariants,
  selectBestVariant
};










