<?php
namespace Search\Test\TestApp\Model\Endpoint;

use Muffin\Webservice\Model\Endpoint;
use Search\Manager;
use Search\Model\SearchTrait;

class ArticlesEndpoint extends Endpoint
{
    use SearchTrait;

    public function initialize(array $config)
    {
        $this->searchManager()
            ->value('foo')
            ->boolean('public');
    }

    public static function defaultConnectionName()
    {
        return 'test';
    }
}
