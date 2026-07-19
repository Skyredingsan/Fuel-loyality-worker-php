CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS pg_trgm;
CREATE EXTENSION IF NOT EXISTS btree_gin;

SET TIMEZONE TO 'Europe/Moscow';

ALTER DATABASE fuel_points SET client_encoding TO 'UTF8';
ALTER DATABASE fuel_points SET timezone TO 'Europe/Moscow';