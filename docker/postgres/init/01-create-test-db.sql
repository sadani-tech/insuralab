-- Runs only on first initialisation of the self-contained `insuralab-postgres`
-- service (docker compose --profile full up). The primary `insuralab` database
-- and role are created from the POSTGRES_* environment variables; this adds the
-- separate database used by the backend test suite.
CREATE DATABASE insuralab_testing OWNER insuralab;
