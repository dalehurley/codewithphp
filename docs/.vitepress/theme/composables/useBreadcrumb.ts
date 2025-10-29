/**
 * Breadcrumb Structured Data
 * 
 * Generate BreadcrumbList schema for navigation hierarchy
 */

interface PageData {
  title: string
  relativePath: string
  frontmatter: {
    series?: string
    chapter?: number
  }
}

/**
 * Generate BreadcrumbList schema
 */
export function generateBreadcrumbSchema(pageData: PageData): object | null {
  const { relativePath, frontmatter, title } = pageData
  
  const breadcrumbs: Array<{
    position: number
    name: string
    item: string
  }> = []
  
  // Always start with home
  breadcrumbs.push({
    position: 1,
    name: 'Home',
    item: 'https://codewithphp.com'
  })
  
  // Check if it's a series page
  const seriesMatch = relativePath.match(/series\/([^/]+)\//)
  if (seriesMatch) {
    const seriesSlug = seriesMatch[1]
    const seriesName = getSeriesDisplayName(seriesSlug)
    
    breadcrumbs.push({
      position: 2,
      name: seriesName,
      item: `https://codewithphp.com/series/${seriesSlug}/`
    })
    
    // If it's a chapter, add the chapter breadcrumb
    if (relativePath.includes('/chapters/') && frontmatter.chapter !== undefined) {
      const chapterUrl = `https://codewithphp.com/${relativePath.replace(/\.md$/, '')}`
      
      breadcrumbs.push({
        position: 3,
        name: title,
        item: chapterUrl
      })
    }
  }
  
  // Only generate breadcrumbs if we have more than just home
  if (breadcrumbs.length === 1) {
    return null
  }
  
  return {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: breadcrumbs.map(crumb => ({
      '@type': 'ListItem',
      position: crumb.position,
      name: crumb.name,
      item: crumb.item
    }))
  }
}

/**
 * Helper: Get series display name
 */
function getSeriesDisplayName(seriesSlug: string): string {
  const names: Record<string, string> = {
    'php-basics': 'PHP Basics',
    'ai-ml-php-developers': 'AI/ML for PHP Developers'
  }
  return names[seriesSlug] || seriesSlug
}

/**
 * Generate breadcrumb navigation HTML (for visual breadcrumbs)
 */
export function generateBreadcrumbHtml(pageData: PageData): string {
  const { relativePath, frontmatter, title } = pageData
  
  const breadcrumbs: Array<{ name: string; url: string }> = []
  
  breadcrumbs.push({ name: 'Home', url: '/' })
  
  const seriesMatch = relativePath.match(/series\/([^/]+)\//)
  if (seriesMatch) {
    const seriesSlug = seriesMatch[1]
    const seriesName = getSeriesDisplayName(seriesSlug)
    breadcrumbs.push({ name: seriesName, url: `/series/${seriesSlug}/` })
    
    if (relativePath.includes('/chapters/')) {
      breadcrumbs.push({ name: title, url: '' })
    }
  }
  
  return breadcrumbs
    .map((crumb, index) => {
      if (crumb.url) {
        return `<a href="${crumb.url}">${crumb.name}</a>`
      }
      return `<span>${crumb.name}</span>`
    })
    .join(' › ')
}


