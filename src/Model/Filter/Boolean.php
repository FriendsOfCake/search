<?php
namespace Search\Model\Filter;

use Cake\ORM\Table;

class Boolean extends Base
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'mode' => 'OR',
        'truthy' => [1, true, '1', 'true', 'yes', 'on'],
        'falsy' => [0, false, '0', 'false', 'no', 'off'],
    ];

    /**
     * Check if a value is truthy/falsy and pass as condition.
     *
     * @return bool
     */
    public function process()
    {
        $value = $this->value();
        if (!is_scalar($value)) {
            return false;
        }

        if (is_string($value)) {
            $value = strtolower($value);
        }

        $bool = null;
        if (in_array($value, $this->getConfig('truthy'), true)) {
            $bool = true;
        } elseif (in_array($value, $this->getConfig('falsy'), true)) {
            $bool = false;
        }

        if ($bool !== null) {
            if (!$this->manager()->getRepository() instanceof Table) {
                foreach ($this->fields() as $field) {
                    $this->getQuery()->where([
                        $field => $bool,
                    ]);
                }

                return true;
            }

            $conditions = [];
            foreach ($this->fields() as $field) {
                $conditions[] = [$field => $bool];
            }

            $this->getQuery()->andWhere([$this->getConfig('mode') => $conditions]);
        }

        return false;
    }
}
