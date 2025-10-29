/**
 * Structured Data (JSON-LD) Composables
 * 
 * Generate Schema.org structured data for educational content
 */

interface PageData {
  title: string
  description: string
  relativePath: string
  frontmatter: {
    series?: string
    chapter?: number
    difficulty?: string
    prerequisites?: string[]
    estimatedTime?: string
    teaches?: string[]
    datePublished?: string
    dateModified?: string
  }
}

/**
 * Generate Course schema for series index pages
 */
export function generateCourseSchema(pageData: PageData): object | null {
  const { frontmatter, title, description, relativePath } = pageData
  
  if (!frontmatter.series) return null
  
  const seriesUrl = `https://codewithphp.com/${relativePath.replace(/\.md$/, '')}`
  
  // Get series-specific data
  const seriesData = getSeriesData(frontmatter.series)
  
  return {
    '@context': 'https://schema.org',
    '@type': 'Course',
    '@id': seriesUrl,
    name: title,
    description: description,
    url: seriesUrl,
    provider: {
      '@type': 'Organization',
      name: 'Code with PHP',
      url: 'https://codewithphp.com',
      logo: {
        '@type': 'ImageObject',
        url: 'https://codewithphp.com/images/php-basics/chapter-00-landing-hero-full.webp'
      }
    },
    educationalLevel: seriesData.level,
    hasCourseInstance: {
      '@type': 'CourseInstance',
      courseMode: 'online',
      courseWorkload: seriesData.workload,
      isAccessibleForFree: true,
      inLanguage: 'en'
    },
    teaches: seriesData.teaches,
    audience: {
      '@type': 'EducationalAudience',
      educationalRole: 'student',
      audienceType: seriesData.audienceType
    },
    license: 'https://opensource.org/licenses/MIT',
    isAccessibleForFree: true,
    inLanguage: 'en',
    keywords: seriesData.keywords.join(', ')
  }
}

/**
 * Generate LearningResource schema for chapter pages
 */
export function generateLearningResourceSchema(pageData: PageData): object | null {
  const { frontmatter, title, description, relativePath } = pageData
  
  if (!frontmatter.series || frontmatter.chapter === undefined) return null
  
  const chapterUrl = `https://codewithphp.com/${relativePath.replace(/\.md$/, '')}`
  const seriesUrl = `https://codewithphp.com/series/${frontmatter.series}/`
  const seriesDisplayName = getSeriesDisplayName(frontmatter.series)
  
  return {
    '@context': 'https://schema.org',
    '@type': 'LearningResource',
    '@id': chapterUrl,
    name: title,
    description: description,
    url: chapterUrl,
    learningResourceType: 'Tutorial',
    educationalLevel: frontmatter.difficulty || 'Beginner',
    timeRequired: frontmatter.estimatedTime || 'PT30M',
    teaches: frontmatter.teaches || [],
    isPartOf: {
      '@type': 'Course',
      '@id': seriesUrl,
      name: seriesDisplayName,
      url: seriesUrl
    },
    provider: {
      '@type': 'Organization',
      name: 'Code with PHP',
      url: 'https://codewithphp.com'
    },
    author: {
      '@type': 'Organization',
      name: 'Code with PHP',
      url: 'https://codewithphp.com'
    },
    datePublished: frontmatter.datePublished,
    dateModified: frontmatter.dateModified,
    isAccessibleForFree: true,
    inLanguage: 'en',
    license: 'https://opensource.org/licenses/MIT'
  }
}

/**
 * Generate WebSite schema for homepage
 */
export function generateWebSiteSchema(): object {
  return {
    '@context': 'https://schema.org',
    '@type': 'WebSite',
    '@id': 'https://codewithphp.com/#website',
    name: 'Code with PHP',
    description: 'Learn PHP and its ecosystem from first principles to advanced topics',
    url: 'https://codewithphp.com',
    inLanguage: 'en',
    publisher: {
      '@type': 'Organization',
      '@id': 'https://codewithphp.com/#organization',
      name: 'Code with PHP',
      url: 'https://codewithphp.com',
      logo: {
        '@type': 'ImageObject',
        url: 'https://codewithphp.com/images/php-basics/chapter-00-landing-hero-full.webp'
      }
    },
    potentialAction: {
      '@type': 'SearchAction',
      target: 'https://codewithphp.com/?q={search_term_string}',
      'query-input': 'required name=search_term_string'
    }
  }
}

/**
 * Generate Organization schema
 */
export function generateOrganizationSchema(): object {
  return {
    '@context': 'https://schema.org',
    '@type': 'Organization',
    '@id': 'https://codewithphp.com/#organization',
    name: 'Code with PHP',
    url: 'https://codewithphp.com',
    logo: {
      '@type': 'ImageObject',
      url: 'https://codewithphp.com/images/php-basics/chapter-00-landing-hero-full.webp'
    },
    sameAs: [
      'https://github.com/dalehurley/codewithphp'
    ],
    description: 'Comprehensive, hands-on PHP tutorials from beginner to advanced'
  }
}

/**
 * Helper: Get series-specific metadata
 */
function getSeriesData(seriesSlug: string): {
  level: string
  workload: string
  teaches: string[]
  audienceType: string
  keywords: string[]
} {
  const seriesMetadata: Record<string, any> = {
    'php-basics': {
      level: 'Beginner',
      workload: 'PT25H',
      teaches: [
        'PHP fundamentals',
        'Object-oriented programming',
        'Database interaction',
        'Web application development',
        'MVC architecture',
        'Laravel and Symfony basics'
      ],
      audienceType: 'Beginner developers, developers transitioning from other languages',
      keywords: ['PHP', 'PHP 8.4', 'web development', 'programming tutorial', 'backend development']
    },
    'ai-ml-php-developers': {
      level: 'Intermediate',
      workload: 'PT40H',
      teaches: [
        'Machine learning fundamentals',
        'Natural language processing',
        'Computer vision',
        'Deep learning',
        'AI model integration',
        'PHP-ML and Rubix ML'
      ],
      audienceType: 'Intermediate PHP developers, developers learning AI/ML',
      keywords: ['PHP', 'machine learning', 'artificial intelligence', 'AI', 'ML', 'NLP', 'computer vision']
    }
  }
  
  return seriesMetadata[seriesSlug] || seriesMetadata['php-basics']
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


