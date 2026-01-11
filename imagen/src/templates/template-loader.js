/**
 * Template System
 * Load and manage prompt templates
 */

import { promises as fs } from 'fs';
import path from 'path';

/**
 * Template storage
 */
const templates = new Map();

/**
 * Load template from file
 * @param {string} templatePath - Path to template file
 * @returns {Promise<Object>} - Template object
 */
export async function loadTemplate(templatePath) {
  try {
    const content = await fs.readFile(templatePath, 'utf-8');
    const template = JSON.parse(content);
    
    // Validate template structure
    if (!template.name || !template.prompt) {
      throw new Error('Invalid template structure');
    }
    
    templates.set(template.name, template);
    return template;
  } catch (error) {
    throw new Error(`Failed to load template: ${error.message}`);
  }
}

/**
 * Load all templates from directory
 * @param {string} templatesDir - Directory containing templates
 * @returns {Promise<Array<Object>>} - Array of loaded templates
 */
export async function loadTemplatesFromDirectory(templatesDir) {
  try {
    const files = await fs.readdir(templatesDir);
    const jsonFiles = files.filter(f => f.endsWith('.json'));
    const loaded = [];
    
    for (const file of jsonFiles) {
      try {
        const templatePath = path.join(templatesDir, file);
        const template = await loadTemplate(templatePath);
        loaded.push(template);
      } catch (error) {
        console.error(`Error loading template ${file}:`, error.message);
      }
    }
    
    return loaded;
  } catch (error) {
    throw new Error(`Failed to load templates: ${error.message}`);
  }
}

/**
 * Get template by name
 * @param {string} name - Template name
 * @returns {Object|null} - Template or null
 */
export function getTemplate(name) {
  return templates.get(name) || null;
}

/**
 * List all available templates
 * @returns {Array<string>} - Template names
 */
export function listTemplates() {
  return Array.from(templates.keys());
}

/**
 * Create template from options
 * @param {Object} options - Template options
 * @returns {Object} - Template object
 */
export function createTemplate(options) {
  const {
    name,
    prompt,
    description = '',
    parameters = {},
    defaults = {}
  } = options;
  
  if (!name || !prompt) {
    throw new Error('Template name and prompt are required');
  }
  
  const template = {
    name,
    prompt,
    description,
    parameters,
    defaults,
    createdAt: new Date().toISOString()
  };
  
  templates.set(name, template);
  return template;
}

/**
 * Fill template with data
 * @param {string} templateName - Template name
 * @param {Object} data - Data to fill template
 * @returns {string} - Filled prompt
 */
export function fillTemplate(templateName, data = {}) {
  const template = getTemplate(templateName);
  if (!template) {
    throw new Error(`Template not found: ${templateName}`);
  }
  
  // Merge defaults with data
  const merged = { ...template.defaults, ...data };
  
  // Replace placeholders in prompt
  let filled = template.prompt;
  
  // Replace ${key} placeholders
  filled = filled.replace(/\$\{(\w+)\}/g, (match, key) => {
    return merged[key] !== undefined ? merged[key] : match;
  });
  
  // Replace {{key}} placeholders (alternative syntax)
  filled = filled.replace(/\{\{(\w+)\}\}/g, (match, key) => {
    return merged[key] !== undefined ? merged[key] : match;
  });
  
  return filled;
}

/**
 * Save template to file
 * @param {string} templateName - Template name
 * @param {string} outputPath - Output file path
 * @returns {Promise<void>}
 */
export async function saveTemplate(templateName, outputPath) {
  const template = getTemplate(templateName);
  if (!template) {
    throw new Error(`Template not found: ${templateName}`);
  }
  
  await fs.mkdir(path.dirname(outputPath), { recursive: true });
  await fs.writeFile(outputPath, JSON.stringify(template, null, 2));
}

export default {
  loadTemplate,
  loadTemplatesFromDirectory,
  getTemplate,
  listTemplates,
  createTemplate,
  fillTemplate,
  saveTemplate
};










