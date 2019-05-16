<?php
declare(strict_types=1);

namespace Search\Test\TestApp\Model\Filter;

use Search\Model\Filter\FilterCollection;

class MyTestCollection extends FilterCollection
{
    public function initialize(): void
    {
        $this->add('first', 'Search.Callback');
    }
}
