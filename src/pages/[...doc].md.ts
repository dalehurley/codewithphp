import type { APIRoute, GetStaticPaths } from 'astro';
import type { CollectionEntry } from 'astro:content';
import { getAgentDocs, renderAgentMarkdown } from '../lib/agent-docs';

export const getStaticPaths: GetStaticPaths = async () =>
  (await getAgentDocs()).map(entry => ({
    params: { doc: entry.slug },
    props: { entry }
  }));

export const GET: APIRoute = ({ props, site }) =>
  new Response(renderAgentMarkdown(props.entry as CollectionEntry<'docs'>, site!), {
    headers: { 'Content-Type': 'text/markdown; charset=utf-8' }
  });
