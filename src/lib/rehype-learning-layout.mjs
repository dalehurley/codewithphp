function element(tagName, children, properties = {}) {
  return { type: 'element', tagName, properties, children };
}

function textContent(node) {
  return node.value ?? node.children?.map(textContent).join('') ?? '';
}

function normalizedTitle(title) {
  return title
    .replace(/<[^>]*>/g, '')
    .replace(/^(?:chapter\s+)?\d+[a-z]?\s*[:–—-]\s*/i, '')
    .replace(/[‘’]/g, "'")
    .replace(/[:–—-]/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
    .toLowerCase();
}

function hasDifficultyBadge(node) {
  return node.properties?.className?.includes('difficulty-badge') ||
    (node.type === 'raw' && /\bclass=["'][^"']*\bdifficulty-badge\b/.test(node.value)) ||
    node.children?.some(hasDifficultyBadge);
}

function isRepeatedTitle(heading, title) {
  if (typeof title !== 'string') return false;

  const headingTitle = normalizedTitle(textContent(heading));
  const pageTitle = normalizedTitle(title);
  return headingTitle === pageTitle ||
    (hasDifficultyBadge(heading) && headingTitle.replace(/\s+(beginner|intermediate|advanced|expert)$/, '') === pageTitle);
}

function rehypeLearningLayout() {
  return (tree, file) => {
    if (!file.path?.includes('/content/docs/series/')) return;

    const firstIndex = tree.children.findIndex((node) => node.type === 'element');
    const first = tree.children[firstIndex];
    const image = first?.children?.[0];
    const imagePath = image?.properties?.src;
    if (first?.tagName === 'p' && first.children.length === 1 && image?.tagName === 'img' &&
        typeof imagePath === 'string' && /^\/images\/[^?#]+-hero-full\.(?:webp|png|jpe?g)$/i.test(imagePath)) {
      const label = file.path.includes('/chapters/') ? 'Lesson artwork' : 'Course artwork';
      const summary = element('summary', [{ type: 'text', value: label }]);
      tree.children[firstIndex] = element('details', [summary, first], { className: ['learning-artwork'] });
    }

    const title = file.data.astro?.frontmatter?.title;
    let firstHeading = true;

    function visit(parent) {
      for (const node of parent.children ?? []) {
        if (node.tagName !== 'h1') {
          visit(node);
          continue;
        }

        if (firstHeading && isRepeatedTitle(node, title)) {
          // Preserve the original fragment target without adding a heading to the TOC.
          node.tagName = 'div';
          node.properties.className = [...(node.properties.className ?? []), 'learning-repeated-title'];
        } else {
          node.tagName = 'h2';
          if (parent.properties?.className?.includes('sl-heading-wrapper')) {
            parent.properties.className = parent.properties.className.map((className) =>
              className === 'level-h1' ? 'level-h2' : className);
          }
        }
        firstHeading = false;
      }
    }

    visit(tree);
  };
}

export default function learningLayout() {
  return {
    name: 'learning-layout',
    hooks: {
      'astro:config:setup': ({ updateConfig }) => {
        // Run after Starlight has assigned the existing heading IDs.
        updateConfig({ markdown: { rehypePlugins: [rehypeLearningLayout] } });
      },
    },
  };
}
