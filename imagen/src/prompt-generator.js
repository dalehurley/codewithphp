/**
 * Creative prompt generator for unique, varied images
 * Ports the Laravel meta-prompt system to JavaScript
 */

import { GoogleGenAI } from '@google/genai';
import config from '../config/default.js';
import { buildStylePrompt, getStyle } from './styles.js';
import {
  CHARACTERS,
  SETTINGS,
  ACTIONS,
  PROPS,
  COMPOSITIONS,
  COLOR_PALETTES,
  randomElement,
  randomElements
} from './creativity/creative-elements.js';
import { mixStyleNames, generateRandomStyleMix, getIntensityModifier } from './creativity/style-mixer.js';
import { getBestComposition } from './creativity/composition-engine.js';
import { determineMoodFromContent, getMoodColorPalette, getMoodLighting, getMood, getMoodExpressions } from './creativity/mood-engine.js';
import { generateMetaphorsForContent, isCliché, suggestAlternative } from './creativity/metaphor-generator.js';
import { injectSurprise, injectMultipleSurprises, needsSurprise } from './creativity/surprise-injector.js';
import { markPromptSeen, isPromptUnique, buildLayeredPrompt, generateUniquePrompt, mutatePrompt } from './creativity/prompt-builder.js';
import { analyzeContent, extractKeyConcepts, determineAppropriateStyle } from './intelligence/content-analyzer.js';
import { getRandomComposition } from './creativity/composition-engine.js';
import { getRandomMood } from './creativity/mood-engine.js';

// Track recent prompts for pattern detection
const recentPrompts = [];
const recentOpeningPhrases = [];

/**
 * Extract opening phrase from prompt (first 50 chars)
 */
function extractOpeningPhrase(prompt) {
  const firstSentence = prompt.split(/[.!?]/)[0] || prompt.substring(0, 50);
  return firstSentence.trim().toLowerCase();
}

/**
 * Check if opening phrase is repetitive
 */
function isRepetitiveOpening(openingPhrase) {
  return recentOpeningPhrases.some(recent => {
    const similarity = calculateSimilarity(openingPhrase, recent);
    return similarity > 0.7; // 70% similarity threshold
  });
}

/**
 * Simple similarity calculation (word overlap)
 */
function calculateSimilarity(str1, str2) {
  const words1 = new Set(str1.split(/\s+/));
  const words2 = new Set(str2.split(/\s+/));
  const intersection = new Set([...words1].filter(x => words2.has(x)));
  const union = new Set([...words1, ...words2]);
  return intersection.size / union.size;
}

/**
 * Add prompt to recent history (keep last 10)
 */
function addToRecentPrompts(prompt) {
  recentPrompts.push(prompt);
  if (recentPrompts.length > 10) {
    recentPrompts.shift(); // Remove oldest
  }
  
  // Track opening phrases
  const opening = extractOpeningPhrase(prompt);
  recentOpeningPhrases.push(opening);
  if (recentOpeningPhrases.length > 10) {
    recentOpeningPhrases.shift();
  }
}

/**
 * Get recent prompts for pattern detection
 */
function getRecentPrompts() {
  return [...recentPrompts];
}

// Outfits array (kept separate for now, can be expanded later)
const OUTFITS = [
  'lab coat and safety goggles',
  'mechanic coveralls and tool belt',
  'pilot jacket and aviator glasses',
  'sharp business suit and briefcase',
  'nurse uniform and stethoscope',
  'cowboy boots and ranch wear',
  'astronaut suit and helmet',
  'teacher cardigan and glasses',
  'construction hard hat and vest',
  'vintage swimwear and sunglasses',
  'scientist lab attire and clipboard',
  'engineer hard hat and blueprints',
  'chef whites and neckerchief',
  'photographer vest with camera straps',
  'musician formal attire and bow tie'
];

/**
 * Generate creative prompt using meta-prompt system
 * 
 * @param {Object} options - Prompt generation options
 * @param {string} options.title - Blog post title
 * @param {string} options.content - Blog post content (optional)
 * @param {boolean} options.includeBanner - Include text banner (50% default)
 * @param {string} options.consistentCharacter - Use specific character (for series consistency)
 * @param {string} options.consistentColors - Use specific color palette (for series consistency)
 * @param {string} options.consistentStyle - Use specific style description (for series consistency)
 * @param {string} options.apiKey - Gemini API key (for content analysis)
 * @returns {Promise<string>} - Creative image generation prompt
 */
export async function generateCreativePrompt(options = {}) {
  const {
    title = 'Untitled',
    content = '',
    includeBanner = Math.random() > 0.5,
    consistentCharacter = null,
    consistentColors = null,
    consistentStyle = null,
    apiKey = null,
    enableStyleMixing = true,
    enableCompositionIntelligence = true,
    enableMoodSystem = true,
    enableMetaphors = true,
    enableSurprises = true,
    enableContentAnalysis = true
  } = options;

  // STEP 1: Analyze content using intelligence system
  let contentAnalysis = null;
  if (enableContentAnalysis && content && apiKey) {
    try {
      contentAnalysis = await analyzeContent(content, apiKey);
      // Log to stderr to avoid breaking MCP JSON-RPC protocol (stdout is for JSON only)
      console.error('🧠 Content analysis:', {
        contentType: contentAnalysis.contentType,
        technicalLevel: contentAnalysis.technicalLevel,
        mood: contentAnalysis.mood,
        keyConcepts: contentAnalysis.keyConcepts?.slice(0, 3)
      });
    } catch (error) {
      console.warn('⚠️  Content analysis failed, using fallback:', error.message);
      // Fallback to simple analysis
      contentAnalysis = {
        keyConcepts: extractKeyConcepts(content),
        contentType: 'tutorial',
        technicalLevel: 'intermediate',
        mood: 'confident',
        appropriateStyle: determineAppropriateStyle(content)
      };
    }
  } else if (content) {
    // Use simple analysis without API
    contentAnalysis = {
      keyConcepts: extractKeyConcepts(content),
      contentType: 'tutorial',
      technicalLevel: 'intermediate',
      mood: 'confident',
      appropriateStyle: determineAppropriateStyle(content)
    };
  }

  // STEP 2: Select elements based on content analysis (intelligent selection with variety)
  let character, outfit, setting, action, props;
  
  if (consistentCharacter) {
    character = consistentCharacter;
  } else if (contentAnalysis?.keyConcepts?.length > 0) {
    // Select character that matches content concepts - use randomElements for variety
    const concepts = contentAnalysis.keyConcepts.join(' ').toLowerCase();
    const matchingCharacters = CHARACTERS.filter(c => 
      concepts.split(' ').some(concept => c.toLowerCase().includes(concept))
    );
    // Get multiple matches and randomly select from top 3-5 for variety
    if (matchingCharacters.length > 0) {
      const topMatches = matchingCharacters.length > 5 
        ? randomElements(matchingCharacters, 5)
        : matchingCharacters;
      character = randomElement(topMatches);
    } else {
      // No matches, use randomElements to get diverse selection from all
      character = randomElement(CHARACTERS);
    }
  } else {
    // Use randomElements to ensure variety (get 3 random, pick one)
    const candidates = randomElements(CHARACTERS, Math.min(3, CHARACTERS.length));
    character = randomElement(candidates);
  }

  // Select outfit based on content type - use randomElements for variety
  if (contentAnalysis?.contentType === 'technical') {
    const technicalOutfits = OUTFITS.filter(o => 
      o.includes('lab') || o.includes('engineer') || o.includes('mechanic')
    );
    if (technicalOutfits.length > 0) {
      const candidates = technicalOutfits.length > 3 
        ? randomElements(technicalOutfits, 3)
        : technicalOutfits;
      outfit = randomElement(candidates);
    } else {
      // Use randomElements for variety
      const candidates = randomElements(OUTFITS, Math.min(3, OUTFITS.length));
      outfit = randomElement(candidates);
    }
  } else {
    // Use randomElements to get variety
    const candidates = randomElements(OUTFITS, Math.min(3, OUTFITS.length));
    outfit = randomElement(candidates);
  }

  // Select setting based on content - use randomElements for variety
  if (contentAnalysis?.keyConcepts?.length > 0) {
    const concepts = contentAnalysis.keyConcepts.join(' ').toLowerCase();
    const matchingSettings = SETTINGS.filter(s => 
      concepts.split(' ').some(concept => s.toLowerCase().includes(concept))
    );
    if (matchingSettings.length > 0) {
      // Get top 3-5 matches for variety
      const topMatches = matchingSettings.length > 5 
        ? randomElements(matchingSettings, 5)
        : matchingSettings;
      setting = randomElement(topMatches);
    } else {
      // Use randomElements for variety
      const candidates = randomElements(SETTINGS, Math.min(3, SETTINGS.length));
      setting = randomElement(candidates);
    }
  } else {
    // Use randomElements to ensure variety
    const candidates = randomElements(SETTINGS, Math.min(3, SETTINGS.length));
    setting = randomElement(candidates);
  }

  // Select action based on content type - use randomElements for variety
  if (contentAnalysis?.contentType === 'technical') {
    const technicalActions = ACTIONS.filter(a => 
      a.includes('coding') || a.includes('building') || a.includes('debugging')
    );
    if (technicalActions.length > 0) {
      const candidates = technicalActions.length > 3 
        ? randomElements(technicalActions, 3)
        : technicalActions;
      action = randomElement(candidates);
    } else {
      // Use randomElements for variety even when no technical matches
      const candidates = randomElements(ACTIONS, Math.min(3, ACTIONS.length));
      action = randomElement(candidates);
    }
  } else {
    // Use randomElements for variety
    const candidates = randomElements(ACTIONS, Math.min(3, ACTIONS.length));
    action = randomElement(candidates);
  }

  // Select props based on key concepts - use multiple props for richer prompts
  if (contentAnalysis?.keyConcepts?.length > 0) {
    const concepts = contentAnalysis.keyConcepts.join(' ').toLowerCase();
    const matchingProps = PROPS.filter(p => 
      concepts.split(' ').some(concept => p.toLowerCase().includes(concept))
    );
    if (matchingProps.length > 0) {
      // Get 1-2 props for richer prompts (30% chance of 2 props)
      const propCount = Math.random() < 0.3 ? 2 : 1;
      const selectedProps = matchingProps.length >= propCount
        ? randomElements(matchingProps, propCount)
        : matchingProps;
      props = selectedProps.length > 1 ? selectedProps.join(' and ') : selectedProps[0];
    } else {
      // Use randomElements for variety, sometimes multiple props
      const propCount = Math.random() < 0.2 ? 2 : 1;
      const candidates = randomElements(PROPS, Math.min(propCount + 2, PROPS.length));
      const selectedProps = randomElements(candidates, propCount);
      props = selectedProps.length > 1 ? selectedProps.join(' and ') : selectedProps[0];
    }
  } else {
    // Use randomElements for variety, sometimes multiple props
    const propCount = Math.random() < 0.2 ? 2 : 1;
    const candidates = randomElements(PROPS, Math.min(propCount + 2, PROPS.length));
    const selectedProps = randomElements(candidates, propCount);
    props = selectedProps.length > 1 ? selectedProps.join(' and ') : selectedProps[0];
  }
  
  // Enhanced composition selection using composition intelligence
  let composition;
  if (enableCompositionIntelligence && content) {
    const compObj = getBestComposition(content);
    if (compObj) {
      composition = compObj.description;
    } else {
      // Use randomElements to get variety from compositions
      const candidates = randomElements(COMPOSITIONS, Math.min(3, COMPOSITIONS.length));
      composition = randomElement(candidates);
    }
  } else {
    // Use random composition with variety tracking
    const compObj = getRandomComposition(true); // Avoid recent
    if (compObj) {
      composition = compObj.description;
    } else {
      // Use randomElements for variety
      const candidates = randomElements(COMPOSITIONS, Math.min(3, COMPOSITIONS.length));
      composition = randomElement(candidates);
    }
  }
  
  // Enhanced mood system - determine mood from content
  let mood, moodColors, moodLighting;
  if (enableMoodSystem && content) {
    // Use content analysis mood if available, otherwise detect from content
    if (contentAnalysis?.mood) {
      const moodObj = getMood(contentAnalysis.mood);
      mood = moodObj || determineMoodFromContent(content);
    } else {
      mood = determineMoodFromContent(content);
    }
    moodColors = getMoodColorPalette(mood);
    moodLighting = getMoodLighting(mood);
  } else {
    // Use random mood with variety tracking
    mood = getRandomMood(true); // Avoid recent
    moodColors = getMoodColorPalette(mood);
    moodLighting = getMoodLighting(mood);
  }
  
  // Use mood-based colors if available, otherwise use consistent or randomElements for variety
  let colors;
  if (consistentColors) {
    colors = consistentColors;
  } else if (moodColors) {
    colors = moodColors;
  } else {
    // Use randomElements to get variety from color palettes
    const candidates = randomElements(COLOR_PALETTES, Math.min(3, COLOR_PALETTES.length));
    colors = randomElement(candidates);
  }
  
  // Enhanced style processing with style mixing support
  let styleDescription;
  if (consistentStyle) {
    const styleObj = getStyle(consistentStyle);
    if (styleObj) {
      // Check if style mixing is enabled and we have multiple styles
      if (enableStyleMixing && typeof consistentStyle === 'string' && consistentStyle.includes('+')) {
        // Multiple styles provided, use style mixer
        const styles = consistentStyle.split('+').map(s => s.trim());
        styleDescription = mixStyleNames(styles);
      } else {
        // Single style, use comprehensive description
        styleDescription = buildStylePrompt(consistentStyle, { includeKeywords: true, includeDescription: true });
      }
    } else {
      styleDescription = consistentStyle;
    }
  } else {
    // Use content analysis style if available
    if (contentAnalysis?.appropriateStyle) {
      const styleObj = getStyle(contentAnalysis.appropriateStyle);
      if (styleObj) {
        styleDescription = buildStylePrompt(contentAnalysis.appropriateStyle, { 
          includeKeywords: true, 
          includeDescription: true 
        });
      } else {
        styleDescription = contentAnalysis.appropriateStyle;
      }
    } else if (enableStyleMixing && Math.random() < 0.4) {
      // Random style mixing for variety (40% chance, increased from 30%)
      const randomMix = generateRandomStyleMix(2);
      styleDescription = mixStyleNames(randomMix.map(s => s.style));
    } else {
      // Default to vintage propaganda/pin-up style
      styleDescription = '1950s propaganda/pin-up poster/comic book advertisement';
    }
  }

  // Enhanced wildcards with visual metaphors (more intelligent selection + cliché avoidance)
  const wildcards = [];
  if (enableMetaphors && content) {
    // Generate metaphors based on content analysis
    const metaphors = generateMetaphorsForContent(content, 3);
    metaphors.forEach(metaphor => {
      // Check for clichés and suggest alternatives
      let finalMetaphor = metaphor;
      if (isCliché(metaphor)) {
        finalMetaphor = suggestAlternative(metaphor);
        // Log to stderr to avoid breaking MCP JSON-RPC protocol
        console.error(`🔄 Replaced cliché "${metaphor}" with "${finalMetaphor}"`);
      }
      
      // Higher probability for metaphors matching content (60% vs 40%)
      const probability = contentAnalysis?.keyConcepts?.some(c => 
        finalMetaphor.toLowerCase().includes(c.toLowerCase())
      ) ? 0.6 : 0.4;
      
      if (Math.random() < probability) {
        wildcards.push(finalMetaphor);
      }
    });
  }
  
  // Original wildcards (reduced probability to 20% to avoid repetition)
  if (Math.random() < 0.2) wildcards.push('AI-powered robotic companion');
  if (Math.random() < 0.2) wildcards.push('dramatic weather effects');
  if (Math.random() < 0.2) wildcards.push('retro-futuristic technology blend');
  if (Math.random() < 0.2) wildcards.push('environmental storytelling details');

  const wildcardText = wildcards.length > 0 ? `Include: ${wildcards.join(', ')}. ` : '';
  const bannerText = includeBanner ? 'Include an attention-grabbing banner with ≤ 8 words that captures the essence. ' : '';
  
  // Build mood-enhanced description with expressions
  const moodText = mood ? `Mood: ${mood.name} (${mood.description}). ` : '';
  const lightingText = moodLighting ? `Lighting: ${moodLighting}. ` : '';
  const expressionText = mood ? `Character expression: ${getMoodExpressions(mood)}. ` : '';

  // Build the meta-prompt with enhanced creativity systems
  // Use content analysis insights if available
  const contentInsights = contentAnalysis 
    ? `\n**Content Analysis Insights:**\n- Content Type: ${contentAnalysis.contentType}\n- Technical Level: ${contentAnalysis.technicalLevel}\n- Key Concepts: ${contentAnalysis.keyConcepts?.slice(0, 5).join(', ') || 'N/A'}\n- Suggested Visual Style: ${contentAnalysis.appropriateStyle || 'N/A'}\n`
    : '';

  // Vary the meta-prompt template to prevent repetitive outputs
  const promptVariations = [
    {
      role: 'elite visual prompt engineer specializing in dynamic, eye-catching imagery',
      style: 'vintage-inspired modern illustration',
      instruction: 'Craft a compelling, production-ready image prompt'
    },
    {
      role: 'creative director with expertise in visual storytelling',
      style: 'retro-futuristic design aesthetic',
      instruction: 'Design a striking visual prompt'
    },
    {
      role: 'art director known for bold, memorable visuals',
      style: 'contemporary retro art style',
      instruction: 'Create an engaging image generation prompt'
    },
    {
      role: 'visual artist specializing in hero imagery',
      style: 'stylized modern illustration',
      instruction: 'Generate a powerful image prompt'
    }
  ];
  
  const variation = randomElement(promptVariations);
  const brandStyle = consistentStyle || styleDescription || 'vintage-inspired modern design';

  let metaPrompt = `You are an ${variation.role}. ${variation.instruction} that an AI image generator can use to produce a striking hero/featured image for the following blog post title: "${title}".

${content ? `Use the following blog post content for inspiration:\n<blog-post-content>\n${content}\n</blog-post-content>\n` : ''}${contentInsights}

The finished artwork should embody a ${variation.style} aesthetic while representing ${config.prompt.creative.brand.name}'s brand promise—delivering ${config.prompt.creative.brand.identity}. The visual style should feel fresh and unique, not repetitive or formulaic.

**Creative Framework for Maximum Variation:**

${bannerText}${randomElement([
  `Design a ${composition} featuring an extremely good-looking ${character} wearing ${outfit} in a ${setting}, ${action}.`,
  `Illustrate a ${composition} with a ${character} wearing ${outfit} in a ${setting}, ${action}.`,
  `Render a ${composition} showcasing a ${character} in ${outfit} within a ${setting}, ${action}.`,
  `Visualize a ${composition} depicting a ${character} wearing ${outfit} in a ${setting}, ${action}.`,
  `Craft a ${composition} presenting a ${character} in ${outfit} at a ${setting}, ${action}.`,
  `Build a ${composition} featuring a ${character} wearing ${outfit} in a ${setting}, ${action}.`
])} Use color palette of ${colors}. Include ${props} as key visual elements. ${wildcardText}

**Art Direction:**
• Style: ${getIntensityModifier(Math.random() < 0.3 ? 'strong' : Math.random() < 0.5 ? 'moderate' : 'subtle')} ${styleDescription}${Math.random() < 0.5 ? ' with bold outlines' : Math.random() < 0.3 ? ' with defined edges' : ''}${Math.random() < 0.4 ? ', dynamic visual style' : Math.random() < 0.3 ? ', contemporary aesthetic' : ''}${Math.random() < 0.3 ? ', textured finish' : ''}.
• ${moodText}${lightingText}${expressionText}Mood: Confident empowerment, playful innovation, retro glamour meets high-tech revolution, explosive energy, empowering transformation, playful innovation, confident optimism.
• Dimensions: EXACTLY 1536 pixels wide by 1024 pixels tall (3:2 aspect ratio). The image must fill the ENTIRE canvas from edge to edge with no empty space, no borders, no margins. All visual elements should extend to the edges of the frame.
• Composition: Fill the entire frame - foreground elements should reach the bottom edge, background should extend to all edges, no white space or empty areas.
• Avoid: Generic corporate aesthetics, predictable compositions, empty space, borders, margins

**Creative Mandate:** This must feel completely unique and reflect the blog post's content and ${config.prompt.creative.brand.name}'s brand identity as a leader in AI innovation. The image should be visually striking, memorable, and perfectly suited for a blog post header.

### Prompt Output Rules
${randomElement([
  `• Begin with a creative, context-specific opening that reflects the ${title} content. Use varied verbs like Design, Illustrate, Render, Visualize, Depict, Showcase, Present, Craft, or Build.`,
  `• Start with a unique opening phrase that captures the essence of "${title}". Avoid generic templates - be creative and specific.`,
  `• Create an original opening that directly relates to the blog post theme. Use dynamic, varied language that feels fresh.`,
  `• Open with a distinctive instruction that reflects the content's unique character. Vary your approach - don't follow templates.`
])}
• DO NOT use repetitive phrases like "Create a vibrant 1950s propaganda pin-up poster" - this is a template to AVOID, not copy.
• Include all stylistic directives above in concise, generator-friendly syntax
• CRITICAL: Specify exact dimensions: "1536x1024 pixels" and emphasize "fill the entire frame edge-to-edge with no empty space"
• Return ONLY the prompt—no commentary, pre-amble, or closing remarks
• Ensure composition fills the entire 1536x1024 canvas - elements should extend to all edges
• Make sure the image captures the blog post essence with maximum visual impact and fun factor
• CRITICAL: Each prompt must be COMPLETELY UNIQUE - never repeat opening phrases or structures from previous prompts

${randomElement([
  'Generate a unique, creative image prompt now.',
  'Create your unique prompt with maximum creativity.',
  'Craft an original, distinctive image prompt.',
  'Design a one-of-a-kind visual prompt.'
])}`;

  // Ensure prompt uniqueness with mutation if needed
  let attempts = 0;
  const maxUniquenessAttempts = 5;
  while (!isPromptUnique(metaPrompt) && attempts < maxUniquenessAttempts) {
    // Mutate prompt to create variation
    const mutations = {
      addStyle: attempts === 0 ? 'subtle variation' : 'different stylistic approach',
      intensity: attempts === 1 ? 'slightly different' : 'distinct',
      addComposition: attempts === 2 ? 'alternative composition' : null,
      addMood: attempts === 3 ? 'varied mood' : null
    };
    
    // Remove null mutations
    Object.keys(mutations).forEach(key => {
      if (mutations[key] === null) delete mutations[key];
    });
    
    metaPrompt = mutatePrompt(metaPrompt, mutations);
    attempts++;
    
    if (attempts < maxUniquenessAttempts) {
      // Log to stderr to avoid breaking MCP JSON-RPC protocol
      console.error(`🔄 Prompt not unique, mutating (attempt ${attempts}/${maxUniquenessAttempts})...`);
    }
  }

  // Inject surprise elements if enabled (with intelligent pattern detection)
  if (enableSurprises) {
    // Use needsSurprise for pattern detection across recent prompts
    const recent = getRecentPrompts();
    const shouldInjectSurprise = needsSurprise(metaPrompt, recent);
    
    if (shouldInjectSurprise) {
      // Higher probability if pattern detected or not unique - use multiple surprises
      const isUnique = isPromptUnique(metaPrompt);
      if (!isUnique || recent.length >= 3) {
        // Use multiple surprises for more variety when patterns detected
        metaPrompt = injectMultipleSurprises(metaPrompt, 2);
      } else {
        metaPrompt = injectSurprise(metaPrompt, { probability: 0.5 });
      }
    } else {
      // Still inject with lower probability for variety
      if (Math.random() < 0.2) {
        metaPrompt = injectSurprise(metaPrompt, { probability: 0.3 });
      }
    }
  }
  
  // Mark prompt as seen for uniqueness tracking
  markPromptSeen(metaPrompt);
  
  // Add to recent prompts for pattern detection
  addToRecentPrompts(metaPrompt);

  return metaPrompt;
}

/**
 * Generate a simple, direct prompt without meta-prompt layer
 * Can use buildLayeredPrompt for structured prompts if options provided
 * 
 * @param {string} description - Direct image description
 * @param {Object} options - Additional options
 * @param {string} options.style - Style descriptor (optional) - can be style name or custom description
 * @param {string} options.subject - Subject/character (for layered prompt)
 * @param {string} options.action - Action (for layered prompt)
 * @param {string} options.setting - Setting (for layered prompt)
 * @param {string} options.composition - Composition (for layered prompt)
 * @param {string} options.colorPalette - Color palette (for layered prompt)
 * @param {Array<string>} options.props - Props (for layered prompt)
 * @param {string} options.content - Content context (for layered prompt)
 * @param {boolean} options.useLayered - Use buildLayeredPrompt if options provided (default: true)
 * @returns {string} - Image generation prompt
 */
export function generateSimplePrompt(description, options = {}) {
  const { 
    style = null,
    subject = null,
    action = null,
    setting = null,
    composition = null,
    colorPalette = null,
    props = [],
    content = '',
    useLayered = true
  } = options;
  
  // Add dimension requirements to ensure full coverage
  const dimensionNote = " The image must be exactly 1536x1024 pixels and fill the entire frame edge-to-edge with no empty space, borders, or margins.";
  
  // Use buildLayeredPrompt if we have enough options for a structured prompt
  const hasLayeredOptions = subject || action || setting || composition || colorPalette || props.length > 0;
  
  if (useLayered && hasLayeredOptions) {
    // Build structured prompt using buildLayeredPrompt or generateUniquePrompt
    const layeredOptions = {
      subject: subject || description.split(' ').slice(0, 3).join(' '), // Use first few words as subject
      action: action || description,
      setting,
      style: style || null,
      composition,
      colorPalette,
      props,
      content,
      mood: options.mood || null
    };
    
    // Use generateUniquePrompt to ensure uniqueness
    const layeredPrompt = generateUniquePrompt(layeredOptions, 5);
    return `${layeredPrompt}${dimensionNote}`;
  }
  
  // Fallback to simple prompt
  if (style) {
    // Try to resolve style using styles system
    const styleObj = getStyle(style);
    if (styleObj) {
      // Use comprehensive style description
      const stylePrompt = buildStylePrompt(style, { includeKeywords: true, includeDescription: true });
      return `${stylePrompt}. ${description}${dimensionNote}`;
    } else {
      // Custom style description, use as-is
      return `${style}: ${description}${dimensionNote}`;
    }
  }
  
  return `${description}${dimensionNote}`;
}

/**
 * Generate final image prompt from meta-prompt using Gemini Pro
 * 
 * @param {string} metaPrompt - The meta-prompt to process
 * @param {string} apiKey - Gemini API key
 * @returns {Promise<string>} - The generated image prompt
 */
async function generateFinalPromptFromMeta(metaPrompt, apiKey) {
  try {
    const client = new GoogleGenAI({ apiKey });
    
    let finalPrompt = '';
    let attempts = 0;
    const maxAttempts = 3;
    
    // Try up to 3 times to get a non-repetitive prompt
    while (attempts < maxAttempts) {
      const response = await client.models.generateContent({
        model: 'gemini-2.5-flash',
        contents: [
          {
            role: 'user',
            parts: [{ text: metaPrompt }]
          }
        ]
      });

      // Extract the generated text
      if (response.candidates && response.candidates.length > 0) {
        const candidate = response.candidates[0];
        if (candidate.content && candidate.content.parts) {
          const textParts = candidate.content.parts
            .filter(part => part.text)
            .map(part => part.text)
            .join('');
          
          finalPrompt = textParts.trim();
          
          // Check for repetitive opening phrases
          const opening = extractOpeningPhrase(finalPrompt);
          const isRepetitive = isRepetitiveOpening(opening);
          
          // Check for the specific problematic phrase
          const hasProblematicPhrase = finalPrompt.toLowerCase().startsWith('create a vibrant 1950s');
          
          if (!isRepetitive && !hasProblematicPhrase) {
            // Good prompt, return it
            return finalPrompt;
          }
          
          // Repetitive or problematic, try again
          if (attempts < maxAttempts - 1) {
            console.error(`🔄 Detected repetitive opening "${opening.substring(0, 50)}...", regenerating (attempt ${attempts + 1}/${maxAttempts})...`);
            // Add instruction to avoid the repetitive phrase
            metaPrompt += `\n\nCRITICAL: The previous attempt started with "${opening.substring(0, 50)}..." which is too similar to previous prompts. Generate a COMPLETELY DIFFERENT opening phrase. Use a different verb and structure.`;
          }
        }
      }
      
      attempts++;
    }
    
    // If we still have a repetitive prompt, try to mutate it
    if (finalPrompt) {
      const opening = extractOpeningPhrase(finalPrompt);
      if (isRepetitiveOpening(opening) || finalPrompt.toLowerCase().startsWith('create a vibrant 1950s')) {
        console.error('⚠️  Attempting to fix repetitive prompt with mutation...');
        // Try to mutate the opening
        const verbs = ['Design', 'Illustrate', 'Render', 'Visualize', 'Depict', 'Showcase', 'Present', 'Craft', 'Build'];
        const randomVerb = randomElement(verbs);
        // Replace common repetitive patterns
        finalPrompt = finalPrompt.replace(/^create a vibrant 1950s/i, `${randomVerb.toLowerCase()} a`);
        finalPrompt = finalPrompt.replace(/^create a/i, `${randomVerb.toLowerCase()} a`);
        // Add variation instruction
        finalPrompt = `${randomVerb} ${finalPrompt.substring(finalPrompt.indexOf(' ') + 1)}`;
      }
    }
    
    if (!finalPrompt) {
      throw new Error('No text generated from meta-prompt');
    }
    
    return finalPrompt;
  } catch (error) {
    console.error('Error generating prompt from meta-prompt:', error.message);
    throw new Error(`Failed to generate prompt: ${error.message}`);
  }
}

/**
 * Main prompt generator function
 * Decides whether to use creative or simple prompt
 * 
 * @param {Object} options - Prompt options
 * @param {string} options.prompt - Base prompt/description
 * @param {boolean} options.creative - Use creative meta-prompt system
 * @param {string} options.title - Blog post title (for creative mode)
 * @param {string} options.content - Blog post content (for creative mode)
 * @param {string} options.style - Style descriptor (for simple mode or consistent style)
 * @param {string} options.apiKey - Gemini API key (for creative mode)
 * @param {string} options.consistentCharacter - Consistent character (for series)
 * @param {string} options.consistentColors - Consistent color palette (for series)
 * @param {string} options.consistentStyle - Consistent style description (for series)
 * @returns {Promise<string>} - Final image generation prompt
 */
export async function generatePrompt(options = {}) {
  const {
    prompt,
    creative = false,
    title = null,
    content = null,
    style = null,
    apiKey = null,
    consistentCharacter = null,
    consistentColors = null,
    consistentStyle = null
  } = options;

  if (creative) {
    // Process style: resolve style name if provided
    let resolvedStyle = consistentStyle || style;
    if (resolvedStyle) {
      const styleObj = getStyle(resolvedStyle);
      if (styleObj) {
        // Use the style name/key for consistency
        resolvedStyle = styleObj.name;
      }
    }
    
    // Generate meta-prompt (now async due to content analysis)
    const metaPrompt = await generateCreativePrompt({
      title: title || prompt,
      content: content || '',
      includeBanner: options.includeBanner,
      consistentCharacter,
      consistentColors,
      consistentStyle: resolvedStyle,
      apiKey: apiKey || process.env.GEMINI_API_KEY
    });
    
    // Log to stderr to avoid breaking MCP JSON-RPC protocol (stdout is for JSON only)
    console.error('🎨 Generated meta-prompt, now asking Gemini to create final image prompt...\n');
    
    // Use Gemini to generate the actual image prompt from the meta-prompt
    const finalPrompt = await generateFinalPromptFromMeta(
      metaPrompt, 
      apiKey || process.env.GEMINI_API_KEY
    );
    
    // Log to stderr to avoid breaking MCP JSON-RPC protocol
    console.error('✨ Final image prompt generated:\n');
    console.error(finalPrompt.substring(0, 300) + '...\n');
    
    // Track the final prompt's opening phrase
    const opening = extractOpeningPhrase(finalPrompt);
    if (isRepetitiveOpening(opening)) {
      console.error(`⚠️  Warning: Opening phrase may be repetitive: "${opening.substring(0, 50)}..."`);
    }
    
    // Add to tracking (this happens in generateCreativePrompt too, but ensure it's tracked)
    addToRecentPrompts(finalPrompt);
    
    return finalPrompt;
  } else {
    // Use simple direct prompt with style resolution
    return generateSimplePrompt(prompt, { style: style || consistentStyle });
  }
}

export default {
  generatePrompt,
  generateCreativePrompt,
  generateSimplePrompt
};

