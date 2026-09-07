/** Keyboard users can jump past the header straight to the main content. */
export function SkipLink() {
  return (
    <a
      href="#main-content"
      className="bg-primary text-primary-foreground sr-only z-50 rounded-md px-4 py-2 text-sm font-medium focus:not-sr-only focus:absolute focus:top-3 focus:left-3"
    >
      Skip to main content
    </a>
  );
}
