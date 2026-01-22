/**
 * SEO Utility Functions for Astro
 * 
 * Helper functions for generating SEO metadata, social images,
 * and structured data for Code with PHP tutorials.
 */

export interface PageData {
  title: string
  description?: string
  slug: string
  data: {
    series?: string
    chapter?: number
    difficulty?: string
    keywords?: string[]
    author?: string
    datePublished?: string
    dateModified?: string
    estimatedTime?: string
    teaches?: string[]
    prerequisites?: string[]
  }
}

/**
 * Generate the path to a social share image for a page
 */
export function generateSocialImagePath(slug: string, frontmatter: any): string {
  // For series index pages
  if (slug.match(/series\/([^/]+)$/)) {
    const series = slug.match(/series\/([^/]+)/)?.[1]
    return `https://codewithphp.com/social/${series}-overview.jpg`
  }
  
  // For chapter pages
  if (frontmatter.series && frontmatter.chapter !== undefined) {
    const chapterNum = String(frontmatter.chapter).padStart(2, '0')
    return `https://codewithphp.com/social/${frontmatter.series}-chapter-${chapterNum}.jpg`
  }
  
  // For homepage
  if (slug === '') {
    return 'https://codewithphp.com/social/homepage.jpg'
  }
  
  // Default fallback
  return 'https://codewithphp.com/images/php-basics/chapter-00-landing-hero-full.webp'
}

/**
 * Get canonical URL for a page
 */
export function getCanonicalUrl(slug: string): string {
  return `https://codewithphp.com/${slug}/`
}

/**
 * Extract series name from slug
 */
export function extractSeriesFromPath(slug: string): string | null {
  const match = slug.match(/series\/([^/]+)\//)
  return match ? match[1] : null
}

/**
 * Get series display name
 */
export function getSeriesDisplayName(seriesSlug: string): string {
  const seriesNames: Record<string, string> = {
    'php-basics': 'PHP Basics',
    'ai-ml-php-developers': 'AI/ML for PHP Developers',
    'python-developers-love-php-laravel': 'Why Python Developers Will Love PHP and Laravel',
    'php-algorithms': 'Algorithms for PHP Developers',
    'php-for-java-developers': 'PHP for Java Developers',
    'data-science-php-developers': 'Data Science for PHP Developers',
    'claude-php-developers': 'Claude for PHP Developers',
    'php-typescript-developers': 'PHP for TypeScript Developers',
    'build-crm-laravel-12': 'Build a CRM with Laravel 12',
    'rails-developers-love-laravel': 'Why Ruby on Rails Developers Will Love Laravel'
  }
  
  return seriesNames[seriesSlug] || seriesSlug
}

/**
 * Generate meta description from content if frontmatter is missing
 */
export function generateMetaDescription(description?: string, content?: string): string {
  if (description) {
    return description
  }
  
  if (content) {
    // Extract first paragraph after heading
    const match = content.match(/##?\s+[^\n]+\n\n(.+?)(\n\n|$)/)
    if (match) {
      return match[1].substring(0, 160) + '...'
    }
  }
  
  return 'Learn PHP and its ecosystem from first principles to advanced topics.'
}

/**
 * Generate keywords array from frontmatter and content
 */
export function generateKeywords(slug: string, frontmatter: any): string[] {
  const keywords: string[] = []
  
  // Add frontmatter keywords
  if (frontmatter.keywords) {
    keywords.push(...frontmatter.keywords)
  }
  
  // Add series name
  const series = extractSeriesFromPath(slug)
  if (series) {
    keywords.push(getSeriesDisplayName(series))
  }
  
  // Add difficulty level
  if (frontmatter.difficulty) {
    keywords.push(`${frontmatter.difficulty} tutorial`)
  }
  
  // Add generic PHP keywords
  keywords.push('PHP tutorial', 'PHP 8.4', 'learn PHP')
  
  return [...new Set(keywords)] // Remove duplicates
}

/**
 * Format ISO 8601 duration to human readable
 */
export function formatDuration(isoDuration?: string): string {
  if (!isoDuration) return ''
  
  const match = isoDuration.match(/PT(\d+H)?(\d+M)?/)
  if (!match) return ''
  
  const hours = match[1] ? parseInt(match[1]) : 0
  const minutes = match[2] ? parseInt(match[2]) : 0
  
  if (hours > 0) {
    return `${hours}h ${minutes}m`
  }
  
  return `${minutes} minutes`
}

/**
 * Check if page is a chapter
 */
export function isChapterPage(slug: string, frontmatter: any): boolean {
  return slug.includes('/chapters/') && frontmatter.chapter !== undefined
}

/**
 * Check if page is a series index
 */
export function isSeriesIndex(slug: string): boolean {
  return slug.match(/series\/[^/]+$/) !== null
}

/**
 * Check if page is the homepage
 */
export function isHomepage(slug: string): boolean {
  return slug === '' || slug === 'index'
}
