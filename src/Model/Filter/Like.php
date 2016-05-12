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
        'comparison' => 'LIKE',
        'escapeWildcards' => false
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

            $value = $this->_escapeWildCards($this->value());
            $right = $this->_wildCards($value);

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

    /**
     * Escape wild cards in value.
     *
     * @param string $value Value.
     * @return string
     */
    protected function _escapeWildCards($value)
    {
        if (!$this->config('escapeWildcards')) {
            return $value;
        }

        $from = ['%', '_'];
        $to = ['\%', '\_'];
        return str_replace($from, $to, $value);
    }
}
