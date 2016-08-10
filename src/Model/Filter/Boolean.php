<?php
namespace Search\Model\Filter;

class Boolean extends Base
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'truthy' => [1, true, '1', 'true', 'yes', 'on'],
        'falsy' => [0, false, '0', 'false', 'no', 'off']
    ];

    /**
     * Check if a value is truthy/falsy and pass as condition.
     *
     * @return void
     */
    public function process()
    {
        if ($this->skip()) {
            return;
        }

        $value = strtolower($this->value());
        $bool = null;
        if (in_array($value, $this->config('truthy'), true)) {
            $bool = true;
        } elseif (in_array($value, $this->config('falsy'), true)) {
            $bool = false;
        }

        if ($bool !== null) {
            $this->query()->andWhere([$this->field() => $bool]);
        }
    }
}
