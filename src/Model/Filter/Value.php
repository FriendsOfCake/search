<?php
declare(strict_types=1);

namespace Search\Model\Filter;

use Cake\Core\Exception\Exception;
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
        'negationChar' => null,
    ];

    /**
     * Process a value condition ($x == $y).
     *
     * @return bool
     */
    public function process(): bool
    {
        $value = $this->value();
        if ($value === null) {
            return false;
        }

        $isMultiValue = is_array($value);
        if (
            $isMultiValue &&
            empty($value)
        ) {
            return false;
        }

        $isNegated = false;
        $negationChar = $this->getConfig('negationChar');
        if ($negationChar) {
            if ($this->getConfig('multiValue')) {
                throw new Exception('Cannot use negation functionality with multi value');
            }

            if (strpos($value, $negationChar) === 0) {
                $value = mb_substr($value, mb_strlen($negationChar));
                $isNegated = true;
            }
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
            $expressions[] = function (QueryExpression $e) use ($field, $value, $isMultiValue, $isNegated) {
                if ($isMultiValue) {
                    return $e->in($field, $value);
                }

                if ($isNegated) {
                    return $e->notEq($field, $value);
                }

                return $e->eq($field, $value);
            };
        }

        $this->getQuery()->where([$this->getConfig('mode') => $expressions]);

        return true;
    }
}
