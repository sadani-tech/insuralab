# ADR 0001 — Monorepo for frontend and backend

- Status: Accepted
- Date: 2026-08-31

## Context

InsuraLab has two deployables: a Next.js public web app and a Laravel domain/API/CMS backend. They evolve together (a schema change often implies an API and a frontend change) and are built by a single maintainer / small team. We need shared documentation (`docs/`, `CLAUDE.md`, ADRs) to be the single source of truth for both.

## Decision

Use a **single Git repository** with top-level `frontend/` and `backend/` directories, shared `docs/`, a root `docker-compose.yml`, and a root `Makefile` for developer-experience commands. Each app keeps its own toolchain, lockfile, and CI-relevant scripts.

We do **not** adopt a JS monorepo tool (Nx, Turborepo, pnpm workspaces). The two stacks are PHP and TypeScript; there is no shared JS package to hoist. A directory split plus a Makefile is sufficient.

## Consequences

- One clone, one issue tracker, atomic cross-cutting commits, one place for project memory.
- CI must run two independent pipelines (PHP, Node) scoped by path.
- Deployment publishes two artifacts from one repo.
- If a third deployable ever appears, revisit; the directory-split approach scales to a handful of apps before a monorepo tool earns its keep.
