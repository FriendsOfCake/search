<?php
declare(strict_types=1);

namespace Search\Test\TestApp\Model\Filter;

use Search\Model\Filter\FilterCollection;

class EmptyCollection extends FilterCollection
{
    /**
     * @return void
     */
    public function initialize(): void
    {
    }
}
