/**
 * Image Analysis System
 * Visual quality assessment, content relevance scoring, and technical quality metrics
 */

import { GoogleGenAI } from '@google/genai';

/**
 * Analyze image quality and relevance
 * @param {Buffer} imageBuffer - Image buffer to analyze
 * @param {string} expectedContent - Expected content description
 * @param {string} expectedStyle - Expected style
 * @param {string} apiKey - Gemini API key
 * @returns {Promise<Object>} - Analysis results
 */
export async function analyzeImage(imageBuffer, expectedContent = '', expectedStyle = '', apiKey) {
  try {
    const client = new GoogleGenAI({ apiKey });
    
    // Convert buffer to base64
    const base64Image = imageBuffer.toString('base64');
    
    // Build analysis prompt
    const analysisPrompt = `Analyze this image and provide a detailed assessment:

${expectedContent ? `Expected Content: ${expectedContent}` : ''}
${expectedStyle ? `Expected Style: ${expectedStyle}` : ''}

Please evaluate:
1. Visual Quality (composition, color harmony, clarity) - Score 0-10
2. Content Relevance (does it match expected content?) - Score 0-10
3. Style Adherence (does it match expected style?) - Score 0-10
4. Technical Quality (sharpness, contrast, balance) - Score 0-10
5. Overall Appeal (visual impact, memorability) - Score 0-10

Provide scores and brief explanations for each category. Format as JSON:
{
  "visualQuality": {"score": 8, "explanation": "..."},
  "contentRelevance": {"score": 7, "explanation": "..."},
  "styleAdherence": {"score": 9, "explanation": "..."},
  "technicalQuality": {"score": 8, "explanation": "..."},
  "overallAppeal": {"score": 8, "explanation": "..."},
  "totalScore": 40
}`;

    const response = await client.models.generateContent({
      model: 'gemini-2.5-flash',
      contents: [
        {
          role: 'user',
          parts: [
            {
              inlineData: {
                mimeType: 'image/png',
                data: base64Image
              }
            },
            {
              text: analysisPrompt
            }
          ]
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
        
        // Try to parse JSON from response
        try {
          const jsonMatch = textParts.match(/\{[\s\S]*\}/);
          if (jsonMatch) {
            return JSON.parse(jsonMatch[0]);
          }
        } catch (e) {
          // Fallback to manual parsing
        }
        
        // Fallback: extract scores manually
        return extractScoresFromText(textParts);
      }
    }
    
    throw new Error('No analysis generated');
  } catch (error) {
    console.error('Error analyzing image:', error.message);
    // Return default scores on error
    return {
      visualQuality: { score: 5, explanation: 'Analysis failed' },
      contentRelevance: { score: 5, explanation: 'Analysis failed' },
      styleAdherence: { score: 5, explanation: 'Analysis failed' },
      technicalQuality: { score: 5, explanation: 'Analysis failed' },
      overallAppeal: { score: 5, explanation: 'Analysis failed' },
      totalScore: 25,
      error: error.message
    };
  }
}

/**
 * Extract scores from text response (fallback)
 * @param {string} text - Response text
 * @returns {Object} - Parsed scores
 */
function extractScoresFromText(text) {
  const scores = {
    visualQuality: { score: 5, explanation: 'Could not parse' },
    contentRelevance: { score: 5, explanation: 'Could not parse' },
    styleAdherence: { score: 5, explanation: 'Could not parse' },
    technicalQuality: { score: 5, explanation: 'Could not parse' },
    overallAppeal: { score: 5, explanation: 'Could not parse' },
    totalScore: 25
  };
  
  // Try to extract numbers that look like scores
  const scoreMatches = text.match(/(\d+(?:\.\d+)?)/g);
  if (scoreMatches && scoreMatches.length >= 5) {
    scores.visualQuality.score = parseFloat(scoreMatches[0]) || 5;
    scores.contentRelevance.score = parseFloat(scoreMatches[1]) || 5;
    scores.styleAdherence.score = parseFloat(scoreMatches[2]) || 5;
    scores.technicalQuality.score = parseFloat(scoreMatches[3]) || 5;
    scores.overallAppeal.score = parseFloat(scoreMatches[4]) || 5;
    scores.totalScore = scores.visualQuality.score + scores.contentRelevance.score + 
                       scores.styleAdherence.score + scores.technicalQuality.score + 
                       scores.overallAppeal.score;
  }
  
  return scores;
}

/**
 * Calculate variety score (how different from previous images)
 * @param {Buffer} imageBuffer - Current image
 * @param {Array<Buffer>} previousImages - Previous images to compare
 * @returns {number} - Variety score (0-10, higher = more different)
 */
export function calculateVarietyScore(imageBuffer, previousImages = []) {
  if (previousImages.length === 0) {
    return 10; // First image gets max variety score
  }
  
  // Simple hash-based comparison
  // In production, use perceptual hashing library
  const currentHash = simpleHash(imageBuffer);
  const previousHashes = previousImages.map(img => simpleHash(img));
  
  // Count unique hashes
  const uniqueHashes = new Set([currentHash, ...previousHashes]);
  const similarity = previousHashes.filter(h => h === currentHash).length;
  
  // Variety score: higher if more unique
  const varietyScore = 10 - (similarity * 2);
  return Math.max(0, Math.min(10, varietyScore));
}

/**
 * Simple hash function for image comparison
 * @param {Buffer} buffer - Image buffer
 * @returns {string} - Hash string
 */
function simpleHash(buffer) {
  // Simple hash based on buffer size and first/last bytes
  const size = buffer.length;
  const first = buffer[0] || 0;
  const last = buffer[buffer.length - 1] || 0;
  const middle = buffer[Math.floor(buffer.length / 2)] || 0;
  return `${size}-${first}-${middle}-${last}`;
}

/**
 * Check if image meets quality threshold
 * @param {Object} analysis - Analysis results
 * @param {number} threshold - Minimum total score (default: 30)
 * @returns {boolean} - True if meets threshold
 */
export function meetsQualityThreshold(analysis, threshold = 30) {
  return analysis.totalScore >= threshold;
}

export default {
  analyzeImage,
  calculateVarietyScore,
  meetsQualityThreshold
};









