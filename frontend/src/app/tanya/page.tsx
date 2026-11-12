import { LibraryPage } from "@/components/knowledge/library-page";
export const dynamic = "force-dynamic";
export const metadata = { title: "Tanya" };
export default function Page() {
  return <LibraryPage section="answers" />;
}
