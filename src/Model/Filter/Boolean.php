<?php
declare(strict_types=1);

namespace Search\Model\Filter;

use Cake\Database\Expression\ComparisonExpression;
use Cake\ORM\Table;

class Boolean extends Base
{
    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'mode' => 'OR',
        'truthy' => [1, true, '1', 'true', 'yes', 'on'],
        'falsy' => [0, false, '0', 'false', 'no', 'off'],
    ];

    /**
     * Check if a value is truthy/falsy and pass as condition.
     *
     * @return bool
     */
    public function process(): bool
    {
        $value = $this->value();
        if (!is_scalar($value)) {
            return false;
        }

        if (is_string($value)) {
            $value = strtolower($value);
        }

        $bool = null;
        if (in_array($value, $this->getConfig('truthy'), true)) {
            $bool = true;
        } elseif (in_array($value, $this->getConfig('falsy'), true)) {
            $bool = false;
        }

        if ($bool === null) {
            return false;
        }

        $conditions = [];
        foreach ($this->fields() as $field) {
            $conditions[] = new ComparisonExpression($field, $bool, 'boolean', '=');
        }

        if ($this->manager()->getRepository() instanceof Table) {
            $this->getQuery()->andWhere([$this->getConfig('mode') => $conditions]);
        } else {
            $this->getQuery()->where($conditions);
        }

        return true;
    }
}
