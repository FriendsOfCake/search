<?php
declare(strict_types=1);

namespace Search\Test\TestApp\Model\Filter;

use Search\Model\Filter\Base;

class TestFilter extends Base
{
    public function process(): bool
    {
        return true;
    }
}
