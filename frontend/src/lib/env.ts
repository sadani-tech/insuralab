/**
 * Typed access to public environment configuration.
 *
 * Only `NEXT_PUBLIC_*` variables are available in the browser bundle. Keep this
 * module dependency-free so it can be imported from anywhere (server or client).
 */

function required(name: string, value: string | undefined): string {
  if (!value || value.length === 0) {
    throw new Error(`Missing required environment variable: ${name}`);
  }
  return value;
}

export const env = {
  /** Base URL of the InsuraLab Laravel API, without a trailing slash. */
  apiBaseUrl: required("NEXT_PUBLIC_API_BASE_URL", process.env.NEXT_PUBLIC_API_BASE_URL).replace(
    /\/+$/,
    "",
  ),

  /** Public URL of this site. */
  siteUrl: (process.env.NEXT_PUBLIC_SITE_URL ?? "http://localhost:3000").replace(/\/+$/, ""),
} as const;
