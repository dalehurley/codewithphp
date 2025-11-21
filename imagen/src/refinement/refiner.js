/**
 * Iterative Refinement System
 * Generate → Analyze → Refine workflow for improved quality
 */

import { analyzeImage, meetsQualityThreshold } from '../intelligence/image-analyzer.js';
import { ImageGenerator } from '../generator.js';
import { mutatePrompt } from '../creativity/prompt-builder.js';

/**
 * Refine image through iterative generation
 * @param {string} initialPrompt - Initial prompt
 * @param {Object} options - Refinement options
 * @param {number} options.maxIterations - Maximum refinement iterations (default: 3)
 * @param {number} options.qualityThreshold - Quality threshold to stop (default: 35)
 * @param {string} options.expectedContent - Expected content for analysis
 * @param {string} options.expectedStyle - Expected style for analysis
 * @param {string} options.apiKey - Gemini API key
 * @returns {Promise<Object>} - Refinement results
 */
export async function refineImage(initialPrompt, options = {}) {
  const {
    maxIterations = 3,
    qualityThreshold = 35,
    expectedContent = '',
    expectedStyle = '',
    apiKey
  } = options;
  
  const generator = new ImageGenerator(apiKey);
  const results = {
    iterations: [],
    bestImage: null,
    bestScore: 0,
    bestPrompt: initialPrompt,
    finalIteration: 0
  };
  
  let currentPrompt = initialPrompt;
  let bestImageBuffer = null;
  
  for (let iteration = 1; iteration <= maxIterations; iteration++) {
    console.log(`Refinement iteration ${iteration}/${maxIterations}...`);
    
    // Generate image
    const imageBuffer = await generator.generateSingle(currentPrompt);
    
    // Analyze image
    const analysis = await analyzeImage(
      imageBuffer,
      expectedContent,
      expectedStyle,
      apiKey
    );
    
    // Store iteration results
    results.iterations.push({
      iteration,
      prompt: currentPrompt,
      score: analysis.totalScore,
      analysis
    });
    
    // Check if this is the best so far
    if (analysis.totalScore > results.bestScore) {
      results.bestScore = analysis.totalScore;
      results.bestImage = imageBuffer;
      results.bestPrompt = currentPrompt;
      bestImageBuffer = imageBuffer;
    }
    
    // Check if quality threshold met
    if (meetsQualityThreshold(analysis, qualityThreshold)) {
      console.log(`Quality threshold met at iteration ${iteration}`);
      results.finalIteration = iteration;
      break;
    }
    
    // Refine prompt for next iteration
    if (iteration < maxIterations) {
      currentPrompt = refinePrompt(currentPrompt, analysis);
    }
    
    results.finalIteration = iteration;
  }
  
  // Return best image
  results.bestImage = bestImageBuffer || results.iterations[results.iterations.length - 1]?.imageBuffer;
  
  return results;
}

/**
 * Refine prompt based on analysis feedback
 * @param {string} prompt - Current prompt
 * @param {Object} analysis - Analysis results
 * @returns {string} - Refined prompt
 */
function refinePrompt(prompt, analysis) {
  let refined = prompt;
  const improvements = [];
  
  // Improve visual quality
  if (analysis.visualQuality.score < 7) {
    improvements.push('enhanced composition');
    improvements.push('improved color harmony');
  }
  
  // Improve content relevance
  if (analysis.contentRelevance.score < 7) {
    improvements.push('better content alignment');
  }
  
  // Improve style adherence
  if (analysis.styleAdherence.score < 7) {
    improvements.push('stronger style application');
  }
  
  // Improve technical quality
  if (analysis.technicalQuality.score < 7) {
    improvements.push('enhanced sharpness');
    improvements.push('better contrast');
  }
  
  // Add improvements to prompt
  if (improvements.length > 0) {
    refined += `. Improvements: ${improvements.join(', ')}.`;
  }
  
  // Mutate prompt for variety
  refined = mutatePrompt(refined, {
    intensity: 'enhanced',
    addComposition: 'refined composition'
  });
  
  return refined;
}

/**
 * Quick refine (single iteration)
 * @param {string} prompt - Prompt to refine
 * @param {Object} options - Options
 * @returns {Promise<Object>} - Refinement result
 */
export async function quickRefine(prompt, options = {}) {
  return refineImage(prompt, { ...options, maxIterations: 1 });
}

export default {
  refineImage,
  quickRefine
};









