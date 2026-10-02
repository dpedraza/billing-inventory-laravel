-- Crea la base de datos de testing si no existe (owner: POSTGRES_USER).
-- Se ejecuta automáticamente en el primer arranque del contenedor PostgreSQL.
SELECT 'CREATE DATABASE testing'
WHERE NOT EXISTS (
    SELECT FROM pg_database WHERE datname = 'testing'
)\gexec
