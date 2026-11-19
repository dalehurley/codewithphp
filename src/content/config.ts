import { defineCollection } from 'astro:content';
import { z } from 'astro/zod';
import { docsSchema } from '@astrojs/starlight/schema';

const editorialDate = z.union([z.string().date(), z.string().datetime({ offset: true }), z.date()])
  .transform((value) => value instanceof Date ? value.toISOString().slice(0, 10) : value)
  .optional();

export const collections = {
  docs: defineCollection({
    schema: docsSchema({
      extend: z.object({
        seoTitle: z.string().trim().min(1).optional(),
        chapter: z.number().optional(),
        difficulty: z.string().optional(),
        teaches: z.array(z.string()).optional(),
        author: z.string().trim().min(1).optional(),
        authorUrl: z.string().url().optional(),
        datePublished: editorialDate,
        dateModified: editorialDate,
        socialImage: z.string().regex(/^\/social\/[\w.-]+\.(?:jpe?g|png|webp)$/i).optional(),
        noindex: z.boolean().optional(),
      }),
    }),
  }),
};
