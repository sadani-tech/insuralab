import { LibraryPage } from "@/components/knowledge/library-page";
export const dynamic = "force-dynamic";
export const metadata = { title: "Sumber" };
export default function Page() {
  return <LibraryPage section="sources" />;
}
