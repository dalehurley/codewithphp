/**
 * Mood & Emotion System
 * Emotion mapping, mood-appropriate palettes, and content-to-mood matching
 */

/**
 * Mood definitions with characteristics
 */
export const MOODS = {
  confident: {
    name: 'Confident',
    description: 'Self-assured, bold, empowering',
    colorPalette: 'bold primary colors, strong contrasts, gold accents',
    lighting: 'bright, clear, well-lit',
    expressions: 'determined gaze, strong posture, assertive stance',
    atmosphere: 'energetic, powerful, inspiring',
    keywords: ['confident', 'bold', 'empowering', 'strong', 'assertive']
  },
  
  playful: {
    name: 'Playful',
    description: 'Fun, lighthearted, energetic',
    colorPalette: 'bright, vibrant colors, pastels, cheerful tones',
    lighting: 'bright, warm, inviting',
    expressions: 'smiling, laughing, animated',
    atmosphere: 'joyful, fun, energetic',
    keywords: ['playful', 'fun', 'cheerful', 'lighthearted', 'energetic']
  },
  
  serious: {
    name: 'Serious',
    description: 'Focused, professional, determined',
    colorPalette: 'muted tones, professional grays, subtle accents',
    lighting: 'even, professional, clear',
    expressions: 'focused, determined, professional',
    atmosphere: 'professional, focused, determined',
    keywords: ['serious', 'professional', 'focused', 'determined', 'business']
  },
  
  mysterious: {
    name: 'Mysterious',
    description: 'Enigmatic, intriguing, shadowy',
    colorPalette: 'dark tones, deep purples, shadowy grays',
    lighting: 'dramatic shadows, low key, moody',
    expressions: 'enigmatic smile, hidden gaze, intriguing',
    atmosphere: 'mysterious, intriguing, shadowy',
    keywords: ['mysterious', 'enigmatic', 'shadowy', 'intriguing', 'hidden']
  },
  
  energetic: {
    name: 'Energetic',
    description: 'Dynamic, vibrant, action-oriented',
    colorPalette: 'vibrant colors, high saturation, electric tones',
    lighting: 'dynamic, high contrast, dramatic',
    expressions: 'animated, dynamic, action-oriented',
    atmosphere: 'dynamic, vibrant, action-packed',
    keywords: ['energetic', 'dynamic', 'vibrant', 'action', 'electric']
  },
  
  calm: {
    name: 'Calm',
    description: 'Peaceful, serene, tranquil',
    colorPalette: 'soft pastels, cool tones, gentle colors',
    lighting: 'soft, diffused, gentle',
    expressions: 'peaceful, serene, relaxed',
    atmosphere: 'peaceful, serene, tranquil',
    keywords: ['calm', 'peaceful', 'serene', 'tranquil', 'relaxed']
  },
  
  determined: {
    name: 'Determined',
    description: 'Resolute, focused, unwavering',
    colorPalette: 'strong colors, bold contrasts, determined tones',
    lighting: 'clear, focused, determined',
    expressions: 'resolute, focused, unwavering',
    atmosphere: 'determined, resolute, focused',
    keywords: ['determined', 'resolute', 'focused', 'unwavering', 'persistent']
  },
  
  romantic: {
    name: 'Romantic',
    description: 'Soft, warm, emotional',
    colorPalette: 'warm tones, soft pinks, gentle colors',
    lighting: 'soft, warm, romantic',
    expressions: 'tender, warm, emotional',
    atmosphere: 'romantic, warm, emotional',
    keywords: ['romantic', 'warm', 'tender', 'emotional', 'soft']
  },
  
  adventurous: {
    name: 'Adventurous',
    description: 'Bold, exploratory, daring',
    colorPalette: 'bold colors, adventurous tones, dynamic',
    lighting: 'dramatic, adventurous, bold',
    expressions: 'bold, exploratory, daring',
    atmosphere: 'adventurous, bold, exploratory',
    keywords: ['adventurous', 'bold', 'exploratory', 'daring', 'brave']
  },
  
  innovative: {
    name: 'Innovative',
    description: 'Creative, forward-thinking, cutting-edge',
    colorPalette: 'modern colors, tech tones, innovative',
    lighting: 'modern, tech-forward, innovative',
    expressions: 'creative, forward-thinking, innovative',
    atmosphere: 'innovative, creative, cutting-edge',
    keywords: ['innovative', 'creative', 'forward-thinking', 'cutting-edge', 'modern']
  }
};

/**
 * Mood history for variety tracking
 */
const moodHistory = [];

/**
 * Get random mood
 * @param {boolean} avoidRecent - Avoid recently used moods
 * @returns {Object} - Mood object
 */
export function getRandomMood(avoidRecent = true) {
  const moods = Object.values(MOODS);
  
  if (avoidRecent && moodHistory.length > 0) {
    const recent = moodHistory.slice(-3);
    const available = moods.filter(m => 
      !recent.find(r => r.name === m.name)
    );
    
    if (available.length > 0) {
      const selected = available[Math.floor(Math.random() * available.length)];
      moodHistory.push(selected);
      return selected;
    }
  }
  
  const selected = moods[Math.floor(Math.random() * moods.length)];
  moodHistory.push(selected);
  return selected;
}

/**
 * Get mood by name
 * @param {string} name - Mood name
 * @returns {Object|null} - Mood object or null
 */
export function getMood(name) {
  const found = Object.values(MOODS).find(
    m => m.name.toLowerCase() === name.toLowerCase()
  );
  return found || null;
}

/**
 * Determine mood from content
 * @param {string} content - Content text
 * @returns {Object} - Best matching mood
 */
export function determineMoodFromContent(content) {
  if (!content) {
    return getRandomMood();
  }
  
  const contentLower = content.toLowerCase();
  const moodScores = {};
  
  // Score each mood based on keyword matches
  Object.values(MOODS).forEach(mood => {
    let score = 0;
    mood.keywords.forEach(keyword => {
      if (contentLower.includes(keyword)) {
        score += 1;
      }
    });
    moodScores[mood.name] = score;
  });
  
  // Find mood with highest score
  const sorted = Object.entries(moodScores)
    .sort((a, b) => b[1] - a[1]);
  
  if (sorted[0][1] > 0) {
    const moodName = sorted[0][0];
    const mood = getMood(moodName);
    if (mood) {
      moodHistory.push(mood);
      return mood;
    }
  }
  
  // Fallback to random if no match
  return getRandomMood();
}

/**
 * Get mood-appropriate color palette
 * @param {Object|string} mood - Mood object or mood name
 * @returns {string} - Color palette description
 */
export function getMoodColorPalette(mood) {
  const moodObj = typeof mood === 'string' ? getMood(mood) : mood;
  return moodObj ? moodObj.colorPalette : 'neutral color palette';
}

/**
 * Get mood-appropriate lighting
 * @param {Object|string} mood - Mood object or mood name
 * @returns {string} - Lighting description
 */
export function getMoodLighting(mood) {
  const moodObj = typeof mood === 'string' ? getMood(mood) : mood;
  return moodObj ? moodObj.lighting : 'neutral lighting';
}

/**
 * Get mood-appropriate expressions
 * @param {Object|string} mood - Mood object or mood name
 * @returns {string} - Expression description
 */
export function getMoodExpressions(mood) {
  const moodObj = typeof mood === 'string' ? getMood(mood) : mood;
  return moodObj ? moodObj.expressions : 'neutral expression';
}

/**
 * Clear mood history
 */
export function clearMoodHistory() {
  moodHistory.length = 0;
}

/**
 * Get mood history
 * @returns {Array<Object>} - Recent moods
 */
export function getMoodHistory() {
  return [...moodHistory];
}

export default {
  MOODS,
  getRandomMood,
  getMood,
  determineMoodFromContent,
  getMoodColorPalette,
  getMoodLighting,
  getMoodExpressions,
  clearMoodHistory,
  getMoodHistory
};










