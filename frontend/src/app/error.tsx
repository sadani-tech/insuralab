"use client";

import { useEffect } from "react";
import { Container } from "@/components/ui/container";
import { Button } from "@/components/ui/button";

export default function Error({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    // Phase 12/13 wires real observability; for now just log to the console.
    console.error(error);
  }, [error]);

  return (
    <Container className="flex flex-1 flex-col items-center justify-center gap-4 py-24 text-center">
      <p className="text-accent font-mono text-xs font-semibold tracking-[0.14em] uppercase">
        Something went wrong
      </p>
      <h1 className="text-2xl font-semibold sm:text-3xl">This page hit an error</h1>
      <p className="text-muted-foreground max-w-md">
        It&apos;s not you. Try again in a moment — if it keeps happening, the content service may be
        unavailable.
      </p>
      <Button className="mt-2" onClick={() => reset()}>
        Try again
      </Button>
    </Container>
  );
}
