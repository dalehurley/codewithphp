/**
 * Smart Caching System
 * Semantic similarity detection to avoid regenerating similar images
 */

import crypto from 'crypto';
import { promises as fs } from 'fs';
import path from 'path';

/**
 * Cache storage
 */
const cacheStore = new Map();
const cacheIndexFile = path.join(process.cwd(), 'imagen', '.cache-index.json');

/**
 * Load cache index from disk
 */
export async function loadCacheIndex() {
  try {
    const content = await fs.readFile(cacheIndexFile, 'utf-8');
    const index = JSON.parse(content);
    
    // Load into memory
    Object.entries(index).forEach(([hash, entry]) => {
      cacheStore.set(hash, entry);
    });
    
    return index;
  } catch (error) {
    // Cache file doesn't exist, start fresh
    return {};
  }
}

/**
 * Save cache index to disk
 */
export async function saveCacheIndex() {
  const index = {};
  cacheStore.forEach((value, key) => {
    index[key] = value;
  });
  
  try {
    await fs.mkdir(path.dirname(cacheIndexFile), { recursive: true });
    await fs.writeFile(cacheIndexFile, JSON.stringify(index, null, 2));
  } catch (error) {
    console.error('Error saving cache index:', error.message);
  }
}

/**
 * Generate semantic hash from prompt
 * @param {string} prompt - Prompt text
 * @returns {string} - Hash string
 */
export function generateSemanticHash(prompt) {
  // Normalize prompt
  const normalized = prompt
    .toLowerCase()
    .replace(/\s+/g, ' ')
    .trim();
  
  // Generate hash
  return crypto.createHash('sha256').update(normalized).digest('hex').substring(0, 16);
}

/**
 * Check if prompt exists in cache
 * @param {string} prompt - Prompt to check
 * @returns {Object|null} - Cache entry or null
 */
export function checkCache(prompt) {
  const hash = generateSemanticHash(prompt);
  return cacheStore.get(hash) || null;
}

/**
 * Add to cache
 * @param {string} prompt - Prompt
 * @param {string} imagePath - Path to generated image
 * @param {Object} metadata - Additional metadata
 */
export function addToCache(prompt, imagePath, metadata = {}) {
  const hash = generateSemanticHash(prompt);
  
  cacheStore.set(hash, {
    prompt,
    hash,
    imagePath,
    timestamp: new Date().toISOString(),
    ...metadata
  });
  
  // Auto-save (async, don't wait)
  saveCacheIndex().catch(console.error);
}

/**
 * Calculate similarity between two prompts
 * @param {string} prompt1 - First prompt
 * @param {string} prompt2 - Second prompt
 * @returns {number} - Similarity score (0-1, higher = more similar)
 */
export function calculateSimilarity(prompt1, prompt2) {
  const words1 = new Set(prompt1.toLowerCase().split(/\s+/));
  const words2 = new Set(prompt2.toLowerCase().split(/\s+/));
  
  // Jaccard similarity
  const intersection = new Set([...words1].filter(x => words2.has(x)));
  const union = new Set([...words1, ...words2]);
  
  return intersection.size / union.size;
}

/**
 * Find similar prompts in cache
 * @param {string} prompt - Prompt to find similar for
 * @param {number} threshold - Similarity threshold (default: 0.8)
 * @returns {Array<Object>} - Similar cache entries
 */
export function findSimilar(prompt, threshold = 0.8) {
  const similar = [];
  
  cacheStore.forEach((entry, hash) => {
    const similarity = calculateSimilarity(prompt, entry.prompt);
    if (similarity >= threshold) {
      similar.push({
        ...entry,
        similarity
      });
    }
  });
  
  // Sort by similarity (descending)
  similar.sort((a, b) => b.similarity - a.similarity);
  
  return similar;
}

/**
 * Check if should use cache (based on similarity)
 * @param {string} prompt - Prompt to check
 * @param {number} similarityThreshold - Similarity threshold (default: 0.9)
 * @returns {Object|null} - Cache entry if similar enough, null otherwise
 */
export function shouldUseCache(prompt, similarityThreshold = 0.9) {
  const similar = findSimilar(prompt, similarityThreshold);
  return similar.length > 0 ? similar[0] : null;
}

/**
 * Get cache statistics
 * @returns {Object} - Cache stats
 */
export function getCacheStats() {
  return {
    totalEntries: cacheStore.size,
    cacheSize: cacheStore.size,
    oldestEntry: getOldestEntry(),
    newestEntry: getNewestEntry()
  };
}

/**
 * Get oldest cache entry
 * @returns {Object|null} - Oldest entry
 */
function getOldestEntry() {
  let oldest = null;
  let oldestTime = Infinity;
  
  cacheStore.forEach(entry => {
    const time = new Date(entry.timestamp).getTime();
    if (time < oldestTime) {
      oldestTime = time;
      oldest = entry;
    }
  });
  
  return oldest;
}

/**
 * Get newest cache entry
 * @returns {Object|null} - Newest entry
 */
function getNewestEntry() {
  let newest = null;
  let newestTime = 0;
  
  cacheStore.forEach(entry => {
    const time = new Date(entry.timestamp).getTime();
    if (time > newestTime) {
      newestTime = time;
      newest = entry;
    }
  });
  
  return newest;
}

/**
 * Clear cache
 */
export function clearCache() {
  cacheStore.clear();
  saveCacheIndex().catch(console.error);
}

/**
 * Remove old cache entries
 * @param {number} maxAge - Maximum age in days
 */
export function removeOldEntries(maxAge = 30) {
  const cutoff = new Date();
  cutoff.setDate(cutoff.getDate() - maxAge);
  
  const keysToDelete = [];
  cacheStore.forEach((entry, key) => {
    const entryDate = new Date(entry.timestamp);
    if (entryDate < cutoff) {
      keysToDelete.push(key);
    }
  });
  
  keysToDelete.forEach(key => cacheStore.delete(key));
  
  if (keysToDelete.length > 0) {
    saveCacheIndex().catch(console.error);
  }
  
  return keysToDelete.length;
}

// Initialize cache on load
loadCacheIndex().catch(console.error);

export default {
  loadCacheIndex,
  saveCacheIndex,
  generateSemanticHash,
  checkCache,
  addToCache,
  calculateSimilarity,
  findSimilar,
  shouldUseCache,
  getCacheStats,
  clearCache,
  removeOldEntries
};



