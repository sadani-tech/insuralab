import { afterEach, describe, expect, it, vi } from "vitest";
import { z } from "zod";
import { apiGet, ApiError, paginatedEnvelope } from "./client";

const itemSchema = z.object({ id: z.number(), name: z.string() });

function mockFetchOnce(body: unknown, init: { ok?: boolean; status?: number } = {}) {
  const { ok = true, status = 200 } = init;
  vi.stubGlobal(
    "fetch",
    vi.fn(async () => ({
      ok,
      status,
      statusText: ok ? "OK" : "Error",
      text: async () => JSON.stringify(body),
    })),
  );
}

describe("apiGet", () => {
  afterEach(() => vi.unstubAllGlobals());

  it("parses a valid paginated response at the boundary", async () => {
    mockFetchOnce({
      data: [{ id: 1, name: "Risk" }],
      links: { first: "a", last: "a", prev: null, next: null },
      meta: {
        current_page: 1,
        from: 1,
        last_page: 1,
        path: "x",
        per_page: 15,
        to: 1,
        total: 1,
      },
    });

    const res = await apiGet("articles", paginatedEnvelope(itemSchema));
    expect(res.data).toEqual([{ id: 1, name: "Risk" }]);
    expect(res.meta.total).toBe(1);
  });

  it("throws an ApiError with the server message on a non-2xx response", async () => {
    mockFetchOnce({ message: "Resource not found." }, { ok: false, status: 404 });

    await expect(apiGet("articles/x", itemSchema)).rejects.toMatchObject({
      name: "ApiError",
      status: 404,
      message: "Resource not found.",
    });
  });

  it("throws when the payload shape does not match the schema", async () => {
    mockFetchOnce({ data: [{ id: "not-a-number", name: "Risk" }] });

    await expect(apiGet("articles", paginatedEnvelope(itemSchema))).rejects.toBeInstanceOf(
      ApiError,
    );
  });

  it("wraps a network failure as an ApiError with status 0", async () => {
    vi.stubGlobal(
      "fetch",
      vi.fn(async () => {
        throw new TypeError("connection refused");
      }),
    );

    await expect(apiGet("health", itemSchema)).rejects.toMatchObject({ status: 0 });
  });
});
