function hasMath(node) {
  return node.properties?.className?.includes('katex') || node.children?.some(hasMath);
}

export default function rehypeKatexStylesheet() {
  return tree => {
    if (!hasMath(tree)) return;
    tree.children.unshift({
      type: 'element',
      tagName: 'link',
      properties: {
        rel: ['stylesheet'],
        href: 'https://cdn.jsdelivr.net/npm/katex@0.16.27/dist/katex.min.css',
      },
      children: [],
    });
  };
}
