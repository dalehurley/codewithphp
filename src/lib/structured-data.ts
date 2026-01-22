/**
 * Structured Data (JSON-LD) for Astro
 * 
 * Generate Schema.org structured data for educational content
 */

import { getSeriesDisplayName } from './seo'

export interface PageFrontmatter {
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

/**
 * Generate Article schema for chapter pages
 */
export function generateArticleSchema(
  slug: string,
  title: string,
  description: string,
  frontmatter: PageFrontmatter
): object | null {
  if (!frontmatter.series || frontmatter.chapter === undefined) return null
  
  const chapterUrl = `https://codewithphp.com/${slug}/`
  const socialImage = `https://codewithphp.com/social/${frontmatter.series}-chapter-${String(frontmatter.chapter).padStart(2, '0')}.jpg`
  
  const modifiedDate = frontmatter.dateModified || new Date().toISOString()
  
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
export function generateHowToSchema(
  slug: string,
  title: string,
  description: string,
  frontmatter: PageFrontmatter
): object | null {
  if (!frontmatter.series || frontmatter.chapter === undefined) return null
  
  const chapterUrl = `https://codewithphp.com/${slug}/`
  
  // Use explicit steps if provided, fallback to teaches array
  let steps = frontmatter.steps || (frontmatter.teaches ? frontmatter.teaches.map(item => ({
    name: item,
    text: item
  })) : [])
  
  // Fallback: Use description as steps
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
export function generateCourseSchema(
  slug: string,
  title: string,
  description: string,
  frontmatter: PageFrontmatter
): object | null {
  if (!frontmatter.series) return null
  
  const seriesUrl = `https://codewithphp.com/${slug}/`
  
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
export function generateLearningResourceSchema(
  slug: string,
  title: string,
  description: string,
  frontmatter: PageFrontmatter
): object | null {
  if (!frontmatter.series || frontmatter.chapter === undefined) return null
  
  const chapterUrl = `https://codewithphp.com/${slug}/`
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
 * Generate breadcrumb schema
 */
export function generateBreadcrumbSchema(slug: string, title: string): object | null {
  if (slug === '' || slug === 'index') return null
  
  const parts = slug.split('/').filter(Boolean)
  const items = [
    {
      '@type': 'ListItem',
      position: 1,
      name: 'Home',
      item: 'https://codewithphp.com/'
    }
  ]
  
  let currentPath = ''
  parts.forEach((part, index) => {
    currentPath += `/${part}`
    const name = index === parts.length - 1 ? title : part
    items.push({
      '@type': 'ListItem',
      position: index + 2,
      name: name,
      item: `https://codewithphp.com${currentPath}/`
    })
  })
  
  return {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: items
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
    },
    'php-algorithms': {
      level: 'Intermediate',
      workload: 'PT30H',
      teaches: [
        'Algorithm complexity and Big O notation',
        'Sorting and searching algorithms',
        'Data structures',
        'Graph algorithms',
        'Dynamic programming',
        'Performance optimization'
      ],
      audienceType: 'Intermediate PHP developers',
      keywords: ['PHP', 'algorithms', 'data structures', 'Big O', 'performance', 'optimization']
    },
    'build-crm-laravel-12': {
      level: 'Intermediate',
      workload: 'PT60H',
      teaches: [
        'Laravel 12 project setup',
        'Authentication and authorization',
        'CRM data modeling',
        'REST API design',
        'Queues and background jobs',
        'Deployment and monitoring'
      ],
      audienceType: 'Intermediate PHP developers building real applications',
      keywords: ['Laravel', 'CRM', 'PHP', 'SaaS', 'REST API', 'authentication', 'Eloquent']
    },
    'claude-php-developers': {
      level: 'Intermediate',
      workload: 'PT35H',
      teaches: [
        'Claude API fundamentals',
        'Prompt engineering',
        'Tool use and agents',
        'Content generation',
        'AI integrations in Laravel',
        'Safety and cost controls'
      ],
      audienceType: 'PHP developers integrating AI features',
      keywords: ['Claude', 'AI', 'PHP', 'LLM', 'prompting', 'automation']
    },
    'data-science-php-developers': {
      level: 'Intermediate',
      workload: 'PT35H',
      teaches: [
        'Data collection and cleaning',
        'Exploratory data analysis',
        'Machine learning in PHP',
        'Visualization and reporting',
        'Model deployment'
      ],
      audienceType: 'PHP developers learning data science',
      keywords: ['data science', 'PHP', 'machine learning', 'data analysis', 'visualization']
    },
    'php-for-java-developers': {
      level: 'Intermediate',
      workload: 'PT20H',
      teaches: [
        'PHP syntax and type system',
        'OOP in PHP vs Java',
        'Composer and dependencies',
        'Framework fundamentals',
        'Security best practices'
      ],
      audienceType: 'Java developers transitioning to PHP',
      keywords: ['PHP', 'Java', 'OOP', 'Laravel', 'Symfony', 'backend']
    },
    'php-typescript-developers': {
      level: 'Intermediate',
      workload: 'PT18H',
      teaches: [
        'Modern PHP syntax',
        'Type system comparisons',
        'Async patterns in PHP',
        'Testing and code quality',
        'Laravel foundations'
      ],
      audienceType: 'TypeScript developers learning PHP',
      keywords: ['PHP', 'TypeScript', 'types', 'backend', 'Laravel']
    },
    'python-developers-love-php-laravel': {
      level: 'Intermediate',
      workload: 'PT12H',
      teaches: [
        'PHP vs Python concepts',
        'Laravel productivity',
        'Eloquent vs Django ORM',
        'REST API development',
        'Deployment best practices'
      ],
      audienceType: 'Python developers exploring PHP and Laravel',
      keywords: ['PHP', 'Laravel', 'Python', 'Django', 'Flask', 'web development']
    },
    'rails-developers-love-laravel': {
      level: 'Intermediate',
      workload: 'PT12H',
      teaches: [
        'Laravel fundamentals',
        'Ruby on Rails comparisons',
        'Eloquent ORM',
        'Testing and deployment',
        'Laravel ecosystem'
      ],
      audienceType: 'Rails developers transitioning to Laravel',
      keywords: ['Laravel', 'Rails', 'PHP', 'Ruby', 'MVC', 'web development']
    }
  }
  
  return seriesMetadata[seriesSlug] || seriesMetadata['php-basics']
}
