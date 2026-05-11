<?php
declare(strict_types=1);

namespace Search\Model\Filter\Escaper;

/**
 * Escaper for the PostgreSQL driver.
 *
 * Inherits wildcard handling from the default escaper but exists as a
 * distinct class so the `Like` filter can select it via `instanceof` against
 * the driver and so it can be overridden independently.
 *
 * Postgres uses `LIKE` for case-sensitive matching and `ILIKE` for the
 * case-insensitive form; users who want the case-insensitive behavior set
 * `comparison => 'ILIKE'` on the filter or on the connection-aware default.
 */
class PostgresEscaper extends DefaultEscaper
{
}
