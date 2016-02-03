<?php
namespace Search\Model\Filter;

use Cake\ORM\Query;

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
        'mode' => 'or',
        'comparison' => 'LIKE'
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
        foreach ($this->fields() as $field) {
            $left = $field . ' ' . $this->config('comparison');
            $right = $this->_wildCards($this->value());

            $conditions[] = [$left => $right];
        }

        $this->query()->andWhere([$this->config('mode') => $conditions]);
    }

    /**
     * Wrap wild cards around the value.
     *
     * @param  string $value Value.
     * @return string
     */
    protected function _wildCards($value)
    {
        if ($this->config('before')) {
            $value = '%' . $value;
        }

        if ($this->config('after')) {
            $value = $value . '%';
        }

        return $value;
    }
}
