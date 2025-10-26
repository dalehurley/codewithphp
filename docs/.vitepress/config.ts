import { defineConfig } from 'vitepress'
import { withMermaid } from 'vitepress-plugin-mermaid'

export default withMermaid(
  defineConfig({
    title: 'Code with PHP',
    description: 'Learn PHP and its ecosystem from first principles to advanced.',
    base: '/',
    cleanUrls: true,
    lastUpdated: true,
    ignoreDeadLinks: [
      // Ignore localhost URLs used in tutorials
      /^http:\/\/localhost/,
      /^https:\/\/127\.0\.0\.1/,
      // Ignore relative links to chapters that may not exist yet
      /\.\.\/\.\.\/chapters\//,
      // Ignore links to chapters that don't exist yet
      /19b-testing-your-blog-application/
    ],
    head: [
      ['link', { rel: 'icon', href: '/favicon.ico' }],
      ['meta', { name: 'theme-color', content: '#3c8772' }],
      ['meta', { property: 'og:type', content: 'website' }],
      ['meta', { property: 'og:locale', content: 'en' }],
      ['meta', { property: 'og:site_name', content: 'Code with PHP' }]
    ],
    themeConfig: {
      nav: [
        { text: 'Home', link: '/' },
        {
          text: 'Series',
          items: [
            { text: 'PHP Basics', link: '/series/php-basics/' },
            { text: 'AI/ML for PHP Developers', link: '/series/ai-ml-php-developers/' },
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
        provider: 'local'
      }
    },
    markdown: {
      lineNumbers: true,
      theme: {
        light: 'github-light',
        dark: 'github-dark'
      }
    },
    mermaid: {
      theme: 'default'
    }
  })
)


