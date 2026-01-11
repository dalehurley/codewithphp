/**
 * Visual Metaphor Generator
 * Generate creative visual metaphors for abstract concepts
 * Maps technical concepts to memorable visual representations
 */

/**
 * Concept to metaphor mappings
 */
const CONCEPT_METAPHORS = {
  // Technical Concepts
  'api': ['bridge connecting systems', 'handshake between services', 'translator between languages'],
  'database': ['library with organized shelves', 'warehouse storing information', 'filing system'],
  'server': ['power plant generating energy', 'factory processing requests', 'hub connecting everything'],
  'code': ['blueprint for building', 'recipe for creation', 'language of machines'],
  'algorithm': ['step-by-step guide', 'recipe for solving', 'path to solution'],
  'function': ['tool in toolbox', 'worker performing task', 'machine component'],
  'variable': ['container holding value', 'label on storage', 'placeholder for data'],
  'loop': ['circular path', 'repeating pattern', 'cycle of actions'],
  'array': ['organized list', 'shelf with items', 'collection in order'],
  'object': ['package containing data', 'box with properties', 'entity with attributes'],
  
  // Abstract Concepts
  'learning': ['journey of discovery', 'building knowledge tower', 'unlocking doors'],
  'problem-solving': ['puzzle being solved', 'maze finding exit', 'key fitting lock'],
  'innovation': ['lightbulb moment', 'spark of creativity', 'breaking boundaries'],
  'collaboration': ['team working together', 'orchestra playing harmony', 'puzzle pieces fitting'],
  'efficiency': ['well-oiled machine', 'smooth workflow', 'optimized process'],
  'scalability': ['growing tree', 'expanding network', 'building foundation'],
  'security': ['fortress protecting', 'shield defending', 'lock securing'],
  'performance': ['race car speed', 'efficient engine', 'optimized system'],
  'testing': ['quality inspector', 'safety check', 'validation process'],
  'deployment': ['launching rocket', 'opening doors', 'releasing product'],
  
  // Process Concepts
  'development': ['building construction', 'crafting creation', 'growing project'],
  'debugging': ['detective investigation', 'doctor diagnosis', 'mechanic repair'],
  'refactoring': ['renovating building', 'reorganizing structure', 'improving design'],
  'optimization': ['fine-tuning engine', 'sharpening tools', 'polishing gem'],
  'integration': ['connecting pieces', 'merging systems', 'unifying components'],
  'migration': ['moving to new home', 'transitioning systems', 'upgrading platform'],
  
  // Data Concepts
  'data': ['raw materials', 'information stream', 'knowledge base'],
  'processing': ['transforming materials', 'refining information', 'converting data'],
  'storage': ['warehouse keeping', 'archive preserving', 'library organizing'],
  'retrieval': ['finding treasure', 'accessing vault', 'opening archive'],
  'analysis': ['examining details', 'studying patterns', 'investigating data'],
  
  // Network Concepts
  'network': ['web connecting', 'highway system', 'communication network'],
  'connection': ['link between', 'bridge connecting', 'path linking'],
  'protocol': ['language speaking', 'rules governing', 'standard following'],
  'request': ['question asking', 'request making', 'demand sending'],
  'response': ['answer giving', 'reply sending', 'result returning']
};

/**
 * Generate visual metaphor for concept
 * @param {string} concept - Concept to visualize
 * @param {string} context - Additional context
 * @returns {string|null} - Visual metaphor or null
 */
export function generateMetaphor(concept, context = '') {
  if (!concept) return null;
  
  const conceptLower = concept.toLowerCase();
  
  // Direct match
  for (const [key, metaphors] of Object.entries(CONCEPT_METAPHORS)) {
    if (conceptLower.includes(key)) {
      const selected = metaphors[Math.floor(Math.random() * metaphors.length)];
      return selected;
    }
  }
  
  // Partial match
  const words = conceptLower.split(/\s+/);
  for (const word of words) {
    for (const [key, metaphors] of Object.entries(CONCEPT_METAPHORS)) {
      if (word.includes(key) || key.includes(word)) {
        const selected = metaphors[Math.floor(Math.random() * metaphors.length)];
        return selected;
      }
    }
  }
  
  // Generate generic metaphor
  return generateGenericMetaphor(concept, context);
}

/**
 * Generate generic metaphor when no specific match found
 * @param {string} concept - Concept
 * @param {string} context - Context
 * @returns {string} - Generic metaphor
 */
function generateGenericMetaphor(concept, context) {
  const genericMetaphors = [
    `visual representation of ${concept}`,
    `creative interpretation of ${concept}`,
    `${concept} visualized as concept`,
    `abstract representation of ${concept}`,
    `${concept} shown metaphorically`
  ];
  
  return genericMetaphors[Math.floor(Math.random() * genericMetaphors.length)];
}

/**
 * Extract concepts from content
 * @param {string} content - Content text
 * @returns {Array<string>} - Extracted concepts
 */
export function extractConcepts(content) {
  if (!content) return [];
  
  const contentLower = content.toLowerCase();
  const concepts = [];
  
  // Find matching concepts
  for (const concept of Object.keys(CONCEPT_METAPHORS)) {
    if (contentLower.includes(concept)) {
      concepts.push(concept);
    }
  }
  
  return concepts;
}

/**
 * Generate multiple metaphors for content
 * @param {string} content - Content text
 * @param {number} count - Number of metaphors to generate
 * @returns {Array<string>} - Array of metaphors
 */
export function generateMetaphorsForContent(content, count = 3) {
  const concepts = extractConcepts(content);
  const metaphors = [];
  
  // Generate metaphors for found concepts
  concepts.slice(0, count).forEach(concept => {
    const metaphor = generateMetaphor(concept, content);
    if (metaphor && !metaphors.includes(metaphor)) {
      metaphors.push(metaphor);
    }
  });
  
  // Fill remaining slots with generic metaphors
  while (metaphors.length < count) {
    const generic = generateGenericMetaphor('concept', content);
    if (!metaphors.includes(generic)) {
      metaphors.push(generic);
    }
  }
  
  return metaphors.slice(0, count);
}

/**
 * Avoid cliché representations
 * @param {string} concept - Concept
 * @returns {boolean} - True if should avoid cliché
 */
export function isCliché(concept) {
  const clichés = [
    'lightbulb for idea',
    'gears for process',
    'puzzle pieces for problem',
    'handshake for agreement'
  ];
  
  const conceptLower = concept.toLowerCase();
  return clichés.some(cliché => conceptLower.includes(cliché));
}

/**
 * Suggest alternative to cliché
 * @param {string} concept - Concept
 * @returns {string} - Alternative metaphor
 */
export function suggestAlternative(concept) {
  const alternatives = {
    'lightbulb': ['spark of inspiration', 'moment of clarity', 'breakthrough insight'],
    'gears': ['smooth workflow', 'efficient process', 'well-oiled system'],
    'puzzle': ['interconnected solution', 'fitting pieces together', 'complete picture'],
    'handshake': ['mutual agreement', 'partnership formed', 'connection made']
  };
  
  for (const [cliché, alts] of Object.entries(alternatives)) {
    if (concept.toLowerCase().includes(cliché)) {
      return alts[Math.floor(Math.random() * alts.length)];
    }
  }
  
  return generateMetaphor(concept);
}

export default {
  generateMetaphor,
  extractConcepts,
  generateMetaphorsForContent,
  isCliché,
  suggestAlternative
};










