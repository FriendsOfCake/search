<?php
declare(strict_types=1);

namespace Search\Model\Filter;

use Cake\Database\Expression\ComparisonExpression;
use InvalidArgumentException;

class Compare extends Base
{
    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'operator' => '>=',
        'mode' => 'AND',
    ];

    /**
     * Allowed operators.
     *
     * @var array
     */
    protected $_operators = [
        '>=', '<=', '<', '>',
    ];

    /**
     * Process a comparison-based condition (e.g. $field <= $value).
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function process(): bool
    {
        $conditions = [];
        if (!in_array($this->getConfig('operator'), $this->_operators, true)) {
            throw new InvalidArgumentException(sprintf(
                'The operator %s is invalid!',
                $this->getConfig('operator')
            ));
        }

        $value = $this->value();
        if (!is_scalar($value)) {
            return false;
        }

        foreach ($this->fields() as $field) {
            $conditions[] = new ComparisonExpression($field, $value, 'string', $this->getConfig('operator'));
        }

        $this->getQuery()->andWhere([$this->getConfig('mode') => $conditions]);

        return true;
    }
}
