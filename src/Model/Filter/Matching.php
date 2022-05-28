<?php
declare(strict_types=1);

namespace Search\Model\Filter;

use Cake\ORM\Query;

class Matching extends Base
{
    /**
     * Modify query to filter a matching association.
     *
     * @return bool
     */
    public function process(): bool
    {
        $assoc = $this->getConfig('assoc');
        $pkName = $this->getConfig('pkName');
        $fkName = $this->getConfig('fkName');
        $query = $this->getQuery();
        $args = $this->getArgs();
        if (isset($args[$fkName]) && $query instanceof Query) {
            $query
                ->matching($assoc, function (Query $query) use ($assoc, $pkName, $fkName, $args) {
                    return $query->where([sprintf('%s.%s IN', $assoc, $pkName) => $args[$fkName]]);
                });

            return true;
        }

        return false;
    }
}
