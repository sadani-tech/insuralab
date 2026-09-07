import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { QuizRunner } from "@/components/quiz/quiz-runner";
import { Breadcrumb } from "@/components/ui/breadcrumb";
import { Container, PageHeader, Section } from "@/components/ui/container";
import { ApiError, getQuiz } from "@/lib/api";

export const revalidate = 300;

async function loadQuiz(slug: string) {
  try {
    return await getQuiz(slug);
  } catch (error) {
    if (error instanceof ApiError && error.status === 404) notFound();
    throw error;
  }
}

export async function generateMetadata({
  params,
}: {
  params: Promise<{ slug: string }>;
}): Promise<Metadata> {
  const { slug } = await params;
  try {
    const quiz = await getQuiz(slug);
    return { title: `Quiz ${quiz.title}`, description: quiz.description ?? undefined };
  } catch {
    return { title: "Quiz" };
  }
}

export default async function QuizDetailPage({
  params,
  searchParams,
}: {
  params: Promise<{ slug: string }>;
  searchParams: Promise<{ path?: string }>;
}) {
  const [{ slug }, query] = await Promise.all([params, searchParams]);
  const quiz = await loadQuiz(slug);
  return (
    <>
      <PageHeader
        eyebrow="Uji Pemahaman"
        title={quiz.title}
        lead="Pilih jawaban yang paling tepat. Penjelasan lengkap akan ditampilkan setelah jawaban diperiksa oleh server."
      >
        <Breadcrumb
          items={[
            { label: "Home", href: "/" },
            { label: "Quiz", href: "/quiz" },
            { label: quiz.title },
          ]}
        />
      </PageHeader>
      <Section>
        <Container>
          <QuizRunner quiz={quiz} pathSlug={query.path} />
        </Container>
      </Section>
    </>
  );
}
