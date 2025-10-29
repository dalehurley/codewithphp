/**
 * SEO Utility Functions
 * 
 * Helper functions for generating SEO metadata, social images,
 * and structured data for Code with PHP tutorials.
 */

interface PageData {
  title: string
  description: string
  relativePath: string
  frontmatter: {
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
export function generateSocialImagePath(pageData: PageData): string {
  const { relativePath, frontmatter } = pageData
  
  // For series index pages
  if (relativePath.match(/series\/([^/]+)\/index\.md$/)) {
    const series = relativePath.match(/series\/([^/]+)\//)?.[1]
    return `https://codewithphp.com/social/${series}-overview.jpg`
  }
  
  // For chapter pages
  if (frontmatter.series && frontmatter.chapter !== undefined) {
    const chapterNum = String(frontmatter.chapter).padStart(2, '0')
    return `https://codewithphp.com/social/${frontmatter.series}-chapter-${chapterNum}.jpg`
  }
  
  // For homepage
  if (relativePath === 'index.md') {
    return 'https://codewithphp.com/social/homepage.jpg'
  }
  
  // Default fallback
  return 'https://codewithphp.com/images/php-basics/chapter-00-landing-hero-full.webp'
}

/**
 * Get canonical URL for a page
 */
export function getCanonicalUrl(relativePath: string): string {
  const cleanPath = relativePath
    .replace(/\.md$/, '')
    .replace(/\/index$/, '/')
    .replace(/index$/, '')
  
  return `https://codewithphp.com/${cleanPath}`
}

/**
 * Extract series name from relative path
 */
export function extractSeriesFromPath(relativePath: string): string | null {
  const match = relativePath.match(/series\/([^/]+)\//)
  return match ? match[1] : null
}

/**
 * Get series display name
 */
export function getSeriesDisplayName(seriesSlug: string): string {
  const seriesNames: Record<string, string> = {
    'php-basics': 'PHP Basics',
    'ai-ml-php-developers': 'AI/ML for PHP Developers'
  }
  
  return seriesNames[seriesSlug] || seriesSlug
}

/**
 * Generate meta description from content if frontmatter is missing
 */
export function generateMetaDescription(pageData: PageData, content?: string): string {
  if (pageData.description) {
    return pageData.description
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
export function generateKeywords(pageData: PageData): string[] {
  const keywords: string[] = []
  
  // Add frontmatter keywords
  if (pageData.frontmatter.keywords) {
    keywords.push(...pageData.frontmatter.keywords)
  }
  
  // Add series name
  const series = extractSeriesFromPath(pageData.relativePath)
  if (series) {
    keywords.push(getSeriesDisplayName(series))
  }
  
  // Add difficulty level
  if (pageData.frontmatter.difficulty) {
    keywords.push(`${pageData.frontmatter.difficulty} tutorial`)
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
export function isChapterPage(pageData: PageData): boolean {
  return pageData.relativePath.includes('/chapters/') && 
         pageData.frontmatter.chapter !== undefined
}

/**
 * Check if page is a series index
 */
export function isSeriesIndex(pageData: PageData): boolean {
  return pageData.relativePath.match(/series\/[^/]+\/index\.md$/) !== null
}

/**
 * Check if page is the homepage
 */
export function isHomepage(pageData: PageData): boolean {
  return pageData.relativePath === 'index.md'
}

