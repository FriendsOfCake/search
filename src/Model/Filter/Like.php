<?php
namespace Search\Model\Filter;

use Cake\Core\App;
use Cake\ORM\Query;
use InvalidArgumentException;
use RuntimeException;

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
        'fieldMode' => 'OR',
        'valueMode' => 'OR',
        'comparison' => 'LIKE',
        'wildcardAny' => '*',
        'wildcardOne' => '?',
        'escaper' => null,
        'colType' => [],
    ];

    /**
     * Process a LIKE condition ($x LIKE $y).
     *
     * @return bool
     */
    public function process()
    {
        $this->_setEscaper();
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
            $colTypes = $this->getConfig('colType');
            if ($colTypes) {
                $colTypes = $this->_aliasColTypes($colTypes);
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
    protected function _aliasColTypes($colTypes)
    {
        $repository = $this->manager()->getRepository();
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
        if ($this->getConfig('before')) {
            $value = $this->_formatWildcards($this->getConfig('wildcardAny')) . $value;
        }

        if ($this->getConfig('after')) {
            $value = $value . $this->_formatWildcards($this->getConfig('wildcardAny'));
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
     * @throws \InvalidArgumentException
     */
    protected function _setEscaper()
    {
        if ($this->getConfig('escaper') === null) {
            $query = $this->getQuery();
            if (!$query instanceof Query) {
                throw new RuntimeException('$query must be instance of Cake\ORM\Query to be able to check driver name.');
            }
            $driver = get_class($query->getConnection()->getDriver());
            $driverName = 'Sqlserver';
            if (substr_compare($driver, $driverName, -strlen($driverName)) === 0) {
                $this->setConfig('escaper', 'Search.Sqlserver');
            } else {
                $this->setConfig('escaper', 'Search.Default');
            }
        }

        $class = $this->getConfig('escaper');
        $className = App::className($class, 'Model/Filter/Escaper', 'Escaper');
        if (!$className) {
            throw new InvalidArgumentException(sprintf(
                'Escape driver "%s" in like filter was not found.',
                $class
            ));
        }

        $this->_escaper = new $className($this->getConfig());
    }
}
