<?php
declare(strict_types=1);

namespace Search\Model\Filter;

use Cake\Database\Expression\ComparisonExpression;
use Cake\Database\Expression\UnaryExpression;
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

        $conditions = [];
        foreach ($this->fields() as $field) {
            if ($nullValue !== null) {
                $conditions[] = new ComparisonExpression($field, $nullValue, 'string', $bool ? '!=' : '=');

                continue;
            }

            $conditions[] = new UnaryExpression($bool ? 'IS NOT NULL' : 'IS NULL', $field, UnaryExpression::POSTFIX);
        }

        if (!$this->manager()->getRepository() instanceof Table) {
            $this->getQuery()->where($conditions);
        } else {
            $this->getQuery()->andWhere([$this->getConfig('mode') => $conditions]);
        }

        return true;
    }
}
