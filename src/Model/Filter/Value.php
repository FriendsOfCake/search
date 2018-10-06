<?php
namespace Search\Model\Filter;

use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Table;

class Value extends Base
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'mode' => 'OR',
    ];

    /**
     * Process a value condition ($x == $y).
     *
     * @return bool
     */
    public function process()
    {
        $value = $this->value();
        if ($value === null) {
            return false;
        }

        $isMultiValue = is_array($value);
        if ($isMultiValue &&
            empty($value)
        ) {
            return false;
        }

        if (!$this->manager()->getRepository() instanceof Table) {
            foreach ($this->fields() as $field) {
                $this->getQuery()->where([
                    $field => $value,
                ]);
            }

            return true;
        }

        $expressions = [];
        foreach ($this->fields() as $field) {
            $expressions[] = function (QueryExpression $e) use ($field, $value, $isMultiValue) {
                if ($isMultiValue) {
                    return $e->in($field, $value);
                }

                return $e->eq($field, $value);
            };
        }

        $this->getQuery()->andWhere([$this->getConfig('mode') => $expressions]);

        return true;
    }
}
