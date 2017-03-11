<?php
namespace Search\Model\Filter;

use Cake\Core\App;
use InvalidArgumentException;
use Search\Manager;

class Like extends Base
{

    /**
     * Escaper to be used.
     *
     * @var \Search\Model\Filter\Escaper\EscaperInterface
     */
    protected $_escaper;

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
        'escaper' => null,
        'colType' => [],
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

        $this->_setEscaper();
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
            $colTypes = $this->config('colType');
            if ($colTypes) {
                $colTypes = $this->_aliasColTypes($colTypes);
            }

            $this->query()->andWhere([$this->config('fieldMode') => $conditions], $colTypes);
        }
    }

    /**
     * Alias the column type fields to match the field aliases of the conditions.
     *
     * @param array $colTypes Column types to be aliased.
     * @return array Aliased column types.
     */
    protected function _aliasColTypes($colTypes)
    {
        $repository = $this->manager()->repository();
        if (!method_exists($repository, 'aliasField')) {
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
        $value = $this->_escaper->formatWildcards($value);

        return $value;
    }

    /**
     * set configuration for escape driver name
     *
     * @return void
     */
    protected function _setEscaper()
    {
        if ($this->config('escaper') === null) {
            $driver = get_class($this->query()->connection()->driver());
            $driverName = 'Sqlserver';
            if (substr_compare($driver, $driverName, -strlen($driverName)) === 0) {
                $this->config('escaper', 'Search.Sqlserver');
            } else {
                $this->config('escaper', 'Search.Default');
            }
        }

        $class = $this->config('escaper');
        $className = App::className($class, 'Model/Filter/Escaper', 'Escaper');
        if (!$className) {
            throw new InvalidArgumentException(sprintf('Escape driver "%s" in like filter was not found.', $class));
        }

        $this->_escaper = new $className($this->config());
    }
}
