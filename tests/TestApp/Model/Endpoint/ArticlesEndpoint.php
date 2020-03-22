<?php
declare(strict_types=1);

namespace Search\Test\TestApp\Model\Endpoint;

use Muffin\Webservice\Model\Endpoint;
use Search\Model\SearchTrait;

class ArticlesEndpoint extends Endpoint
{
    use SearchTrait;

    public function initialize(array $config): void
    {
        $this->searchManager()
            ->value('foo')
            ->boolean('public');
    }

    public static function defaultConnectionName(): string
    {
        return 'test';
    }
}
