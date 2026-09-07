import { z, type ZodType } from "zod";
import { env } from "@/lib/env";

/** Base URL of the versioned API, e.g. http://localhost:8000/api/v1 */
export const API_V1 = `${env.apiBaseUrl}/v1`;

export class ApiError extends Error {
  constructor(
    message: string,
    readonly status: number,
    readonly url: string,
    readonly body?: unknown,
  ) {
    super(message);
    this.name = "ApiError";
  }
}

type RequestOptions = {
  /** Query params; `undefined` / `null` entries are dropped. */
  query?: Record<string, string | number | boolean | undefined | null>;
  /** Next.js fetch caching. Defaults to a short revalidate window. */
  revalidate?: number | false;
  signal?: AbortSignal;
};

function buildUrl(path: string, query?: RequestOptions["query"]): string {
  const url = new URL(`${API_V1}/${path.replace(/^\/+/, "")}`);
  if (query) {
    for (const [key, value] of Object.entries(query)) {
      if (value !== undefined && value !== null && value !== "") {
        url.searchParams.set(key, String(value));
      }
    }
  }
  return url.toString();
}

async function parseResponse<T>(res: Response, url: string, schema: ZodType<T>): Promise<T> {
  const raw = await res.text();
  let json: unknown = null;
  try {
    json = raw ? JSON.parse(raw) : null;
  } catch {
    throw new ApiError(`Invalid JSON response from ${url}`, res.status, url, raw);
  }

  if (!res.ok) {
    const message =
      (json && typeof json === "object" && "message" in json && typeof json.message === "string"
        ? json.message
        : null) ?? `API ${res.status} ${res.statusText}`;
    throw new ApiError(message, res.status, url, json);
  }

  const parsed = schema.safeParse(json);
  if (!parsed.success) {
    throw new ApiError(
      `Unexpected API response shape for ${url}: ${parsed.error.issues
        .map((i) => `${i.path.join(".")} ${i.message}`)
        .join("; ")}`,
      res.status,
      url,
      json,
    );
  }
  return parsed.data;
}

/**
 * Fetch JSON from the InsuraLab API and validate it at the boundary with a Zod
 * schema. The frontend defines its own DTO types (this module) rather than
 * mirroring Laravel models — the schema is the contract.
 */
export async function apiGet<T>(
  path: string,
  schema: ZodType<T>,
  options: RequestOptions = {},
): Promise<T> {
  const url = buildUrl(path, options.query);

  let res: Response;
  try {
    res = await fetch(url, {
      headers: { Accept: "application/json" },
      signal: options.signal,
      next: options.revalidate === false ? undefined : { revalidate: options.revalidate ?? 60 },
      cache: options.revalidate === false ? "no-store" : undefined,
    });
  } catch (cause) {
    throw new ApiError(`Network error requesting ${url}`, 0, url, cause);
  }

  return parseResponse(res, url, schema);
}

export async function apiPost<T>(path: string, body: unknown, schema: ZodType<T>): Promise<T> {
  const url = buildUrl(path);
  let res: Response;
  try {
    res = await fetch(url, {
      method: "POST",
      headers: { Accept: "application/json", "Content-Type": "application/json" },
      body: JSON.stringify(body),
      cache: "no-store",
    });
  } catch (cause) {
    throw new ApiError(`Network error requesting ${url}`, 0, url, cause);
  }
  return parseResponse(res, url, schema);
}

/** Wrapper envelopes returned by Laravel API resources. */
export const singleEnvelope = <T extends ZodType>(data: T) => z.object({ data });

export const paginationMeta = z.object({
  current_page: z.number(),
  from: z.number().nullable(),
  last_page: z.number(),
  path: z.string(),
  per_page: z.number(),
  to: z.number().nullable(),
  total: z.number(),
});

export const paginatedEnvelope = <T extends ZodType>(item: T) =>
  z.object({
    data: z.array(item),
    links: z.object({
      first: z.string().nullable(),
      last: z.string().nullable(),
      prev: z.string().nullable(),
      next: z.string().nullable(),
    }),
    meta: paginationMeta,
  });

export const collectionEnvelope = <T extends ZodType>(item: T) => z.object({ data: z.array(item) });

export type Paginated<T> = {
  data: T[];
  meta: z.infer<typeof paginationMeta>;
};
