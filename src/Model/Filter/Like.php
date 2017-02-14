<?php
namespace Search\Model\Filter;

use Search\Manager;

class Like extends Base
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'before' => false,
        'after' => false,
        'mode' => null,
        'fieldMode' => 'OR',
        'valueMode' => 'OR',
        'comparison' => 'LIKE',
        'wildcardAny' => '*',
        'wildcardOne' => '?',
    ];

    /**
     * {@inheritDoc}
     *
     * @param string $name Name.
     * @param \Search\Manager $manager Manager.
     * @param array $config Config.
     */
    public function __construct($name, Manager $manager, array $config = [])
    {
        parent::__construct($name, $manager, $config);

        $mode = $this->config('mode');
        if ($mode !== null) {
            $this->config('fieldMode', $mode);
        }
    }

    /**
     * Process a LIKE condition ($x LIKE $y).
     *
     * @return void
     */
    public function process()
    {
        if ($this->skip()) {
            return;
        }

        $comparison = $this->config('comparison');
        $valueMode = $this->config('valueMode');
        $value = $this->value();
        $isMultiValue = is_array($value);

        $conditions = [];
        foreach ($this->fields() as $field) {
            $left = $field . ' ' . $comparison;
            if ($isMultiValue) {
                $valueConditions = [];
                foreach ($value as $val) {
                    $right = $this->_wildcards($val);
                    if ($right !== false) {
                        $valueConditions[] = [$left => $right];
                    }
                }
                if (!empty($valueConditions)) {
                    $conditions[] = [$valueMode => $valueConditions];
                }
            } else {
                $right = $this->_wildcards($value);
                if ($right !== false) {
                    $conditions[] = [$left => $right];
                }
            }
        }

        if (!empty($conditions)) {
            $this->query()->andWhere([$this->config('fieldMode') => $conditions]);
        }
    }

    /**
     * Wrap wild cards around the value.
     *
     * @param string $value Value.
     * @return string|false Either the wildcard decorated input value, or `false` when
     *  encountering a non-string value.
     */
    protected function _wildcards($value)
    {
        if (!is_string($value)) {
            return false;
        }

        $value = $this->_formatWildcards($value);
        if ($this->config('before')) {
            $value = $this->_formatWildcards($this->config('wildcardAny')) . $value;
        }

        if ($this->config('after')) {
            $value = $value . $this->_formatWildcards($this->config('wildcardAny'));
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
    protected function _formatWildcards($value)
    {
        $from = $to = $substFrom = $substTo = [];
        $driver = get_class($this->query()->connection()->driver());
        $driverName = 'Sqlserver';
        if ($this->config('wildcardAny') !== '%') {
            $from[] = '%';
            if (substr_compare($driver, $driverName, -strlen($driverName)) === 0) {
                $to[] = '[%]';
            } else {
                $to[] = '\%';
            }
            $substFrom[] = $this->config('wildcardAny');
            $substTo[] = '%';
        }
        if ($this->config('wildcardOne') !== '_') {
            $from[] = '_';
            if (substr_compare($driver, $driverName, -strlen($driverName)) === 0) {
                $to[] = '[_]';
            } else {
                $to[] = '\_';
            }
            $substFrom[] = $this->config('wildcardOne');
            $substTo[] = '_';
        }
        if ($from) {
            // Escape first
            $value = str_replace($from, $to, $value);
            // Replace wildcards
            $value = str_replace($substFrom, $substTo, $value);
        }

        return $value;
    }
}
