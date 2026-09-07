import { z } from "zod";

/**
 * Public API DTOs. These describe what the InsuraLab REST API returns — not the
 * Laravel models behind it. See docs/API.md.
 */

export const referenceStatusSchema = z.enum(["current", "historical", "needs_review"]);
export type ReferenceStatus = z.infer<typeof referenceStatusSchema>;

export const contentLevelSchema = z.enum(["beginner", "intermediate", "advanced"]);
export type ContentLevel = z.infer<typeof contentLevelSchema>;

export const providerSummarySchema = z.object({
  id: z.number(),
  name: z.string(),
  slug: z.string(),
  country_of_origin: z.string().nullable(),
  last_reviewed_at: z.string().nullable(),
  products_count: z.number().optional(),
  articles_count: z.number().optional(),
});
export type ProviderSummary = z.infer<typeof providerSummarySchema>;

export const providerMetadataSchema = z.object({
  entities: z.array(z.object({ name: z.string(), focus: z.string().optional() })).optional(),
  business_areas: z.array(z.string()).optional(),
  distribution: z.string().optional(),
});

export const categorySchema = z.object({
  id: z.number(),
  name: z.string(),
  slug: z.string(),
  description: z.string().nullable(),
  sort_order: z.number().nullable(),
  articles_count: z.number().optional(),
});
export type Category = z.infer<typeof categorySchema>;

export const protectionCategorySchema = z.object({
  id: z.number(),
  key: z.string(),
  name: z.string(),
  slug: z.string(),
  short_description: z.string().nullable(),
  description: z.string().nullable(),
  icon: z.string().nullable(),
  sort_order: z.number().nullable(),
});
export type ProtectionCategory = z.infer<typeof protectionCategorySchema>;

const articleCoreSchema = z.object({
  id: z.number(),
  title: z.string(),
  slug: z.string(),
  excerpt: z.string().nullable(),
  level: contentLevelSchema.nullable(),
  estimated_reading_minutes: z.number(),
  is_featured: z.boolean(),
  knowledge_type: z.enum(["general", "provider_specific"]),
  category: z.object({ id: z.number(), name: z.string(), slug: z.string() }).nullable(),
  provider: providerSummarySchema.nullable(),
  published_at: z.string().nullable(),
});

/** List-endpoint shape — carries the flat trust fields. */
export const articleSummarySchema = articleCoreSchema.extend({
  reference_status: referenceStatusSchema.nullable(),
  last_reviewed_at: z.string().nullable(),
});
export type ArticleSummary = z.infer<typeof articleSummarySchema>;

export const glossaryTermSummarySchema = z.object({
  id: z.number(),
  term: z.string(),
  slug: z.string(),
  short_definition: z.string(),
  category: z.string(),
  letter: z.string(),
  is_provider_specific: z.boolean(),
  reference_status: referenceStatusSchema.nullable(),
  articles_count: z.number().optional(),
});
export type GlossaryTermSummary = z.infer<typeof glossaryTermSummarySchema>;

export const productSummarySchema = z.object({
  id: z.number(),
  name: z.string(),
  slug: z.string(),
  short_description: z.string().nullable(),
  insurance_scheme: z.string().nullable(),
  product_structure: z.string().nullable(),
  provider: providerSummarySchema.nullable(),
  product_category: z
    .object({ id: z.number(), name: z.string(), slug: z.string(), kind: z.string() })
    .nullable(),
  protection_categories: z.array(protectionCategorySchema).optional(),
  reference_status: referenceStatusSchema.nullable(),
  reference_period: z.string().nullable(),
  last_reviewed_at: z.string().nullable(),
});
export type ProductSummary = z.infer<typeof productSummarySchema>;

export const sourceSchema = z.object({
  id: z.number(),
  title: z.string(),
  publisher: z.string().nullable(),
  url: z.string().nullable(),
  published_at: z.string().nullable(),
  accessed_at: z.string().nullable(),
  reference_period: z.string().nullable(),
  notes: z.string().nullable(),
  note: z.string().nullish(),
});
export type Source = z.infer<typeof sourceSchema>;

const glossaryRefSchema = z.object({
  term: z.string(),
  slug: z.string(),
  short_definition: z.string(),
});

export const articleTrustSchema = z.object({
  is_provider_specific: z.boolean(),
  reference_status: referenceStatusSchema.nullable(),
  reference_period: z.string().nullable(),
  last_reviewed_at: z.string().nullable(),
});

/** Detail-endpoint shape — trust lives in a nested object, not flat fields. */
export const articleDetailSchema = articleCoreSchema.extend({
  body: z.string(),
  key_takeaways: z.array(z.string()),
  tags: z.array(z.object({ name: z.string(), slug: z.string() })),
  glossary_terms: z.array(glossaryRefSchema).optional().default([]),
  related: z.array(articleSummarySchema).optional().default([]),
  trust: articleTrustSchema,
  sources: z.array(sourceSchema),
});
export type ArticleDetail = z.infer<typeof articleDetailSchema>;

export const glossaryTermDetailSchema = z.object({
  id: z.number(),
  term: z.string(),
  slug: z.string(),
  short_definition: z.string(),
  definition: z.string(),
  category: z.string(),
  letter: z.string(),
  provider: providerSummarySchema.nullable(),
  trust: z.object({
    is_provider_specific: z.boolean(),
    reference_status: referenceStatusSchema.nullable(),
    last_reviewed_at: z.string().nullable(),
  }),
  related_terms: z.array(glossaryRefSchema).optional().default([]),
  related_articles: z.array(articleSummarySchema).optional().default([]),
  sources: z.array(sourceSchema),
});
export type GlossaryTermDetail = z.infer<typeof glossaryTermDetailSchema>;

export const learningPathSummarySchema = z.object({
  id: z.number(),
  title: z.string(),
  slug: z.string(),
  description: z.string().nullable(),
  level: contentLevelSchema.nullable(),
  estimated_duration_minutes: z.number().nullable(),
  lessons_count: z.number().optional(),
  quizzes_count: z.number().optional(),
});
export type LearningPathSummary = z.infer<typeof learningPathSummarySchema>;

export const quizSummarySchema = z.object({
  id: z.number(),
  title: z.string(),
  slug: z.string(),
  description: z.string().nullable(),
  level: contentLevelSchema.nullable(),
  estimated_duration_minutes: z.number().nullable(),
  category: z.object({ name: z.string(), slug: z.string() }).nullable(),
  questions_count: z.number().optional(),
  related_learning_paths: z.array(z.object({ title: z.string(), slug: z.string() })).default([]),
});
export type QuizSummary = z.infer<typeof quizSummarySchema>;

export const learningPathDetailSchema = learningPathSummarySchema.extend({
  items: z.array(
    z.object({
      position: z.number(),
      type: z.enum(["article", "quiz"]),
      note: z.string().nullable(),
      article: articleSummarySchema.nullable(),
      quiz: quizSummarySchema.nullable(),
    }),
  ),
});
export type LearningPathDetail = z.infer<typeof learningPathDetailSchema>;

export const quizDetailSchema = quizSummarySchema.extend({
  questions: z.array(
    z.object({
      id: z.number(),
      key: z.string(),
      prompt: z.string(),
      type: z.literal("single"),
      options: z.array(z.object({ id: z.number(), key: z.string(), label: z.string() })).min(2),
    }),
  ),
});
export type QuizDetail = z.infer<typeof quizDetailSchema>;

const quizLearningTopicSchema = z.object({
  title: z.string(),
  slug: z.string(),
  excerpt: z.string().nullable(),
  url: z.string(),
});

export const quizResultSchema = z.object({
  quiz: z.object({ slug: z.string(), title: z.string() }),
  score: z.object({ correct: z.number(), total: z.number(), percentage: z.number() }),
  results: z.array(
    z.object({
      question: z.string(),
      prompt: z.string(),
      correct: z.boolean(),
      selected_option: z.string(),
      selected_option_label: z.string(),
      correct_option: z.string(),
      correct_option_label: z.string(),
      explanation: z.string(),
      concept_explanation: z.string().nullable(),
      related_lessons: z.array(quizLearningTopicSchema),
    }),
  ),
  review_topics: z.array(quizLearningTopicSchema),
});
export type QuizResult = z.infer<typeof quizResultSchema>;

export const providerSchema = providerSummarySchema.extend({
  description: z.string().nullable(),
  presence_in_indonesia: z.string().nullable(),
  website_url: z.string().nullable(),
  status: z.string().nullable(),
  metadata: providerMetadataSchema.catch({}),
  counts: z.object({ products: z.number(), articles: z.number() }),
  articles: z.array(articleSummarySchema).optional().default([]),
  sources: z.array(sourceSchema).optional().default([]),
});
export type Provider = z.infer<typeof providerSchema>;

const glossaryRefListSchema = z.array(glossaryRefSchema).optional().default([]);

export const productDetailSchema = z.object({
  id: z.number(),
  name: z.string(),
  slug: z.string(),
  short_description: z.string().nullable(),
  description: z.string().nullable(),
  insurance_scheme: z.string().nullable(),
  product_structure: z.string().nullable(),
  provider: providerSummarySchema.nullable(),
  product_category: z
    .object({
      id: z.number(),
      name: z.string(),
      slug: z.string(),
      kind: z.string(),
      description: z.string().nullable(),
    })
    .nullable(),
  protection_categories: z.array(
    protectionCategorySchema.extend({ is_primary: z.boolean().optional() }),
  ),
  metadata: z.record(z.string(), z.unknown()).catch({}),
  related_articles: z.array(articleSummarySchema).optional().default([]),
  related_terms: glossaryRefListSchema,
  trust: z.object({
    reference_status: referenceStatusSchema.nullable(),
    reference_period: z.string().nullable(),
    last_reviewed_at: z.string().nullable(),
  }),
  sources: z.array(sourceSchema),
});
export type ProductDetail = z.infer<typeof productDetailSchema>;

export const healthSchema = z.object({
  status: z.string(),
  service: z.string(),
  api_version: z.string(),
  time: z.string(),
});
export type Health = z.infer<typeof healthSchema>;

export const assessmentOptionSchema = z.object({
  id: z.number(),
  key: z.string(),
  label: z.string(),
});

export const assessmentQuestionSchema = z.object({
  id: z.number(),
  key: z.string(),
  prompt: z.string(),
  help_text: z.string().nullable(),
  type: z.literal("single"),
  is_required: z.boolean(),
  condition: z.object({ question: z.string(), options: z.array(z.string()) }).nullable(),
  options: z.array(assessmentOptionSchema).min(2),
});

export const assessmentDefinitionSchema = z.object({
  id: z.number(),
  title: z.string(),
  slug: z.string(),
  methodology_version: z.string(),
  description: z.string().nullable(),
  intro: z.string().nullable(),
  disclaimer: z.string().nullable(),
  questions: z.array(assessmentQuestionSchema),
});
export type AssessmentDefinition = z.infer<typeof assessmentDefinitionSchema>;

export const assessmentLearningTopicSchema = z.object({
  title: z.string(),
  slug: z.string(),
  excerpt: z.string().nullable(),
  url: z.string(),
});

export const assessmentResultSchema = z.object({
  assessment: z.string(),
  methodology_version: z.string(),
  dimensions: z.array(
    z.object({
      key: z.string(),
      name: z.string(),
      relevance: z.enum(["foundational", "worth_exploring", "highly_relevant"]),
      relevance_label: z.string(),
      description: z.string(),
      reasons: z.array(z.string()).min(1),
      learning_topics: z.array(assessmentLearningTopicSchema),
      learning_path: z
        .object({ title: z.string(), slug: z.string(), url: z.string() })
        .nullable()
        .optional(),
    }),
  ),
  additional_learning: z
    .object({
      title: z.string(),
      description: z.string(),
      topics: z.array(assessmentLearningTopicSchema),
    })
    .optional(),
  disclaimer: z.string().nullable(),
});
export type AssessmentResult = z.infer<typeof assessmentResultSchema>;
