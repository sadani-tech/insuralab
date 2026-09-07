import {
  apiGet,
  apiPost,
  collectionEnvelope,
  paginatedEnvelope,
  singleEnvelope,
  type Paginated,
} from "./client";
import {
  articleDetailSchema,
  articleSummarySchema,
  categorySchema,
  glossaryTermDetailSchema,
  glossaryTermSummarySchema,
  healthSchema,
  learningPathDetailSchema,
  learningPathSummarySchema,
  quizDetailSchema,
  quizResultSchema,
  quizSummarySchema,
  productDetailSchema,
  productSummarySchema,
  protectionCategorySchema,
  providerSchema,
  providerSummarySchema,
  type ArticleDetail,
  type ArticleSummary,
  type Category,
  type GlossaryTermDetail,
  type GlossaryTermSummary,
  type Health,
  type LearningPathDetail,
  type LearningPathSummary,
  type QuizDetail,
  type QuizResult,
  type QuizSummary,
  type ProductDetail,
  type ProductSummary,
  type ProtectionCategory,
  type Provider,
  type ProviderSummary,
  assessmentDefinitionSchema,
  assessmentResultSchema,
  type AssessmentDefinition,
  type AssessmentResult,
} from "./schemas";

const unwrap = <T>(value: { data: T }) => value.data;

export function getHealth(signal?: AbortSignal): Promise<Health> {
  return apiGet("health", healthSchema, { revalidate: false, signal });
}

export async function getFeaturedArticles(limit = 3): Promise<ArticleSummary[]> {
  const res = await apiGet("articles", paginatedEnvelope(articleSummarySchema), {
    query: { featured: true, per_page: limit },
  });
  return res.data;
}

export async function getArticles(params?: {
  page?: number;
  per_page?: number;
  category?: string;
  provider?: string;
  level?: string;
  knowledge_type?: "general" | "provider_specific";
}): Promise<Paginated<ArticleSummary>> {
  const res = await apiGet("articles", paginatedEnvelope(articleSummarySchema), { query: params });
  return { data: res.data, meta: res.meta };
}

export function getArticle(slug: string): Promise<ArticleDetail> {
  return apiGet(`articles/${slug}`, singleEnvelope(articleDetailSchema)).then(unwrap);
}

export async function getCategories(): Promise<Category[]> {
  return unwrap(await apiGet("categories", collectionEnvelope(categorySchema)));
}

export async function getLearningPaths(): Promise<LearningPathSummary[]> {
  return unwrap(await apiGet("learning-paths", collectionEnvelope(learningPathSummarySchema)));
}

export function getLearningPath(slug: string): Promise<LearningPathDetail> {
  return apiGet(`learning-paths/${slug}`, singleEnvelope(learningPathDetailSchema)).then(unwrap);
}

export async function getQuizzes(): Promise<QuizSummary[]> {
  return unwrap(await apiGet("quizzes", collectionEnvelope(quizSummarySchema)));
}

export function getQuiz(slug: string): Promise<QuizDetail> {
  return apiGet(`quizzes/${slug}`, singleEnvelope(quizDetailSchema)).then(unwrap);
}

export function submitQuiz(
  slug: string,
  answers: { question: string; option: string }[],
): Promise<QuizResult> {
  return apiPost(`quizzes/${slug}/submit`, { answers }, singleEnvelope(quizResultSchema)).then(
    unwrap,
  );
}

export async function getProtectionCategories(): Promise<ProtectionCategory[]> {
  return unwrap(
    await apiGet("protection-categories", collectionEnvelope(protectionCategorySchema)),
  );
}

export async function getGlossary(params?: {
  page?: number;
  per_page?: number;
  letter?: string;
  category?: string;
  q?: string;
}): Promise<Paginated<GlossaryTermSummary>> {
  const res = await apiGet("glossary", paginatedEnvelope(glossaryTermSummarySchema), {
    query: params,
  });
  return { data: res.data, meta: res.meta };
}

export function getGlossaryTerm(slug: string): Promise<GlossaryTermDetail> {
  return apiGet(`glossary/${slug}`, singleEnvelope(glossaryTermDetailSchema)).then(unwrap);
}

export async function getProviders(): Promise<Paginated<ProviderSummary>> {
  const res = await apiGet("providers", paginatedEnvelope(providerSummarySchema));
  return { data: res.data, meta: res.meta };
}

export function getProvider(slug: string): Promise<Provider> {
  return apiGet(`providers/${slug}`, singleEnvelope(providerSchema)).then(unwrap);
}

export function getProduct(slug: string): Promise<ProductDetail> {
  return apiGet(`products/${slug}`, singleEnvelope(productDetailSchema)).then(unwrap);
}

export async function getProducts(params?: {
  page?: number;
  per_page?: number;
  provider?: string;
  category?: string;
  protection?: string;
  insurance_scheme?: string;
  product_structure?: string;
  reference_status?: string;
}): Promise<Paginated<ProductSummary>> {
  const res = await apiGet("products", paginatedEnvelope(productSummarySchema), { query: params });
  return { data: res.data, meta: res.meta };
}

export function getProtectionNeedsAssessment(): Promise<AssessmentDefinition> {
  return apiGet("assessments/protection-needs", singleEnvelope(assessmentDefinitionSchema), {
    revalidate: 60,
  }).then(unwrap);
}

export function evaluateProtectionNeedsAssessment(
  methodologyVersion: string,
  answers: { question: string; option: string }[],
): Promise<AssessmentResult> {
  return apiPost(
    "assessments/protection-needs/evaluate",
    { methodology_version: methodologyVersion, answers },
    singleEnvelope(assessmentResultSchema),
  ).then(unwrap);
}
