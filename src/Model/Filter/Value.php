<?php
namespace Search\Model\Filter;

use Cake\Database\Expression\QueryExpression;
use Search\Manager;

class Value extends Base
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'fieldMode' => 'OR',
        'valueMode' => 'OR'
    ];

    /**
     * {@inheritDoc}
     *
     * @param string $name Name.
     * @param \Search\Manager $manager Manager.
     * @param array $config Config.
     */
    public function __construct($name, Manager $manager, array $config = [])
    {
        parent::__construct($name, $manager, $config);

        $mode = $this->config('mode');
        if ($mode !== null) {
            $this->config('valueMode', $mode);
        }
    }

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

        $expressions = [];
        foreach ($this->fields() as $field) {
            $expressions[] = function (QueryExpression $e) use ($field, $value, $isMultiValue) {
                if (strtoupper($this->config('valueMode')) === 'OR' &&
                    $isMultiValue
                ) {
                    return $e->in($field, $value);
                }

                foreach ((array)$value as $val) {
                    $e->eq($field, $val);
                }

                return $e;
            };
        }

        if (!empty($expressions)) {
            $this->query()->andWhere([$this->config('fieldMode') => $expressions]);
        }
    }
}
