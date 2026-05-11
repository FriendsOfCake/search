<?php
declare(strict_types=1);

namespace Search\Model\Filter;

use Search\Manager;

/**
 * Mapped Filter
 *
 * Maps form values to filter conditions. Useful for filters with a default
 * value that shouldn't trigger isSearch().
 *
 * Boolean example:
 * ```
 * $this->mapped('enabled', [
 *     'map' => ['' => true, '0' => false, '-1' => null],
 *     'default' => '',
 * ]);
 * ```
 *
 * Enum example (unmapped values pass through directly):
 * ```
 * $this->mapped('status', [
 *     'map' => ['' => 'pending', '-1' => null],
 *     'default' => '',
 * ]);
 * // 'active', 'completed' etc. pass through as-is
 * ```
 *
 * - Map keys are form values, map values are the condition to apply
 * - `null` in map means "no filter condition" (show all)
 * - `default` key specifies which value doesn't trigger isSearch()
 * - Non-empty values not in map pass through directly as filter condition
 */
class Mapped extends Base
{
    /**
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'fields' => null,
        'map' => [],
        'default' => null,
    ];

    /**
     * Constructor - sets alwaysRun and filterEmpty defaults for this filter type.
     *
     * @param string $name Filter name
     * @param \Search\Manager $manager Search Manager
     * @param array<string, mixed> $config Config
     */
    public function __construct(string $name, Manager $manager, array $config = [])
    {
        // Mapped filter needs alwaysRun=true to apply default, filterEmpty=false to allow empty string
        $config += [
            'alwaysRun' => true,
            'filterEmpty' => false,
        ];

        parent::__construct($name, $manager, $config);
    }

    /**
     * Process the mapped filter.
     *
     * @return bool Whether this counts as an active search
     */
    public function process(): bool
    {
        $value = $this->value();
        $map = $this->getConfig('map');
        $default = $this->getConfig('default');
        $fields = $this->fields();

        // Normalize null to empty string for map lookup
        $lookupValue = $value ?? '';
        $isDefault = false;
        $condition = null;

        if (array_key_exists($lookupValue, $map)) {
            // Value is in map - use mapped condition
            $condition = $map[$lookupValue];
            $isDefault = ($lookupValue === $default);
        } elseif ($lookupValue !== '') {
            // Non-empty value not in map - pass through directly
            $condition = $lookupValue;
        } elseif ($default !== null && array_key_exists($default, $map)) {
            // Empty value, fall back to default
            $condition = $map[$default];
            $isDefault = true;
        } else {
            // No value, no default - skip entirely
            return false;
        }

        // null in map = no filter condition
        if ($condition !== null) {
            foreach ($fields as $field) {
                $this->getQuery()->where([$field => $condition]);
            }
        }

        // Return false (not a search) only when using default
        return !$isDefault;
    }
}
