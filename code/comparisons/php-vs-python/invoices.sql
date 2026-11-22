PRAGMA foreign_keys = ON;

BEGIN;

CREATE TABLE customers (
    slug TEXT PRIMARY KEY NOT NULL
);

CREATE TABLE invoices (
    customer TEXT NOT NULL REFERENCES customers (slug),
    cents INTEGER NOT NULL CHECK (typeof(cents) = 'integer' AND cents >= 0),
    status TEXT NOT NULL CHECK (status IN ('paid', 'open'))
);

INSERT INTO customers (slug) VALUES ('north'), ('south'), ('west');

INSERT INTO invoices (customer, cents, status) VALUES
    ('north', 1250, 'paid'),
    ('north', 900, 'open'),
    ('north', 1200, 'paid'),
    ('south', 2000, 'paid'),
    ('west', 500, 'open');

COMMIT;
