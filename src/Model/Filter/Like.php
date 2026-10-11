<?php
declare(strict_types=1);

namespace Search\Model\Filter;

use Cake\Core\App;
use Cake\Database\Driver;
use Cake\Database\Driver\Postgres;
use Cake\Database\Driver\Sqlserver;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use InvalidArgumentException;
use RuntimeException;
use Search\Model\Filter\Escaper\EscaperInterface;

class Like extends Base
{
    /**
     * Escaper to be used.
     *
     * @var \Search\Model\Filter\Escaper\EscaperInterface
     */
    protected EscaperInterface $escaper;

    /**
     * Default configuration.
     *
     * `escapers` maps a Cake database driver class name to an escaper class
     * spec (Cake plugin-syntax, e.g. `Search.Sqlserver`). It is consulted by
     * `setEscaper()` via `instanceof` so a subclassed driver still matches.
     * Apps register custom escapers by extending this map at filter setup:
     *
     * ```php
     * $searchManager->like('title', [
     *     'escapers' => [
     *         App\Database\Driver\MyMariaDb::class => 'App.MyMariaDb',
     *     ],
     * ]);
     * ```
     *
     * Entries are evaluated in iteration order; place more specific driver
     * classes before less specific ones. When no entry matches the active
     * driver, `Search.Default` is used.
     *
     * @var array<string, mixed>
     */
    protected array $defaultConfig = [
        'before' => false,
        'after' => false,
        'fieldMode' => 'OR',
        'valueMode' => 'OR',
        'comparison' => 'LIKE',
        'wildcardAny' => '*',
        'wildcardOne' => '?',
        'escaper' => null,
        'escapers' => [
            Sqlserver::class => 'Search.Sqlserver',
            Postgres::class => 'Search.Postgres',
        ],
        'colType' => [],
    ];

    /**
     * Process a LIKE condition ($x LIKE $y).
     *
     * @return bool
     */
    public function process(): bool
    {
        $this->setEscaper();
        $comparison = $this->getConfig('comparison');
        $valueMode = $this->getConfig('valueMode');
        $value = $this->value();
        $isMultiValue = is_array($value);

        $conditions = [];
        foreach ($this->fields() as $field) {
            $left = $field . ' ' . $comparison;
            if ($isMultiValue) {
                $valueConditions = [];
                foreach ($value as $val) {
                    $right = $this->wildcards($val);
                    if ($right !== false) {
                        $valueConditions[] = [$left => $right];
                    }
                }
                if (!empty($valueConditions)) {
                    $conditions[] = [$valueMode => $valueConditions];
                }
            } else {
                $right = $this->wildcards($value);
                if ($right !== false) {
                    $conditions[] = [$left => $right];
                }
            }
        }

        if ($conditions) {
            $colTypes = (array)$this->getConfig('colType');
            if ($colTypes) {
                $colTypes = $this->aliasColTypes($colTypes);
            }

            $this->getQuery()->andWhere([$this->getConfig('fieldMode') => $conditions], $colTypes);

            return true;
        }

        return false;
    }

    /**
     * Alias the column type fields to match the field aliases of the conditions.
     *
     * @param array $colTypes Column types to be aliased.
     * @return array Aliased column types.
     */
    protected function aliasColTypes(array $colTypes): array
    {
        $repository = $this->manager()->getRepository();
        if (!$repository instanceof Table) {
            return $colTypes;
        }

        $return = [];
        foreach ($colTypes as $field => $colType) {
            $return[$repository->aliasField($field)] = $colType;
        }

        return $return;
    }

    /**
     * Wrap wild cards around the value.
     *
     * @param mixed $value Value.
     * @return string|false Either the wildcard decorated input value, or `false` when
     *  encountering a non-string value.
     */
    protected function wildcards(mixed $value): string|false
    {
        if (!is_string($value)) {
            return false;
        }

        $value = $this->formatWildcards($value);
        if ($this->getConfig('before')) {
            $value = $this->formatWildcards($this->getConfig('wildcardAny')) . $value;
        }

        if ($this->getConfig('after')) {
            $value = $value . $this->formatWildcards($this->getConfig('wildcardAny'));
        }

        return $value;
    }

    /**
     * Replace substitutions with original wildcards
     * but first, escape the original wildcards in the text to use them as normal search text
     *
     * @param string $value Value.
     * @return string Value
     */
    protected function formatWildcards(string $value): string
    {
        $value = $this->escaper->formatWildcards($value);

        return $value;
    }

    /**
     * set configuration for escape driver name
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function setEscaper(): void
    {
        $class = $this->getConfig('escaper');

        if ($class === null) {
            $query = $this->getQuery();
            if (!$query instanceof SelectQuery) {
                throw new RuntimeException(
                    '$query must be instance of Cake\ORM\Query\SelectQuery to be able to check driver name.',
                );
            }

            $driver = $query->getConnection()->getDriver();
            $class = $this->resolveEscaperClass($driver);
            // Intentionally NOT caching the resolved class on the filter:
            // re-using the same filter instance against a query backed by a
            // different connection / driver must resolve afresh.
        }

        /** @var class-string<\Search\Model\Filter\Escaper\EscaperInterface>|null $className */
        $className = App::className($class, 'Model/Filter/Escaper', 'Escaper');
        if ($className === null) {
            throw new InvalidArgumentException(sprintf(
                'Escape driver "%s" in like filter was not found.',
                $class,
            ));
        }

        $this->escaper = new $className($this->getConfig());
    }

    /**
     * Resolve the escaper class spec for the given driver instance against
     * the configured `escapers` map. Falls back to `Search.Default` when no
     * map entry matches.
     *
     * Override this method (or supply a custom `escapers` map) to register
     * driver-specific escapers without subclassing the filter.
     *
     * @param \Cake\Database\Driver $driver Driver instance to resolve against.
     * @return string Cake plugin-syntax escaper class spec.
     */
    protected function resolveEscaperClass(Driver $driver): string
    {
        $escapers = (array)$this->getConfig('escapers');
        foreach ($escapers as $driverClass => $escaperClass) {
            if (!is_string($driverClass) || $driverClass === '') {
                continue;
            }
            if ($driver instanceof $driverClass) {
                return (string)$escaperClass;
            }
        }

        return 'Search.Default';
    }
}
