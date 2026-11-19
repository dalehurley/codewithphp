const schema = 'https://schema.org';

interface SiteMetadata {
  name: string;
  url: string;
  language: string;
}

interface PageMetadata {
  title: string;
  url: string;
  description: string;
  image: string;
  author?: string;
  authorUrl?: string;
  datePublished?: string;
  dateModified?: string;
  difficulty?: string;
  teaches?: string[];
}

interface Breadcrumb {
  name: string;
  url: string;
}

function organization(site: SiteMetadata) {
  return {
    '@type': 'Organization',
    '@id': `${site.url}#organization`,
    name: site.name,
    url: site.url,
  };
}

export function generateHomepageSchemas(site: SiteMetadata, description: string) {
  return [
    {
      '@context': schema,
      '@type': 'WebSite',
      '@id': `${site.url}#website`,
      name: site.name,
      url: site.url,
      description,
      inLanguage: site.language,
      publisher: organization(site),
    },
    {
      '@context': schema,
      ...organization(site),
      logo: new URL('/images/brand/pinup-mark.png', site.url).href,
      sameAs: ['https://github.com/dalehurley/codewithphp'],
    },
  ];
}

export function generateBreadcrumbSchema(items: Breadcrumb[]) {
  return {
    '@context': schema,
    '@type': 'BreadcrumbList',
    itemListElement: items.map(({ name, url }, index) => ({
      '@type': 'ListItem',
      position: index + 1,
      name,
      item: url,
    })),
  };
}

export function generateCourseSchema(site: SiteMetadata, page: PageMetadata) {
  return {
    '@context': schema,
    '@type': 'Course',
    '@id': `${page.url}#course`,
    name: page.title,
    description: page.description,
    url: page.url,
    image: page.image,
    provider: organization(site),
    inLanguage: site.language,
    educationalLevel: page.difficulty,
    teaches: page.teaches,
    isAccessibleForFree: true,
  };
}

export function generateCourseListSchema(urls: string[]) {
  return {
    '@context': schema,
    '@type': 'ItemList',
    itemListElement: urls.map((url, index) => ({
      '@type': 'ListItem',
      position: index + 1,
      url,
    })),
  };
}

export function generateArticleSchema(site: SiteMetadata, page: PageMetadata, hasArticleImage: boolean) {
  return {
    '@context': schema,
    '@type': 'Article',
    '@id': `${page.url}#article`,
    headline: page.title,
    description: page.description,
    image: hasArticleImage ? [page.image] : undefined,
    mainEntityOfPage: page.url,
    datePublished: page.datePublished,
    dateModified: page.dateModified,
    author: page.author === site.name
      ? organization(site)
      : page.author ? { '@type': 'Person', name: page.author, url: page.authorUrl } : undefined,
    publisher: organization(site),
    inLanguage: site.language,
  };
}
