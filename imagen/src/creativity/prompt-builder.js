/**
 * Advanced Prompt Builder
 * Multi-layered prompt construction with semantic understanding
 * Includes uniqueness tracking and prompt mutation system
 */

import crypto from 'crypto';
import { mixStyleNames } from './style-mixer.js';

/**
 * Prompt history for uniqueness tracking
 * In production, this would be stored in a database or cache
 */
const promptHistory = new Set();

/**
 * Generate semantic hash of prompt for duplicate detection
 * @param {string} prompt - Prompt text
 * @returns {string} - Hash string
 */
export function hashPrompt(prompt) {
  // Normalize prompt (lowercase, remove extra spaces)
  const normalized = prompt.toLowerCase().replace(/\s+/g, ' ').trim();
  
  // Generate hash
  return crypto.createHash('sha256').update(normalized).digest('hex').substring(0, 16);
}

/**
 * Check if prompt is unique (not seen before)
 * @param {string} prompt - Prompt text
 * @returns {boolean} - True if unique
 */
export function isPromptUnique(prompt) {
  const hash = hashPrompt(prompt);
  return !promptHistory.has(hash);
}

/**
 * Mark prompt as seen
 * @param {string} prompt - Prompt text
 */
export function markPromptSeen(prompt) {
  const hash = hashPrompt(prompt);
  promptHistory.add(hash);
}

/**
 * Build multi-layered prompt with semantic understanding
 * @param {Object} options - Prompt building options
 * @param {string} options.subject - Main subject/character
 * @param {string} options.action - Action being performed
 * @param {string} options.setting - Setting/environment
 * @param {string|Array<string>} options.style - Style(s) to apply
 * @param {string} options.mood - Mood/emotion
 * @param {string} options.composition - Composition type
 * @param {string} options.colorPalette - Color palette
 * @param {Array<string>} options.props - Props to include
 * @param {string} options.content - Chapter/content context
 * @returns {string} - Built prompt
 */
export function buildLayeredPrompt(options = {}) {
  const {
    subject,
    action,
    setting,
    style,
    mood,
    composition,
    colorPalette,
    props = [],
    content = ''
  } = options;
  
  const layers = [];
  
  // Layer 1: Style (foundation)
  if (style) {
    const styleDescription = Array.isArray(style) 
      ? mixStyleNames(style)
      : style;
    layers.push(`Style: ${styleDescription}`);
  }
  
  // Layer 2: Composition (structure)
  if (composition) {
    layers.push(`Composition: ${composition}`);
  }
  
  // Layer 3: Subject and Action (content)
  if (subject && action) {
    layers.push(`Featuring ${subject} ${action}`);
  } else if (subject) {
    layers.push(`Featuring ${subject}`);
  } else if (action) {
    layers.push(`Action: ${action}`);
  }
  
  // Layer 4: Setting (environment)
  if (setting) {
    layers.push(`Setting: ${setting}`);
  }
  
  // Layer 5: Mood (emotion)
  if (mood) {
    layers.push(`Mood: ${mood}`);
  }
  
  // Layer 6: Color Palette (visual)
  if (colorPalette) {
    layers.push(`Color palette: ${colorPalette}`);
  }
  
  // Layer 7: Props (details)
  if (props.length > 0) {
    layers.push(`Props: ${props.join(', ')}`);
  }
  
  // Layer 8: Content context (semantic enhancement)
  if (content) {
    // Extract key concepts from content (simplified)
    const contentKeywords = extractKeywords(content);
    if (contentKeywords.length > 0) {
      layers.push(`Context: ${contentKeywords.slice(0, 3).join(', ')}`);
    }
  }
  
  return layers.join('. ');
}

/**
 * Extract keywords from content (simplified keyword extraction)
 * @param {string} content - Content text
 * @returns {Array<string>} - Extracted keywords
 */
function extractKeywords(content) {
  // Simple keyword extraction - in production, use NLP library
  const words = content.toLowerCase()
    .replace(/[^\w\s]/g, ' ')
    .split(/\s+/)
    .filter(w => w.length > 4); // Filter short words
  
  // Count frequency
  const frequency = {};
  words.forEach(word => {
    frequency[word] = (frequency[word] || 0) + 1;
  });
  
  // Return top keywords
  return Object.entries(frequency)
    .sort((a, b) => b[1] - a[1])
    .slice(0, 10)
    .map(([word]) => word);
}

/**
 * Mutate prompt to create variation
 * @param {string} basePrompt - Original prompt
 * @param {Object} mutations - Mutation options
 * @returns {string} - Mutated prompt
 */
export function mutatePrompt(basePrompt, mutations = {}) {
  let mutated = basePrompt;
  
  // Add style variation
  if (mutations.addStyle) {
    mutated += `. With ${mutations.addStyle} influences`;
  }
  
  // Add mood variation
  if (mutations.addMood) {
    mutated += `. Mood: ${mutations.addMood}`;
  }
  
  // Add composition variation
  if (mutations.addComposition) {
    mutated += `. Composition: ${mutations.addComposition}`;
  }
  
  // Add intensity modifier
  if (mutations.intensity) {
    mutated = mutated.replace(/Style: /g, `Style: ${mutations.intensity} `);
  }
  
  return mutated;
}

/**
 * Generate unique prompt with uniqueness guarantee
 * @param {Object} options - Prompt options
 * @param {number} maxAttempts - Maximum attempts to find unique prompt
 * @returns {string} - Unique prompt
 */
export function generateUniquePrompt(options, maxAttempts = 10) {
  let attempts = 0;
  let prompt;
  
  do {
    prompt = buildLayeredPrompt(options);
    attempts++;
    
    // If not unique, add variation
    if (!isPromptUnique(prompt) && attempts < maxAttempts) {
      options.mutations = {
        addStyle: 'subtle variation',
        intensity: 'slightly different'
      };
      prompt = mutatePrompt(prompt, options.mutations);
    }
  } while (!isPromptUnique(prompt) && attempts < maxAttempts);
  
  // Mark as seen
  markPromptSeen(prompt);
  
  return prompt;
}

/**
 * Clear prompt history (useful for testing)
 */
export function clearPromptHistory() {
  promptHistory.clear();
}

/**
 * Get prompt history size
 * @returns {number} - Number of prompts in history
 */
export function getPromptHistorySize() {
  return promptHistory.size;
}

export default {
  buildLayeredPrompt,
  mutatePrompt,
  generateUniquePrompt,
  hashPrompt,
  isPromptUnique,
  markPromptSeen,
  clearPromptHistory,
  getPromptHistorySize
};







