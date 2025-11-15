# Basic Configuration and Usage

## Search Behavior

Attach the `Search` behavior to your table class. In your table class'
`initialize()` method call its `searchManager()` method, it will return a search
manager instance. You can now add filters to the manager by chaining them.
The first arg of the `add()` method is the field, the second is the filter using
the dot notation of cake to load filters from plugins. The third one is an array
of filter specific options. Please refer to [the Options section](filters-and-examples.md#options) for
an explanation of the available options supported by the different filters.

```php
/**
 * @mixin \Search\Model\Behavior\SearchBehavior
 */
class PostsTable extends Table
{
    /**
     * @param array $config
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        // Add the behavior to your table
        $this->addBehavior('Search.Search');

        // Setup search filter using search manager
        $this->getBehavior('Search')->searchManager()
            ->value('author_id')
            // Here we will alias the 'q' query param to search the `Articles.title`
            // field and the `Articles.content` field, using a LIKE match, with `%`
            // both before and after.
            ->add('q', 'Search.Like', [
                'before' => true,
                'after' => true,
                'fieldMode' => 'OR',
                'comparison' => 'LIKE',
                'wildcardAny' => '*',
                'wildcardOne' => '?',
                'fields' => ['title', 'content'],
            ])
            ->add('foo', 'Search.Callback', [
                'callback' => function (\Cake\ORM\Query $query, array $args, \Search\Model\Filter\Base $filter) {
                    // Modify $query as required
                }
            ]);
    }
```

You can use `SearchManager::add()` method to add filter or use specific methods
like `value()`, `like()` etc. for in built filters.

## Search Component
Add the `Search.Search` component with the necessary actions in your controller.

```php
public function initialize(): void
{
    parent::initialize();

    $this->loadComponent('Search.Search', [
        // This is default config. You can modify "actions" as needed to make
        // the Search component work only for specified methods.
        'actions' => ['index', 'lookup'],
    ]);
}
```

The `Search.Search` component will allow your filtering forms to be populated using
the data in the query params. It uses the [PRG pattern](https://en.wikipedia.org/wiki/Post/Redirect/Get) (Post, redirect, get).

## Find call
In order for the Search plugin to work it will need to process the query params
which are passed in your URL. So you will need to edit your `index` method to
accommodate this.

```php
public function index()
{
    $query = $this->Posts
        // Use the plugins 'search' custom finder and pass in the
        // processed query params
        ->find('search', search: $this->request->getQueryParams())
        // You can add extra things to the query if you need to
        ->contain(['Comments'])
        ->where(['title IS NOT' => null]);

    $this->set('posts', $this->paginate($query));
}
```

The `search` finder is dynamically provided by the `Search` behavior.

If you are using the [crud](https://github.com/FriendsOfCake/crud) plugin you
just need to enable the [search](http://crud.readthedocs.io/en/latest/listeners/search.html)
listener for your crud action.

## Filtering your data
Once you have completed all the setup you can now filter your data by passing
query params in your index method. Using the example given above, you could
filter your posts using the following.

`example.com/posts?q=cakephp`

Would filter your list of posts to any article with "cakephp" in the `title`
or `content` field. You might choose to make a `get` form which posts the filter
directly to the URL, but if you're using the `Search.Search` component, you'll want
to use `POST`.

## Creating your form
In most cases you'll want to add a form to your index view which will search
your data.

```php
    echo $this->Form->create(null, ['valueSources' => 'query']);
    // You'll need to populate $authors in the template from your controller
    echo $this->Form->control('author_id');
    // Match the search param in your table configuration
    echo $this->Form->control('q');
    echo $this->Form->button('Filter', ['type' => 'submit']);
    echo $this->Html->link('Reset', ['action' => 'index']);
    echo $this->Form->end();
```

The array passed to `FormHelper::create()` will cause the helper to create an
`ArrayContext` internally and populate the respective search fields from the
query params.

### Adding a reset button dynamically
The Search component will pass down the information on whether the query was
modified by your search query string by setting `$_isSearch` view variable to
true here in this case. It also passes down a `$_searchParams` array of all query
string params that currently are part of the search.

You can use the Search helper (which is autoloaded by default for the search actions
by the Search component).

```php
// in your form template
if ($this->Search->isSearch()) {
    echo $this->Search->resetLink(__('Reset'), ['class' => 'button']);
}
```
