# InsuraLab

> **Understand insurance before you buy it.**
>
> Asuransi tidak harus rumit. Pahami risiko, kenali proteksi, dan pelajari cara kerjanya sebelum mengambil keputusan.

InsuraLab is an independent, **provider-agnostic** insurance education platform. It helps people understand financial risk and how insurance protection works — without pushing them toward a particular product.

> _We don't tell people what to buy. We help people understand what they are protecting._

Allianz is the **first provider case study**, never the identity of the platform.

---

## Status

Foundation execution — **Phases 0–2 complete**. No public site, public API, assessment engine, or 3D visualization yet (see `docs/ROADMAP.md`). The frontend is a minimal development shell.

## Repository layout

| Path | What |
|---|---|
| `frontend/` | Next.js 16 (App Router, TypeScript strict, Tailwind v4) — the public web experience. Dev shell only for now. |
| `backend/` | Laravel 10 + Filament 3 — domain logic, the CMS at `/admin`, and (later) the REST API. |
| `docs/` | Project memory: vision, architecture, domain knowledge, content guide, roadmap, implementation plan. |
| `docs/decisions/` | Architecture Decision Records. |
| `docker-compose.yml` | Local PostgreSQL (self-contained option). |
| `Makefile` / root `package.json` | Developer commands (use `npm run …` on Windows). |
| `CLAUDE.md` | Project constitution — **read first**. |

## Prerequisites

- **PHP 8.1+** with `intl`, `gd`, `pdo_pgsql` extensions, and **Composer 2**
- **Node.js 20+** and npm
- **Docker** (for PostgreSQL) — or any reachable PostgreSQL 15+
- See `docs/decisions/0005-laravel-10-php-81-pin.md` for the PHP-version trade-off.

## Quick start

```bash
# 1. Database — EITHER reuse an existing local PostgreSQL container on :5433
#    (create role `insuralab` / db `insuralab` + `insuralab_testing`),
#    OR start the self-contained one on :5434:
docker compose --profile full up -d          # then set backend/.env DB_PORT=5434

# 2. Backend
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed                    # schema + seed content + admin user
php artisan serve                            # http://127.0.0.1:8000  (admin: /admin)

# 3. Frontend (new terminal)
cd frontend
cp .env.example .env.local
npm install
npm run dev                                  # http://localhost:3000
```

Or from the repo root: `npm run install:all` then `npm run fresh` then `npm start`.

**Default admin login** (from `backend/.env`): `admin@insuralab.test` / `password`.

## Common commands (root)

| Command | Does |
|---|---|
| `npm run install:all` | Install backend + frontend deps |
| `npm start` | Run API (:8000) + web (:3000) together |
| `npm run stop` | Stop dev servers on :3000 / :8000 |
| `npm run migrate` / `npm run seed` / `npm run fresh` | Database |
| `npm test` | Backend test suite (Pest) |
| `npm run lint` | Lint the frontend |
| `npm run format` / `npm run format:check` | Pint + Prettier |
| `npm run build` | Production build of the frontend |

Equivalent `make` targets exist for Unix/CI (`make help`).

## Documentation

Start with **`CLAUDE.md`**, then `docs/PRODUCT_VISION.md`, `docs/ARCHITECTURE.md`, `docs/DOMAIN_KNOWLEDGE.md`, `docs/CONTENT_GUIDE.md`, `docs/ROADMAP.md`, and `docs/decisions/`.

## License

Not yet licensed. All rights reserved by the author pending a license decision.
