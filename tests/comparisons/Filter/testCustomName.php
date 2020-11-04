<?php
declare(strict_types=1);

namespace Search\Test\TestApp\Model\Filter;

use Search\Model\Filter\FilterCollection;

class MyPostsFilterCollection extends FilterCollection
{
    /**
     * @return void
     */
    public function initialize(): void
    {
        $this->value('author_id');
        $this->like('title');
        $this->like('published');
    }
}
