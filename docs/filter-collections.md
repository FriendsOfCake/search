# Filter collections

The SearchManager has the ability to maintain multiple filter collections.
For e.g. you can have separate collections for *backend* and *frontend*.

All you need to do is:

```php
// PostsTable::initialize()
    $this->searchManager()
        ->useCollection('backend')
        ->add('q', 'Search.Like', [
            'before' => true,
            'after' => true,
            'mode' => 'or',
            'comparison' => 'LIKE',
            'wildcardAny' => '*',
            'wildcardOne' => '?',
            'fields' => ['body'],
        ])
        ->useCollection('frontend')
        ->value('name');
```

Let's use the *backend*'s filters by doing:

```php
// PostsController::action()
    $query = $this->Examples
        ->find('search', [
            'search' => $this->request->getQueryParams(),
            'collection' => 'backend',
        ]);
    }
```

## Filter collection classes

Apart from configuring filters through search mananger in your table class,
you can also create them as separate collection classes. This helps in
keeping your table's `initialize()` method uncluttered and the filters are lazy
loaded only when actually used.

```php
// src/Model/Filter/PostsCollection.php
<?php
declare(strict_types=1);

namespace App\Model\Filter;

use Search\Model\Filter\FilterCollection;

class PostsCollection extends FilterCollection
{
    public function initialize(): void
    {
        $this->add('foo', 'Search.Callback', [
            'callback' => function ($query, $args, $filter) {
                // Modify $query as required
            },
        ]);
        // More $this->add() calls here. The argument for FilterCollection::add()
        // are same as those of searchManager()->add() shown above.
    }
}
```

Conventionally if `PostsCollection` exists then it will be used as default filter
collection for `PostsTable`.

You can also configure the `Search` behavior to use another collection class
as default using the `collectionClass` config:

```php
use App\Model\Filter\MyPostsCollection;

// In PostsTable::initialize()
$this->addBehavior('Search.Search', [
    'collectionClass' => MyPostsCollection::class,
]);
```

You can also specify alternate collection class to use when making find call:

```php
// PostsController::action()
    $query = $this->Posts
        ->find('search', [
            'search' => $this->request->getQueryParams(),
            'collection' => 'posts_backend',
        ]);
    }
```

The above will use `App\Model\Filter\PostsBackendCollection`.


## Collection class vs table config

You can also set defaults in the Table class and inherit those for all searches.
The added collection classes would then provide only custom ones per search.

In your Table:
```php
    /**
     * @return \Search\Manager
     */
    public function searchManager()
    {
        $searchManager = $this->behaviors()->Search->searchManager()
            ->value('status');

        return $searchManager;
    }
```
In your Controller:
```php
    $this->Posts->addBehavior('Search.Search', [
        'collectionClass' => PostsFilterCollection::class,
    ]);
```
This would add additional filters on top of inherited `status` one.
