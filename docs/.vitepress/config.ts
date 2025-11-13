import { defineConfig, type HeadConfig } from 'vitepress'
import { withMermaid } from 'vitepress-plugin-mermaid'
import { generateSocialImagePath, getCanonicalUrl } from './theme/utils/seo'
import { generateCourseSchema, generateLearningResourceSchema, generateWebSiteSchema, generateOrganizationSchema } from './theme/composables/useStructuredData'
import { generateBreadcrumbSchema } from './theme/composables/useBreadcrumb'
import mathjax3 from 'markdown-it-mathjax3'

export default withMermaid(
  defineConfig({
    title: 'Code with PHP',
    description: 'Learn PHP and its ecosystem from first principles to advanced.',
    base: '/',
    cleanUrls: true,
    lastUpdated: true,
    ignoreDeadLinks: true, // Temporarily disabled to test Quiz components
    // ignoreDeadLinks: [
    //   // Ignore localhost URLs used in tutorials
    //   /^http:\/\/localhost/,
    //   /^https:\/\/127\.0\.0\.1/,
    //   // Ignore relative links to chapters that may not exist yet
    //   /\.\.\/\.\.\/chapters\//,
    //   // Ignore links to chapters that don't exist yet
    //   /19b-testing-your-blog-application/,
    //   // Ignore links to code files (PHP, CSV, JSON, etc.)
    //   /\/series\/.*\/code\/.*\.(php|csv|json|txt|sql|sh|db|example)$/,
    //   // Ignore links to code files without extension
    //   /\/series\/.*\/code\/.*\/[^/]*$/
    // ],
    head: [
      ['link', { rel: 'icon', href: '/favicon.ico' }],
      ['meta', { name: 'theme-color', content: '#3c8772' }],
      ['meta', { property: 'og:type', content: 'website' }],
      ['meta', { property: 'og:locale', content: 'en' }],
      ['meta', { property: 'og:site_name', content: 'Code with PHP' }],
      
      // MathJax for LaTeX math rendering
      ['link', { rel: 'stylesheet', href: 'https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css' }],
      
      // GenAI optimization
      ['meta', { name: 'author', content: 'Code with PHP' }],
      ['meta', { name: 'publisher', content: 'Code with PHP' }],
      ['meta', { name: 'robots', content: 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' }],
      
      // SearchGPT and AI crawler hints
      ['meta', { property: 'article:publisher', content: 'https://codewithphp.com' }],
      ['meta', { property: 'article:section', content: 'Programming Tutorials' }],
      
      // Keywords for AI understanding
      ['meta', { name: 'keywords', content: 'PHP tutorial, PHP 8.4, learn PHP, PHP course, web development, programming tutorial' }],
      
      // Language
      ['meta', { httpEquiv: 'content-language', content: 'en' }],
      
      // Enforce HTTPS redirect
      ['script', {}, `
        if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
          location.replace('https://' + location.host + location.pathname + location.search + location.hash);
        }
      `]
    ],
    
    // Sitemap configuration
    sitemap: {
      hostname: 'https://codewithphp.com'
    },
    
    // Per-page metadata injection
    transformHead: ({ pageData }) => {
      const head: HeadConfig[] = []
      
      // Canonical URL
      const canonicalUrl = getCanonicalUrl(pageData.relativePath)
      head.push(['link', { rel: 'canonical', href: canonicalUrl }])
      
      // Page title and description
      const title = pageData.title || 'Code with PHP'
      const description = pageData.description || 'Learn PHP and its ecosystem from first principles to advanced topics.'
      
      // Open Graph tags
      head.push(['meta', { property: 'og:title', content: title }])
      head.push(['meta', { property: 'og:description', content: description }])
      head.push(['meta', { property: 'og:url', content: canonicalUrl }])
      
      // Social share image
      const socialImage = generateSocialImagePath(pageData)
      head.push(['meta', { property: 'og:image', content: socialImage }])
      head.push(['meta', { property: 'og:image:width', content: '1200' }])
      head.push(['meta', { property: 'og:image:height', content: '630' }])
      head.push(['meta', { property: 'og:image:alt', content: title }])
      
      // Twitter Card tags
      head.push(['meta', { name: 'twitter:card', content: 'summary_large_image' }])
      head.push(['meta', { name: 'twitter:title', content: title }])
      head.push(['meta', { name: 'twitter:description', content: description }])
      head.push(['meta', { name: 'twitter:image', content: socialImage }])
      head.push(['meta', { name: 'twitter:image:alt', content: title }])
      
      // Article metadata for GenAI
      if (pageData.frontmatter.datePublished) {
        head.push(['meta', { property: 'article:published_time', content: pageData.frontmatter.datePublished }])
      }
      if (pageData.frontmatter.dateModified || pageData.lastUpdated) {
        const modifiedDate = pageData.frontmatter.dateModified || (pageData.lastUpdated ? new Date(pageData.lastUpdated).toISOString() : new Date().toISOString())
        head.push(['meta', { property: 'article:modified_time', content: modifiedDate }])
      }
      if (pageData.frontmatter.author) {
        head.push(['meta', { name: 'article:author', content: pageData.frontmatter.author }])
      }
      
      // Structured data (JSON-LD)
      const structuredData: object[] = []
      
      // Homepage: WebSite + Organization
      if (pageData.relativePath === 'index.md') {
        structuredData.push(generateWebSiteSchema())
        structuredData.push(generateOrganizationSchema())
      }
      
      // Series index: Course schema
      if (pageData.relativePath.match(/series\/[^/]+\/index\.md$/)) {
        const courseSchema = generateCourseSchema(pageData)
        if (courseSchema) structuredData.push(courseSchema)
      }
      
      // Chapter pages: LearningResource schema
      if (pageData.frontmatter.series && pageData.frontmatter.chapter !== undefined) {
        const learningResourceSchema = generateLearningResourceSchema(pageData)
        if (learningResourceSchema) structuredData.push(learningResourceSchema)
      }
      
      // Breadcrumb schema (for all non-homepage pages)
      if (pageData.relativePath !== 'index.md') {
        const breadcrumbSchema = generateBreadcrumbSchema(pageData)
        if (breadcrumbSchema) structuredData.push(breadcrumbSchema)
      }
      
      // Inject structured data
      if (structuredData.length > 0) {
        head.push([
          'script',
          { type: 'application/ld+json' },
          JSON.stringify(structuredData.length === 1 ? structuredData[0] : structuredData)
        ])
      }
      
      return head
    },
    themeConfig: {
      nav: [
        { text: 'Home', link: '/' },
        {
          text: 'Series',
          items: [
            { text: 'PHP Basics', link: '/series/php-basics/' },
            { text: 'AI/ML for PHP Developers', link: '/series/ai-ml-php-developers/' },
            { text: 'Why Python Developers Will Love PHP and Laravel', link: '/series/python-developers-love-php-laravel/' },
            { text: 'Build a CRM with Laravel 12', link: '/series/build-crm-laravel-12/' },
          ]
        },
        { text: 'GitHub', link: 'https://github.com/dalehurley/codewithphp' }
      ],
      sidebar: {
        '/series/php-basics/': [
          { text: 'Overview', link: '/series/php-basics/' },
          {
            text: 'Chapters',
            items: [
              
              {
                text: '00 — Setting Up Your Development Environment',
                link: '/series/php-basics/chapters/00-setting-up-your-development-environment'
              },
              {
                text: '01 — Your First PHP Script',
                link: '/series/php-basics/chapters/01-your-first-php-script'
              },
              {
                text: '02 — Variables, Data Types, and Constants',
                link: '/series/php-basics/chapters/02-variables-data-types-and-constants'
              },
              {
                text: '03 — Control Structures',
                link: '/series/php-basics/chapters/03-control-structures'
              },
              {
                text: '04 — Understanding and Using Functions',
                link: '/series/php-basics/chapters/04-understanding-and-using-functions'
              },
              {
                text: '05 — Handling HTML Forms and User Input',
                link: '/series/php-basics/chapters/05-handling-html-forms-and-user-input'
              },
              {
                text: '06 — Deep Dive into Arrays',
                link: '/series/php-basics/chapters/06-deep-dive-into-arrays'
              },
              {
                text: '07 — Mastering String Manipulation',
                link: '/series/php-basics/chapters/07-mastering-string-manipulation'
              },
              {
                text: '08 — Introduction to Object-Oriented Programming',
                link: '/series/php-basics/chapters/08-introduction-to-object-oriented-programming'
              },
              {
                text: '09 — OOP: Inheritance, Abstract Classes, and Interfaces',
                link: '/series/php-basics/chapters/09-oop-inheritance-abstract-classes-and-interfaces'
              },
              {
                text: '10 — OOP: Traits and Namespaces',
                link: '/series/php-basics/chapters/10-oop-traits-and-namespaces'
              },
              {
                text: '11 — Error and Exception Handling',
                link: '/series/php-basics/chapters/11-error-and-exception-handling'
              },
              {
                text: '12 — Dependency Management with Composer',
                link: '/series/php-basics/chapters/12-dependency-management-with-composer'
              },
              {
                text: '13 — Working with the Filesystem',
                link: '/series/php-basics/chapters/13-working-with-the-filesystem'
              },
              {
                text: '14 — Interacting with Databases using PDO',
                link: '/series/php-basics/chapters/14-interacting-with-databases-using-pdo'
              },
              {
                text: '15 — Managing State with Sessions and Cookies',
                link: '/series/php-basics/chapters/15-managing-state-with-sessions-and-cookies'
              },
              {
                text: '15b — CSRF Protection & Form Security',
                link: '/series/php-basics/chapters/15b-csrf-protection-and-form-security'
              },
              {
                text: '16 — Writing Better Code with PSR-1 and PSR-12',
                link: '/series/php-basics/chapters/16-writing-better-code-with-psr-1-and-psr-12'
              },
              {
                text: '17 — Building a Basic HTTP Router',
                link: '/series/php-basics/chapters/17-building-a-basic-http-router'
              },
              {
                text: '18 — Project: Structuring a Simple Application',
                link: '/series/php-basics/chapters/18-project-structuring-a-simple-application'
              },
              {
                text: '19 — Project: Building a Simple Blog',
                link: '/series/php-basics/chapters/19-project-building-a-simple-blog'
              },
              {
                text: '20 — A Gentle Introduction to Laravel',
                link: '/series/php-basics/chapters/20-a-gentle-introduction-to-laravel'
              },
              {
                text: '21 — A Gentle Introduction to Symfony',
                link: '/series/php-basics/chapters/21-a-gentle-introduction-to-symfony'
              },
              {
                text: '22 — What to Learn Next',
                link: '/series/php-basics/chapters/22-what-to-learn-next'
              }
            ]
          }
        ],

        '/series/ai-ml-php-developers/': [
          { text: 'Overview', link: '/series/ai-ml-php-developers/' },
          {
            text: 'Chapters',
            items: [
              { text: '01 — Introduction to AI and Machine Learning for PHP Developers', link: '/series/ai-ml-php-developers/chapters/01-introduction-to-ai-and-machine-learning-for-php-developers' },
              { text: '02 — Setting Up Your AI Development Environment', link: '/series/ai-ml-php-developers/chapters/02-setting-up-your-ai-development-environment' },
              { text: '03 — Core Machine Learning Concepts and Terminology', link: '/series/ai-ml-php-developers/chapters/03-core-machine-learning-concepts-and-terminology' },
              { text: '04 — Data Collection and Preprocessing in PHP', link: '/series/ai-ml-php-developers/chapters/04-data-collection-and-preprocessing-in-php' },
              { text: '05 — Your First Machine Learning Model: Linear Regression in PHP', link: '/series/ai-ml-php-developers/chapters/05-your-first-machine-learning-model-linear-regression-in-php' },
              { text: '06 — Classification Basics and Building a Spam Filter', link: '/series/ai-ml-php-developers/chapters/06-classification-basics-and-building-a-spam-filter' },
              { text: '07 — Model Evaluation and Improvement', link: '/series/ai-ml-php-developers/chapters/07-model-evaluation-and-improvement' },
              { text: '08 — Leveraging PHP Machine Learning Libraries', link: '/series/ai-ml-php-developers/chapters/08-leveraging-php-machine-learning-libraries' },
              { text: '09 — Advanced Machine Learning Techniques (Trees, Ensembles, and Clustering)', link: '/series/ai-ml-php-developers/chapters/09-advanced-machine-learning-techniques-trees-ensembles-and-clustering' },
              { text: '10 — Neural Networks and Deep Learning Fundamentals', link: '/series/ai-ml-php-developers/chapters/10-neural-networks-and-deep-learning-fundamentals' },
              { text: '11 — Integrating PHP with Python for Advanced ML', link: '/series/ai-ml-php-developers/chapters/11-integrating-php-with-python-for-advanced-ml' },
              { text: '12 — Deep Learning with TensorFlow and PHP', link: '/series/ai-ml-php-developers/chapters/12-deep-learning-with-tensorflow-and-php' },
              { text: '13 — Natural Language Processing (NLP) Fundamentals', link: '/series/ai-ml-php-developers/chapters/13-natural-language-processing-nlp-fundamentals' },
              { text: '14 — NLP Project: Text Classification in PHP', link: '/series/ai-ml-php-developers/chapters/14-nlp-project-text-classification-in-php' },
              { text: '15 — Language Models and Text Generation with OpenAI APIs', link: '/series/ai-ml-php-developers/chapters/15-language-models-and-text-generation-with-openai-apis' },
              { text: '16 — Computer Vision Essentials for PHP Developers', link: '/series/ai-ml-php-developers/chapters/16-computer-vision-essentials-for-php-developers' },
              { text: '17 — Image Classification Project with Pre-trained Models', link: '/series/ai-ml-php-developers/chapters/17-image-classification-project-with-pre-trained-models' },
              { text: '18 — Object Detection and Recognition in PHP Applications', link: '/series/ai-ml-php-developers/chapters/18-object-detection-and-recognition-in-php-applications' },
              { text: '19 — Predictive Analytics and Time Series Data', link: '/series/ai-ml-php-developers/chapters/19-predictive-analytics-and-time-series-data' },
              { text: '20 — Time Series Forecasting Project', link: '/series/ai-ml-php-developers/chapters/20-time-series-forecasting-project' },
              { text: '21 — Recommender Systems: Theory and Use Cases', link: '/series/ai-ml-php-developers/chapters/21-recommender-systems-theory-and-use-cases' },
              { text: '22 — Building a Recommendation Engine in PHP', link: '/series/ai-ml-php-developers/chapters/22-building-a-recommendation-engine-in-php' },
              { text: '23 — Integrating AI Models into Web Applications', link: '/series/ai-ml-php-developers/chapters/23-integrating-ai-models-into-web-applications' },
              { text: '24 — Deploying and Scaling AI-Powered PHP Services', link: '/series/ai-ml-php-developers/chapters/24-deploying-and-scaling-ai-powered-php-services' },
              { text: '25 — Capstone Project and Future Trends', link: '/series/ai-ml-php-developers/chapters/25-capstone-project-and-future-trends' }
            ]
          }
        ],

        '/series/python-developers-love-php-laravel/': [
          { text: 'Overview', link: '/series/python-developers-love-php-laravel/' },
          {
            text: 'Chapters',
            items: [
              {
                text: '00 — Introduction: Why Look at PHP & Laravel',
                link: '/series/python-developers-love-php-laravel/chapters/00-introduction-why-look-at-php-laravel'
              },
              {
                text: '01 — Mapping Concepts: Python Web Frameworks vs Laravel',
                link: '/series/python-developers-love-php-laravel/chapters/01-mapping-concepts-python-web-frameworks-vs-laravel'
              },
              {
                text: '02 — Modern PHP: What\'s Changed',
                link: '/series/python-developers-love-php-laravel/chapters/02-modern-php-whats-changed'
              },
              {
                text: '03 — Laravel\'s Developer Experience: Productivity, Conventions and Tools',
                link: '/series/python-developers-love-php-laravel/chapters/03-laravel-developer-experience-productivity-tools'
              },
              {
                text: '04 — The PHP Syntax & Language Differences for Python Devs',
                link: '/series/python-developers-love-php-laravel/chapters/04-php-syntax-language-differences-for-python-devs'
              },
              {
                text: '05 — Working with Data: Eloquent ORM & Database Workflow',
                link: '/series/python-developers-love-php-laravel/chapters/05-working-with-data-eloquent-orm-database-workflow'
              },
              {
                text: '06 — Building REST APIs & Integrations: From Python Flask/Django to Laravel',
                link: '/series/python-developers-love-php-laravel/chapters/06-building-rest-apis-integrations-python-to-laravel'
              },
              {
                text: '07 — Testing, Deployment, DevOps: Best Practices You Know + Laravel Workflow',
                link: '/series/python-developers-love-php-laravel/chapters/07-testing-deployment-devops-best-practices'
              },
              {
                text: '08 — Ecosystem, Community, Packages & Where Laravel Excels',
                link: '/series/python-developers-love-php-laravel/chapters/08-ecosystem-community-packages-where-laravel-excels'
              },
              {
                text: '09 — When to Use Laravel (and When Python Still Makes Sense)',
                link: '/series/python-developers-love-php-laravel/chapters/09-when-to-use-laravel-when-python-still-makes-sense'
              },
              {
                text: '10 — Bonus: Hands-On Mini Project',
                link: '/series/python-developers-love-php-laravel/chapters/10-bonus-hands-on-mini-project'
              }
            ]
          }
        ],
        '/series/build-crm-laravel-12/': [
          { text: 'Overview', link: '/series/build-crm-laravel-12/' },
          {
            text: 'Part 1: Core Setup',
            items: [
              {
                text: '01 — Introduction & Series Overview',
                link: '/series/build-crm-laravel-12/chapters/01-introduction-series-overview'
              },
              {
                text: '02 — Setting Up Laravel 12 Project & Dev Environment',
                link: '/series/build-crm-laravel-12/chapters/02-setting-up-laravel-12-project-dev-environment'
              },
              {
                text: '03 — Laravel 12 Fundamentals & Project Structure',
                link: '/series/build-crm-laravel-12/chapters/03-laravel-12-fundamentals-project-structure'
              },
            ]
          },
          {
            text: 'Part 2: Database & Foundation',
            items: [
              {
                text: '04 — Planning Application Architecture & Data Modeling',
                link: '/series/build-crm-laravel-12/chapters/04-planning-application-architecture-data-modeling'
              },
              {
                text: '05 — Database Migrations & Schema Design',
                link: '/series/build-crm-laravel-12/chapters/05-database-migrations-schema-design'
              },
              {
                text: '06 — Eloquent Models & Relationships',
                link: '/series/build-crm-laravel-12/chapters/06-eloquent-models-relationships'
              },
              {
                text: '07 — User Authentication with React Starter Kit',
                link: '/series/build-crm-laravel-12/chapters/07-user-authentication-react-starter-kit'
              },
              {
                text: '08 — Team Management & User Roles',
                link: '/series/build-crm-laravel-12/chapters/08-team-management-user-roles'
              },
              {
                text: '09 — Authorization & Access Control',
                link: '/series/build-crm-laravel-12/chapters/09-authorization-access-control'
              },
              {
                text: '10 — Layout and UI Design Customization',
                link: '/series/build-crm-laravel-12/chapters/10-layout-ui-design-customization'
              },
            ]
          },
          {
            text: 'Part 3: Core CRM Modules',
            items: [
              {
                text: '11 — Contacts Module – Database & Model',
                link: '/series/build-crm-laravel-12/chapters/11-contacts-module-database-model'
              },
              {
                text: '12 — Contacts Module – CRUD Operations',
                link: '/series/build-crm-laravel-12/chapters/12-contacts-module-crud-operations'
              },
              {
                text: '13 — Companies Module – Database & Model',
                link: '/series/build-crm-laravel-12/chapters/13-companies-module-database-model'
              },
              {
                text: '14 — Companies Module – CRUD Operations',
                link: '/series/build-crm-laravel-12/chapters/14-companies-module-crud-operations'
              },
              {
                text: '15 — Deals Module – Database & Pipeline Design',
                link: '/series/build-crm-laravel-12/chapters/15-deals-module-database-pipeline-design'
              },
              {
                text: '16 — Deals Module – CRUD & Pipeline Interface',
                link: '/series/build-crm-laravel-12/chapters/16-deals-module-crud-pipeline-interface'
              },
              {
                text: '17 — Tasks Module – Database & Model',
                link: '/series/build-crm-laravel-12/chapters/17-tasks-module-database-model'
              },
              {
                text: '18 — Tasks Module – CRUD & Task Scheduling',
                link: '/series/build-crm-laravel-12/chapters/18-tasks-module-crud-task-scheduling'
              },
            ]
          },
          {
            text: 'Part 4: Communication & API',
            items: [
              {
                text: '19 — Notifications & Email Integration',
                link: '/series/build-crm-laravel-12/chapters/19-notifications-email-integration'
              },
              {
                text: '20 — Building a RESTful API for the CRM',
                link: '/series/build-crm-laravel-12/chapters/20-building-restful-api-crm'
              },
              {
                text: '21 — API Authentication with Sanctum',
                link: '/series/build-crm-laravel-12/chapters/21-api-authentication-sanctum'
              },
              {
                text: '22 — OAuth2 with Laravel Passport (Bonus)',
                link: '/series/build-crm-laravel-12/chapters/22-oauth2-laravel-passport'
              },
              {
                text: '23 — Social Logins with Laravel Socialite (Bonus)',
                link: '/series/build-crm-laravel-12/chapters/23-social-logins-socialite'
              },
            ]
          },
          {
            text: 'Part 5: Business Features',
            items: [
              {
                text: '24 — Subscription Billing with Laravel Cashier',
                link: '/series/build-crm-laravel-12/chapters/24-subscription-billing-cashier'
              },
              {
                text: '25 — Advanced Search with Laravel Scout (Bonus)',
                link: '/series/build-crm-laravel-12/chapters/25-advanced-search-scout'
              },
              {
                text: '26 — Real-Time Events with Laravel Reverb & Echo (Bonus)',
                link: '/series/build-crm-laravel-12/chapters/26-realtime-events-reverb-echo'
              },
              {
                text: '27 — Performance Optimization & Monitoring',
                link: '/series/build-crm-laravel-12/chapters/27-performance-optimization-monitoring'
              },
              {
                text: '28 — High-Performance Tuning with Laravel Octane (Bonus)',
                link: '/series/build-crm-laravel-12/chapters/28-high-performance-octane'
              },
            ]
          },
          {
            text: 'Part 6: Background Processing',
            items: [
              {
                text: '29 — Queues & Background Jobs',
                link: '/series/build-crm-laravel-12/chapters/29-queues-background-jobs'
              },
              {
                text: '30 — Monitoring Queues with Laravel Horizon (Bonus)',
                link: '/series/build-crm-laravel-12/chapters/30-monitoring-queues-horizon'
              },
            ]
          },
          {
            text: 'Part 7: Testing & Production',
            items: [
              {
                text: '31 — Automated Browser Testing with Laravel Dusk (Bonus)',
                link: '/series/build-crm-laravel-12/chapters/31-browser-testing-dusk'
              },
              {
                text: '32 — Debugging & Monitoring with Laravel Telescope (Bonus)',
                link: '/series/build-crm-laravel-12/chapters/32-debugging-monitoring-telescope'
              },
              {
                text: '33 — Feature Flags with Laravel Pennant (Bonus)',
                link: '/series/build-crm-laravel-12/chapters/33-feature-flags-pennant'
              },
              {
                text: '34 — AI-Assisted Development with Laravel Boost (Bonus)',
                link: '/series/build-crm-laravel-12/chapters/34-ai-assisted-development-boost'
              },
              {
                text: '35 — Mobile & PWA Support (Bonus)',
                link: '/series/build-crm-laravel-12/chapters/35-mobile-pwa-support'
              },
              {
                text: '36 — Multi-Tenancy & Tenant Management (Bonus)',
                link: '/series/build-crm-laravel-12/chapters/36-multi-tenancy-tenant-management'
              },
              {
                text: '37 — Extending the CRM via Packages & Plugins (Bonus)',
                link: '/series/build-crm-laravel-12/chapters/37-extending-crm-packages-plugins'
              },
              {
                text: '38 — Deployment & DevOps',
                link: '/series/build-crm-laravel-12/chapters/38-deployment-devops'
              },
              {
                text: '39 — Best Practices & Conclusion',
                link: '/series/build-crm-laravel-12/chapters/39-best-practices-conclusion'
              },
            ]
          },
          {
            text: 'Bonus Chapter',
            items: [
              {
                text: '40 — Jetstream Alternative — Team Management & Authentication (Bonus)',
                link: '/series/build-crm-laravel-12/chapters/40-bonus-jetstream-alternative-authentication'
              },
            ]
          },
        ],
      },
      socialLinks: [
        { icon: 'github', link: 'https://github.com/dalehurley/codewithphp' }
      ],
      editLink: {
        pattern:
          'https://github.com/dalehurley/codewithphp/edit/main/docs/:path'
      },
      outline: {
        level: [2, 3],
        label: 'On This Page'
      },
      search: {
        provider: 'local',
        options: {
          detailedView: true,
          miniSearch: {
            searchOptions: {
              fuzzy: 0.2,
              prefix: true,
              boost: {
                title: 4,
                heading: 3,
                text: 2
              }
            }
          },
          locales: {
            root: {
              translations: {
                button: {
                  buttonText: 'Search',
                  buttonAriaLabel: 'Search documentation'
                },
                modal: {
                  displayDetails: 'Display detailed list',
                  resetButtonTitle: 'Reset search',
                  backButtonTitle: 'Close search',
                  noResultsText: 'No results for',
                  footer: {
                    selectText: 'to select',
                    selectKeyAriaLabel: 'enter',
                    navigateText: 'to navigate',
                    navigateUpKeyAriaLabel: 'up arrow',
                    navigateDownKeyAriaLabel: 'down arrow',
                    closeText: 'to close',
                    closeKeyAriaLabel: 'escape'
                  }
                }
              }
            }
          }
        }
      }
    },
    markdown: {
      lineNumbers: true,
      theme: {
        light: 'github-light',
        dark: 'github-dark'
      },
      config: (md) => {
        md.use(mathjax3)
      }
    },
    mermaid: {
      theme: 'default'
    }
  })
)


