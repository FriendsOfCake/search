<?php
namespace Search\Model\Filter;

use Cake\Database\Expression\Comparison;
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
            $columnType = 'string';

            if (is_string($field)) {
                $scheme = $this->manager()->table()->schema();

                $columnExists = $scheme->column($field);
                $columnType = (!$columnExists) ? $scheme->columnType($field) : 'string';
            }

            $value = $this->_wildCards($this->value());

            $conditions[] = new Comparison($field, $value, $columnType, 'LIKE');
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
