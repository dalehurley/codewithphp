/**
 * Content Intelligence System
 * Extract key concepts, identify content type, and determine visual metaphors
 */

import { GoogleGenAI } from '@google/genai';

/**
 * Analyze content and extract key information
 * @param {string} content - Content text
 * @param {string} apiKey - Gemini API key
 * @returns {Promise<Object>} - Content analysis
 */
export async function analyzeContent(content, apiKey) {
  if (!content || content.trim().length === 0) {
    return getDefaultAnalysis();
  }
  
  try {
    const client = new GoogleGenAI({ apiKey });
    
    const analysisPrompt = `Analyze the following tutorial content and extract key information:

${content}

Please provide:
1. Key Concepts (main topics covered) - Array of strings
2. Content Type (technical, conceptual, tutorial, reference, etc.) - String
3. Technical Level (beginner, intermediate, advanced) - String
4. Visual Metaphors (suggested visual representations) - Array of strings
5. Appropriate Style (suggested visual style) - String
6. Mood/Tone (confident, playful, serious, etc.) - String

Format as JSON:
{
  "keyConcepts": ["concept1", "concept2"],
  "contentType": "technical",
  "technicalLevel": "intermediate",
  "visualMetaphors": ["metaphor1", "metaphor2"],
  "appropriateStyle": "style name",
  "mood": "confident"
}`;

    const response = await client.models.generateContent({
      model: 'gemini-2.5-flash',
      contents: [
        {
          role: 'user',
          parts: [{ text: analysisPrompt }]
        }
      ]
    });

    // Extract analysis from response
    if (response.candidates && response.candidates.length > 0) {
      const candidate = response.candidates[0];
      if (candidate.content && candidate.content.parts) {
        const textParts = candidate.content.parts
          .filter(part => part.text)
          .map(part => part.text)
          .join('');
        
        // Try to parse JSON
        try {
          const jsonMatch = textParts.match(/\{[\s\S]*\}/);
          if (jsonMatch) {
            return JSON.parse(jsonMatch[0]);
          }
        } catch (e) {
          // Fallback to simple extraction
        }
      }
    }
    
    // Fallback to simple analysis
    return simpleContentAnalysis(content);
  } catch (error) {
    console.error('Error analyzing content:', error.message);
    return simpleContentAnalysis(content);
  }
}

/**
 * Simple content analysis (fallback)
 * @param {string} content - Content text
 * @returns {Object} - Simple analysis
 */
function simpleContentAnalysis(content) {
  const contentLower = content.toLowerCase();
  
  // Extract key concepts (simple keyword extraction)
  const words = contentLower
    .replace(/[^\w\s]/g, ' ')
    .split(/\s+/)
    .filter(w => w.length > 4);
  
  const frequency = {};
  words.forEach(word => {
    frequency[word] = (frequency[word] || 0) + 1;
  });
  
  const keyConcepts = Object.entries(frequency)
    .sort((a, b) => b[1] - a[1])
    .slice(0, 5)
    .map(([word]) => word);
  
  // Determine content type
  let contentType = 'tutorial';
  if (contentLower.includes('api') || contentLower.includes('endpoint')) {
    contentType = 'technical';
  } else if (contentLower.includes('concept') || contentLower.includes('theory')) {
    contentType = 'conceptual';
  } else if (contentLower.includes('example') || contentLower.includes('demo')) {
    contentType = 'tutorial';
  }
  
  // Determine technical level
  let technicalLevel = 'intermediate';
  if (contentLower.includes('beginner') || contentLower.includes('introduction') || contentLower.includes('basics')) {
    technicalLevel = 'beginner';
  } else if (contentLower.includes('advanced') || contentLower.includes('expert') || contentLower.includes('optimization')) {
    technicalLevel = 'advanced';
  }
  
  // Determine mood
  let mood = 'confident';
  if (contentLower.includes('fun') || contentLower.includes('playful')) {
    mood = 'playful';
  } else if (contentLower.includes('serious') || contentLower.includes('important')) {
    mood = 'serious';
  } else if (contentLower.includes('mystery') || contentLower.includes('secret')) {
    mood = 'mysterious';
  }
  
  return {
    keyConcepts,
    contentType,
    technicalLevel,
    visualMetaphors: [],
    appropriateStyle: 'realistic',
    mood
  };
}

/**
 * Get default analysis when no content
 * @returns {Object} - Default analysis
 */
function getDefaultAnalysis() {
  return {
    keyConcepts: [],
    contentType: 'tutorial',
    technicalLevel: 'intermediate',
    visualMetaphors: [],
    appropriateStyle: 'realistic',
    mood: 'confident'
  };
}

/**
 * Identify if content is technical vs conceptual
 * @param {string} content - Content text
 * @returns {string} - 'technical' or 'conceptual'
 */
export function identifyContentType(content) {
  if (!content) return 'tutorial';
  
  const contentLower = content.toLowerCase();
  const technicalKeywords = ['code', 'function', 'api', 'database', 'server', 'algorithm', 'syntax', 'variable'];
  const conceptualKeywords = ['concept', 'theory', 'idea', 'principle', 'philosophy', 'approach', 'methodology'];
  
  const technicalCount = technicalKeywords.filter(kw => contentLower.includes(kw)).length;
  const conceptualCount = conceptualKeywords.filter(kw => contentLower.includes(kw)).length;
  
  if (technicalCount > conceptualCount) {
    return 'technical';
  } else if (conceptualCount > technicalCount) {
    return 'conceptual';
  }
  
  return 'tutorial';
}

/**
 * Extract key concepts from content
 * @param {string} content - Content text
 * @returns {Array<string>} - Key concepts
 */
export function extractKeyConcepts(content) {
  if (!content) return [];
  
  // Simple extraction - in production use NLP
  const words = content.toLowerCase()
    .replace(/[^\w\s]/g, ' ')
    .split(/\s+/)
    .filter(w => w.length > 4);
  
  const frequency = {};
  words.forEach(word => {
    frequency[word] = (frequency[word] || 0) + 1;
  });
  
  return Object.entries(frequency)
    .sort((a, b) => b[1] - a[1])
    .slice(0, 10)
    .map(([word]) => word);
}

/**
 * Determine appropriate visual style for content
 * @param {string} content - Content text
 * @returns {string} - Suggested style
 */
export function determineAppropriateStyle(content) {
  if (!content) return 'realistic';
  
  const contentLower = content.toLowerCase();
  
  // Technical content -> diagram or realistic
  if (contentLower.includes('code') || contentLower.includes('api') || contentLower.includes('database')) {
    return 'diagram';
  }
  
  // Conceptual content -> abstract or illustration
  if (contentLower.includes('concept') || contentLower.includes('theory')) {
    return 'abstract';
  }
  
  // Tutorial content -> realistic or illustration
  return 'realistic';
}

export default {
  analyzeContent,
  identifyContentType,
  extractKeyConcepts,
  determineAppropriateStyle
};



