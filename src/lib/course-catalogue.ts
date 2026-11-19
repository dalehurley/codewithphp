interface CourseCover {
  title: string;
  description: string;
  color: 'lime' | 'coral' | 'cream';
  artwork: 'braces' | 'windows' | 'halftone' | 'symbol';
  mark?: string;
}

export const courseCovers = {
  'php-basics': {
    title: 'PHP Basics',
    description: 'Build your first router and blog.',
    color: 'lime',
    artwork: 'braces',
    mark: '{ }',
  },
  'build-crm-laravel-12': {
    title: 'Build a CRM',
    description: 'Build a complete Laravel app.',
    color: 'coral',
    artwork: 'windows',
    mark: undefined,
  },
  'php-8-6': {
    title: 'What’s new in PHP 8.6',
    description: 'Try language changes and upgrade examples.',
    color: 'cream',
    artwork: 'halftone',
    mark: '8.6',
  },
  'php-typescript-developers': {
    title: 'From TypeScript to PHP',
    description: 'Take what you know about types into PHP.',
    color: 'lime',
    artwork: 'symbol',
    mark: '=>',
  },
  'php-for-java-developers': {
    title: 'From Java to PHP',
    description: 'Compare classes, interfaces, and application patterns.',
    color: 'coral',
    artwork: 'symbol',
    mark: 'new',
  },
  'python-developers-love-php-laravel': {
    title: 'From Python to Laravel',
    description: 'Bring your Python skills into PHP and Laravel.',
    color: 'cream',
    artwork: 'symbol',
    mark: '__',
  },
  'rails-developers-love-laravel': {
    title: 'From Rails to Laravel',
    description: 'Translate familiar Rails ideas into Laravel conventions.',
    color: 'lime',
    artwork: 'symbol',
    mark: '::',
  },
  'php-algorithms': {
    title: 'PHP Algorithms',
    description: 'Make your code faster with the right data structures.',
    color: 'coral',
    artwork: 'symbol',
    mark: 'O(n)',
  },
  'ai-ml-php-developers': {
    title: 'AI/ML for PHP Developers',
    description: 'Build practical machine learning features in PHP.',
    color: 'lime',
    artwork: 'symbol',
    mark: 'ML',
  },
  'data-science-php-developers': {
    title: 'Data Science for PHP Developers',
    description: 'Turn datasets into useful analyses and visualisations.',
    color: 'cream',
    artwork: 'symbol',
    mark: '[ ]',
  },
  'claude-php-developers': {
    title: 'Claude for PHP Developers',
    description: 'Build chatbots and applications with the Claude API.',
    color: 'coral',
    artwork: 'symbol',
    mark: 'AI',
  },
  'agentic-ai-php-developers': {
    title: 'Agentic AI for PHP Developers',
    description: 'Build agents that plan, use tools, and keep context.',
    color: 'lime',
    artwork: 'symbol',
    mark: 'fn',
  },
} satisfies Record<string, CourseCover>;

export type CourseSlug = keyof typeof courseCovers;

export const featuredCourses: CourseSlug[] = [
  'php-basics', 'build-crm-laravel-12', 'php-8-6', 'php-typescript-developers', 'php-algorithms',
];

export const catalogueTracks: { id: string; title: string; courses: CourseSlug[] }[] = [
  { id: 'start-here', title: 'Start here', courses: ['php-basics', 'php-8-6', 'build-crm-laravel-12'] },
  { id: 'switch-languages', title: 'Switch languages', courses: [
    'php-typescript-developers', 'php-for-java-developers', 'python-developers-love-php-laravel', 'rails-developers-love-laravel',
  ] },
  { id: 'go-deeper', title: 'Go deeper', courses: [
    'php-algorithms', 'ai-ml-php-developers', 'data-science-php-developers', 'claude-php-developers', 'agentic-ai-php-developers',
  ] },
];
