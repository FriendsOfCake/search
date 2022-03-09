<?php
declare(strict_types=1);

namespace Search\Model\Filter;

use Cake\Database\Expression\ComparisonExpression;
use Cake\ORM\Table;

class Exists extends Base
{
    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'mode' => 'OR',
        'nullValue' => null, // Set to empty string for required fields
    ];

    /**
     * Check if a value is truthy/falsy and pass as condition aware of NULLable.
     *
     * @return bool
     */
    public function process(): bool
    {
        $value = $this->value();
        if (!is_scalar($value) || $value === '') {
            return false;
        }

        $bool = (bool)$value;

        $nullValue = $this->getConfig('nullValue');
        $comparison = ' !=';
        if (!$bool) {
            $comparison = '';
        }
        if ($nullValue === null) {
            $comparison = ' IS NOT';
            if (!$bool) {
                $comparison = ' IS';
            }
        }

        if (!$this->manager()->getRepository() instanceof Table) {
            foreach ($this->fields() as $field) {
                $this->getQuery()->where([
                    new ComparisonExpression($field, $nullValue, 'string', $comparison),
                ]);
            }

            return true;
        }

        $conditions = [];
        foreach ($this->fields() as $field) {
            $conditions[] = [
                new ComparisonExpression($field, $nullValue, 'string', $comparison),
            ];
        }

        $this->getQuery()->andWhere([$this->getConfig('mode') => $conditions]);

        return true;
    }
}
