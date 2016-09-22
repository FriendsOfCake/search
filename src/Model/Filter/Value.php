<?php
namespace Search\Model\Filter;

class Value extends Base
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'mode' => 'or',
    ];

    /**
     * Process a value condition ($x == $y).
     *
     * @return void
     */
    public function process()
    {
        if ($this->skip()) {
            return;
        }

        $value = $this->value();
        if ($value === null) {
            return;
        }

        $isMultiValue = is_array($value);
        if ($isMultiValue &&
            empty($value)
        ) {
            return;
        }

        $this->query()->andWhere(function ($e) use ($value, $isMultiValue) {
            /* @var $e \Cake\Database\Expression\QueryExpression */
            $field = $this->field();

            if (strtolower($this->config('mode')) === 'or' &&
                $isMultiValue
            ) {
                return $e->in($field, $value);
            }

            foreach ((array)$value as $val) {
                $e->eq($field, $val);
            }

            return $e;
        });
    }
}
