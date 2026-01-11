/**
 * Structured Data (JSON-LD) Composables
 * 
 * Generate Schema.org structured data for educational content
 */

interface PageData {
  title: string
  description: string
  relativePath: string
  lastUpdated?: number
  frontmatter: {
    series?: string
    chapter?: number
    difficulty?: string
    prerequisites?: string[]
    estimatedTime?: string
    teaches?: string[]
    datePublished?: string
    dateModified?: string
    author?: string
    steps?: Array<{ name: string; text: string; image?: string }>
  }
}

/**
 * Generate Article schema for chapter pages
 */
export function generateArticleSchema(pageData: PageData): object | null {
  const { frontmatter, title, description, relativePath, lastUpdated } = pageData
  
  if (!frontmatter.series || frontmatter.chapter === undefined) return null
  
  const chapterUrl = `https://codewithphp.com/${relativePath.replace(/\.md$/, '').replace(/\/index$/, '/')}`
  const socialImage = `https://codewithphp.com/social/${frontmatter.series}-chapter-${String(frontmatter.chapter).padStart(2, '0')}.jpg`
  
  const modifiedDate = frontmatter.dateModified || (lastUpdated ? new Date(lastUpdated).toISOString() : new Date().toISOString())
  
  return {
    '@context': 'https://schema.org',
    '@type': 'TechArticle',
    '@id': `${chapterUrl}#article`,
    headline: title,
    description: description,
    image: [socialImage],
    datePublished: frontmatter.datePublished || modifiedDate,
    dateModified: modifiedDate,
    author: {
      '@type': 'Organization',
      name: frontmatter.author || 'Code with PHP',
      url: 'https://codewithphp.com'
    },
    publisher: {
      '@type': 'Organization',
      name: 'Code with PHP',
      url: 'https://codewithphp.com',
      logo: {
        '@type': 'ImageObject',
        url: 'https://codewithphp.com/images/php-basics/chapter-00-landing-hero-full.webp'
      }
    },
    mainEntityOfPage: {
      '@type': 'WebPage',
      '@id': chapterUrl
    },
    proficiencyLevel: frontmatter.difficulty || 'Beginner'
  }
}

/**
 * Generate HowTo schema for chapter pages
 */
export function generateHowToSchema(pageData: PageData): object | null {
  const { frontmatter, title, description, relativePath } = pageData
  
  if (!frontmatter.series || frontmatter.chapter === undefined) return null
  
  const chapterUrl = `https://codewithphp.com/${relativePath.replace(/\.md$/, '').replace(/\/index$/, '/')}`
  
  // Use explicit steps if provided, fallback to teaches array, then fallback to a default step from description
  let steps = frontmatter.steps || (frontmatter.teaches ? frontmatter.teaches.map(item => ({
    name: item,
    text: item
  })) : [])
  
  // Fallback: Use description as the first step if no steps are provided
  if (steps.length === 0 && description) {
    steps = [
      {
        name: 'Introduction',
        text: description
      },
      {
        name: 'Implementation',
        text: 'Follow the step-by-step instructions in the chapter to implement the solution'
      },
      {
        name: 'Verification',
        text: 'Verify your implementation by running the provided code samples'
      }
    ]
  }
  
  if (steps.length === 0) return null
  
  return {
    '@context': 'https://schema.org',
    '@type': 'HowTo',
    '@id': `${chapterUrl}#howto`,
    name: title,
    description: description,
    totalTime: frontmatter.estimatedTime || 'PT30M',
    supply: [
      {
        '@type': 'HowToSupply',
        name: 'PHP 8.4'
      }
    ],
    tool: [
      {
        '@type': 'HowToTool',
        name: 'Terminal'
      },
      {
        '@type': 'HowToTool',
        name: 'Code Editor'
      }
    ],
    step: steps.map((s, index) => ({
      '@type': 'HowToStep',
      url: `${chapterUrl}#step-${index + 1}`,
      name: s.name,
      itemListElement: [{
        '@type': 'HowToDirection',
        text: s.text
      }]
    }))
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
    },
    'python-developers-love-php-laravel': {
      level: 'Intermediate',
      workload: 'PT18H',
      teaches: [
        'PHP and Laravel fundamentals',
        'Mapping Python concepts to PHP/Laravel',
        'Eloquent ORM vs Django ORM',
        'Laravel routing and Blade templates',
        'REST API development in Laravel',
        'Modern PHP 8.4 features'
      ],
      audienceType: 'Python developers transitioning to PHP/Laravel',
      keywords: ['PHP', 'Laravel', 'Python', 'Django', 'Flask', 'web development', 'framework comparison']
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
    'ai-ml-php-developers': 'AI/ML for PHP Developers',
    'python-developers-love-php-laravel': 'Why Python Developers Will Love PHP and Laravel'
  }
  return names[seriesSlug] || seriesSlug
}
