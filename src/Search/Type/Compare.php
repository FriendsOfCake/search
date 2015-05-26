<?php
namespace Search\Search\Type;

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
        if (!$this->present()) {
            return;
        }

        $conditions = [];
        if (!in_array($this->config('operator'), $this->_operators)) {
            throw new \InvalidArgumentException(sprintf('The operator %s is invalid!', $this->config('operator')));
        }
        foreach ($this->fields() as $field) {
            $left = $field . ' ' . $this->config('operator');
            $right = $this->value();

            $conditions[] = [$left => $right];
        }

        $this->query()->andWhere($conditions);
    }
}
