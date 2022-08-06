# Custom repository

It is also possible to use the search plugin on custom repositories which
implement `Cake\Datasource\RepositoryInterface` like endpoint classes used
in the Webservice plugin.

```php
<?php
declare(strict_types=1);

namespace App\Model\Endpoint;

use Muffin\Webservice\Model\Endpoint;
use Search\Model\SearchTrait;

class ProductsEndpoint extends Endpoint
{
    use SearchTrait;

    public function initialize()
    {
        $this->searchManager()
            ->value('category_id');
    }
}

```

After including the trait you can use the searchManager by calling the
`searchManager()` method from your `initialize()` method.
