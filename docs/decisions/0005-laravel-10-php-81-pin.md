# ADR 0005 — Pin Laravel 10 / Filament 3 to the PHP 8.1 host runtime

- Status: Accepted (with a known trade-off)
- Date: 2026-08-31

## Context

The original brief specifies "Laravel latest stable" and "PHP 8.3+". The development host runs **PHP 8.1.34**, and installing a newer PHP toolchain was declined for this execution. Laravel 11/12 require PHP ≥ 8.2; the newest Filament requires PHP ≥ 8.2. Running the backend entirely inside a PHP 8.3 Docker container was also considered and not chosen.

Given the choice presented and made, the backend must run natively on PHP 8.1.

## Decision

- Pin the backend to **Laravel 10.x** (`laravel/framework ^10.10`, resolved to 10.50.3) and **Filament 3.x**, both of which support PHP 8.1.
- Set Composer `policy.advisories.block = false` in `backend/composer.json`, because Laravel 10.x is past its active-support window and several 10.x releases carry unpatched security advisories that Composer 2.10 otherwise refuses to install.
- Database: reuse the existing local PostgreSQL 18 Docker container (`127.0.0.1:5433`), with a dedicated `insuralab` database and role. `docker-compose.yml` additionally defines a self-contained `insuralab-postgres` service (profile `full`) for portability.

## Consequences / known trade-offs

- **Security:** Laravel 10 no longer receives routine bug/security fixes (security-only support ended Feb 2025; this is Aug 2026). The advisory block is disabled. This is acceptable **only** for a local, pre-deployment educational foundation handling no real user data. It is **not** acceptable for production.
- **Longevity:** Filament 3 and Laravel 10 will fall further behind. Package choices in later phases must remain 8.1-compatible or the runtime must be upgraded first.
- **Divergence from brief:** "latest stable" is not met.

## Recommended follow-up (before Phase 3)

Upgrade the runtime to **PHP 8.3+** and migrate to **Laravel 11/12 + Filament 3/4**, then re-enable Composer advisory blocking. This is a prerequisite for any deployment and should be treated as Phase 3 preparation work. Until then, the pin stands.
