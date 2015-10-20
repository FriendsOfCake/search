<?php
namespace Search\Model\Filter;

use Cake\Database\Expression\Comparison;
use Cake\ORM\Query;

class Compare extends Base
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'operator' => '>=',
    ];

    /**
     * Allowed operators.
     *
     * @var array
     */
    protected $_operators = [
        '>=', '<=', '<', '>'
    ];

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

        $conditions = [];
        if (!in_array($this->config('operator'), $this->_operators)) {
            throw new \InvalidArgumentException(sprintf('The operator %s is invalid!', $this->config('operator')));
        }
        foreach ($this->fields() as $field) {
            $columnType = 'string';

            if (is_string($field)) {
                $columnExists = $this->manager()->table()->schema()->column($field);
                $columnType = (!$columnExists) ? $this->manager()->table()->schema()->columnType($field) : 'string';
            }

            $conditions[] = new Comparison($field, $this->value(), $columnType, $this->config('operator'));;
        }

        $this->query()->andWhere($conditions);
    }
}
