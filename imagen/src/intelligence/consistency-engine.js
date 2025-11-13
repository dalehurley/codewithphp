/**
 * Visual Consistency Engine
 * Maintain character/object consistency across series
 */

/**
 * Consistency tracker
 */
const consistencyTracker = {
  characters: new Map(),
  props: new Map(),
  settings: new Map(),
  colorPalettes: new Map(),
  styles: new Map()
};

/**
 * Track visual element for consistency
 * @param {string} series - Series name
 * @param {string} elementType - Type of element (character, prop, setting, etc.)
 * @param {string} elementValue - Element value
 * @param {string} chapter - Chapter identifier
 */
export function trackElement(series, elementType, elementValue, chapter) {
  const key = `${series}:${elementType}`;
  
  if (!consistencyTracker[elementType]) {
    consistencyTracker[elementType] = new Map();
  }
  
  if (!consistencyTracker[elementType].has(key)) {
    consistencyTracker[elementType].set(key, {
      series,
      elementType,
      value: elementValue,
      chapters: []
    });
  }
  
  const element = consistencyTracker[elementType].get(key);
  if (!element.chapters.includes(chapter)) {
    element.chapters.push(chapter);
  }
}

/**
 * Get consistent element for series
 * @param {string} series - Series name
 * @param {string} elementType - Type of element
 * @returns {string|null} - Consistent element or null
 */
export function getConsistentElement(series, elementType) {
  const key = `${series}:${elementType}`;
  const element = consistencyTracker[elementType]?.get(key);
  return element ? element.value : null;
}

/**
 * Check if element should be consistent
 * @param {string} series - Series name
 * @param {string} elementType - Type of element
 * @returns {boolean} - True if should maintain consistency
 */
export function shouldMaintainConsistency(series, elementType) {
  const key = `${series}:${elementType}`;
  return consistencyTracker[elementType]?.has(key) || false;
}

/**
 * Generate consistency report
 * @param {string} series - Series name
 * @returns {Object} - Consistency report
 */
export function generateConsistencyReport(series) {
  const report = {
    series,
    elements: {},
    consistencyScore: 0,
    totalElements: 0,
    consistentElements: 0
  };
  
  const elementTypes = ['characters', 'props', 'settings', 'colorPalettes', 'styles'];
  
  elementTypes.forEach(elementType => {
    const elements = [];
    consistencyTracker[elementType]?.forEach((element, key) => {
      if (key.startsWith(`${series}:`)) {
        elements.push({
          type: element.elementType,
          value: element.value,
          chapters: element.chapters,
          usageCount: element.chapters.length
        });
        report.totalElements++;
        if (element.chapters.length > 1) {
          report.consistentElements++;
        }
      }
    });
    
    report.elements[elementType] = elements;
  });
  
  // Calculate consistency score
  if (report.totalElements > 0) {
    report.consistencyScore = (report.consistentElements / report.totalElements) * 100;
  }
  
  return report;
}

/**
 * Enforce consistency for new generation
 * @param {string} series - Series name
 * @param {Object} options - Generation options
 * @returns {Object} - Options with consistency enforced
 */
export function enforceConsistency(series, options = {}) {
  const enforced = { ...options };
  
  // Enforce character consistency
  if (shouldMaintainConsistency(series, 'characters')) {
    const consistentCharacter = getConsistentElement(series, 'characters');
    if (consistentCharacter) {
      enforced.consistentCharacter = consistentCharacter;
    }
  }
  
  // Enforce color palette consistency
  if (shouldMaintainConsistency(series, 'colorPalettes')) {
    const consistentColors = getConsistentElement(series, 'colorPalettes');
    if (consistentColors) {
      enforced.consistentColors = consistentColors;
    }
  }
  
  // Enforce style consistency
  if (shouldMaintainConsistency(series, 'styles')) {
    const consistentStyle = getConsistentElement(series, 'styles');
    if (consistentStyle) {
      enforced.consistentStyle = consistentStyle;
    }
  }
  
  return enforced;
}

/**
 * Clear consistency tracking for series
 * @param {string} series - Series name
 */
export function clearSeriesConsistency(series) {
  const elementTypes = ['characters', 'props', 'settings', 'colorPalettes', 'styles'];
  
  elementTypes.forEach(elementType => {
    const keysToDelete = [];
    consistencyTracker[elementType]?.forEach((element, key) => {
      if (key.startsWith(`${series}:`)) {
        keysToDelete.push(key);
      }
    });
    
    keysToDelete.forEach(key => {
      consistencyTracker[elementType].delete(key);
    });
  });
}

/**
 * Get all tracked elements for series
 * @param {string} series - Series name
 * @returns {Object} - All tracked elements
 */
export function getSeriesElements(series) {
  return {
    characters: getConsistentElement(series, 'characters'),
    props: getConsistentElement(series, 'props'),
    settings: getConsistentElement(series, 'settings'),
    colorPalettes: getConsistentElement(series, 'colorPalettes'),
    styles: getConsistentElement(series, 'styles')
  };
}

export default {
  trackElement,
  getConsistentElement,
  shouldMaintainConsistency,
  generateConsistencyReport,
  enforceConsistency,
  clearSeriesConsistency,
  getSeriesElements
};



