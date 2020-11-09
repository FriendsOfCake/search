<?php
declare(strict_types=1);

namespace Search\Test\TestApp\Model\Filter;

use Search\Model\Filter\FilterCollection;

class EmptyFilterCollection extends FilterCollection
{
    /**
     * @return void
     */
    public function initialize(): void
    {
    }
}
