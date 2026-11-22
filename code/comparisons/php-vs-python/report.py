from contextlib import closing
from pathlib import Path
import sqlite3
import sys

if len(sys.argv) != 2 or not sys.argv[1].strip():
    print("Usage: python3 report.py CUSTOMER", file=sys.stderr)
    raise SystemExit(2)

path = Path(__file__).with_name("invoices.sqlite")
if not path.is_file():
    print("Create invoices.sqlite from invoices.sql first.", file=sys.stderr)
    raise SystemExit(1)

try:
    database_uri = path.resolve().as_uri() + "?mode=ro"
    with closing(sqlite3.connect(database_uri, uri=True)) as db:
        row = db.execute(
            """
            SELECT COUNT(i.customer), COALESCE(SUM(i.cents), 0)
            FROM customers AS c
            LEFT JOIN invoices AS i
                ON i.customer = c.slug AND i.status = 'paid'
            WHERE c.slug = :customer
            GROUP BY c.slug
            """,
            {"customer": sys.argv[1]},
        ).fetchone()
except sqlite3.Error:
    print("Could not read the invoice database. Check that the fixture was loaded.", file=sys.stderr)
    raise SystemExit(1)

if row is None:
    print("Unknown customer.", file=sys.stderr)
    raise SystemExit(3)

count, total = row
print(f"Paid invoices = {count}, total = {total} cents")
