<?php
namespace Search\Model\Filter;

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
     * Process a comparison-based condition (e.g. $field <= $value).
     *
     * @return void
     */
    public function process()
    {
        if ($this->skip()) {
            return;
        }

        $conditions = [];
        if (!in_array($this->config('operator'), $this->_operators, true)) {
            throw new \InvalidArgumentException(sprintf('The operator %s is invalid!', $this->config('operator')));
        }

        $value = $this->value();
        if (!is_scalar($value)) {
            return;
        }

        foreach ($this->fields() as $field) {
            $left = $field . ' ' . $this->config('operator');
            $conditions[] = [$left => $value];
        }

        $this->query()->andWhere($conditions);
    }
}
