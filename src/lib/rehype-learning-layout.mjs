function element(tagName, children, properties = {}) {
  return { type: 'element', tagName, properties, children };
}

function textContent(node) {
  return node.value ?? node.children?.map(textContent).join('') ?? '';
}

function normalizedTitle(title) {
  return title.replace(/^(?:chapter\s+)?\d+[a-z]?\s*[:–—-]\s*/i, '').trim().toLowerCase();
}

export default function rehypeLearningLayout() {
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

    const heading = tree.children.find((node) => node.tagName === 'h1');
    const title = file.data.astro?.frontmatter?.title;
    if (heading && typeof title === 'string' && normalizedTitle(textContent(heading)) === normalizedTitle(title)) {
      // Keep the original heading ID so existing inbound links can still reveal it.
      heading.properties.className = [...(heading.properties.className ?? []), 'learning-repeated-title'];
    }
  };
}
