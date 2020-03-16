<?php
declare(strict_types=1);

namespace Search\Test\TestApp\Model\Filter;

use Search\Model\Filter\FilterCollection;

class ArticlesCollection extends FilterCollection
{
    public function initialize(): void
    {
        $this->value('foo')
            ->like('search', ['filterEmpty' => true])
            ->value('baz')
            ->value('group', ['fields' => 'Articles.group']);
    }
}
