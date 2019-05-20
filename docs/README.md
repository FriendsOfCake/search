# CakePHP Search Plugin Documentation

## Configuration and Usage

### Search Behavior

Attach the `Search` behaviour to your table class. In your table class'
`initialize()` method call the `searchManager()` method, it will return a search
manager instance. You can now add filters to the manager by chaining them.
The first arg of the `add()` method is the field, the second is the filter using
the dot notation of cake to load filters from plugins. The third one is an array
of filter specific options. Please refer to [the Options section](#options) for
an explanation of the available options supported by the different filters.

```php
/**
 * @mixin \Search\Model\Behavior\SearchBehavior
 */
class PostsTable extends Table
{
    /**
     * @param array $config
     *
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        // Add the behaviour to your table
        $this->addBehavior('Search.Search');

        // Setup search filter using search manager
        $this->searchManager()
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
                'field' => ['title', 'content']
            ])
            ->add('foo', 'Search.Callback', [
                'callback' => function ($query, $args, $filter) {
                    // Modify $query as required
                }
            ]);
    }
```

You can use `SearchManager::add()` method to add filter or use specific methods
like `value()`, `like()` etc. for in built filters.

### Filter collections

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
            'field' => ['body']
        ])
        ->useCollection('frontend')
        ->value('name');
```

Let's use the *backend*'s filters by doing:

```php
// PostsController::action()
    $query = $this->Examples
        ->find('search', [
            'search' => $this->request->getQuery(),
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
namespace App\Model\Filter;

use Search\Model\Filter\FilterCollection;

class PostsCollection extends FilterCollection
{
    public function initialize()
    {
        $this->add('foo', 'Search.Callback', [
            'callback' => function ($query, $args, $filter) {
                // Modify $query as required
            }
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
    'collectionClass' => MyPostsCollection::class
]);
```

You can also specify alternate collection class to use when making find call:

```php
// PostsController::action()
    $query = $this->Posts
        ->find('search', [
            'search' => $this->request->getQueryParams(),
            'collection' => 'posts_backend'
        ]);
    }
```

The above will use `App\Model\Filter\PostsBackendCollection`.

### Prg Component
Add the Search Prg component to the necessary methods in your controller.

```php
public function initialize()
{
    parent::initialize();

    $this->loadComponent('Search.Prg', [
        // This is default config. You can modify "actions" as needed to make
        // the PRG component work only for specified methods.
        'actions' => ['index', 'lookup']
    ]);
}
```

The `Search.Prg` component will allow your filtering forms to be populated using
the data in the query params. It uses the [Post, redirect, get pattern](https://en.wikipedia.org/wiki/Post/Redirect/Get).

### Find call
In order for the Search plugin to work it will need to process the query params
which are passed in your URL. So you will need to edit your `index` method to
accommodate this.

```php
public function index()
{
    $query = $this->Posts
        // Use the plugins 'search' custom finder and pass in the
        // processed query params
        ->find('search', ['search' => $this->request->getQueryParams()])
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

### Custom repository

It is also possible to use the search plugin on custom repositories which
implement `Cake\Datasource\RepositoryInterface` like endpoint classes used
in the Webservice plugin.

```php
<?php

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

## Filtering your data
Once you have completed all the setup you can now filter your data by passing
query params in your index method. Using the example given above, you could
filter your posts using the following.

`example.com/posts?q=cakephp`

Would filter your list of posts to any article with "cakephp" in the `title`
or `content` field. You might choose to make a `get` form which posts the filter
directly to the URL, but if you're using the `Search.Prg` component, you'll want
to use `POST`.

### Creating your form
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

#### Adding a reset button dynamically
The Prg component will pass down the information on whether the query was
modified by your search query string by setting `$_isSearch` view variable to
true here in this case. It also passes down a `$_searchParams` array of all query string params
that currently are part of the search.
You can use those to display certain elements on the page or use the following helper as
convenience wrapper.

```php
// in AppView.php
$this->loadHelper('Search.Search');

// in your form template
if ($this->Search->isSearch()) {
    echo $this->Search->resetLink(__('Reset'), ['class' => 'button']);
}
```

## Filters

The Search plugin comes with a set of predefined search filters that allow you to
easily create the search results you need. Use:

- `Value` to limit results to exact matches
- `Like` to produce results containing the search query (`LIKE` or `ILIKE`)
- `Boolean` to limit results by truthy (by default: 1, true, '1', 'true', 'yes', 'on')
  and falsy (by default: 0, false, '0', 'false', 'no', 'off') values which are
  passed down to the ORM as true/1 or false/0 or ignored when being neither truthy or falsy.
- `Exists` to produce results for existing (non-empty) column content.
- `Finder` to produce results using a [(custom)](http://book.cakephp.org/3.0/en/orm/retrieving-data-and-resultsets.html#custom-find-methods) finder
- `Compare` to produce results requiring operator comparison (`>`, `<`, `>=` and `<=`)
- `Callback` to produce results using your own custom callable function, it
  should return bool to specify `isSearch()` (useful when using with `alwaysRun` enabled)

### Options

#### All filters

The following options are supported by all filters.

- `field` (`string`, defaults to the name passed to the first argument of the
  add filter method) The name of the field to use for searching. Use this option
  if you need to use a name in your forms that doesn't match the actual field name.

- `name` (`string`, defaults to the name passed to the first argument of the add
  filter method) The name of the field to look up in the request data. Use this
  option if you need to configure the name of the filter differently than the name
  of the field, in cases where you can't use the `field` option, for example when it
  is being used to define multiple fields, which is supported by the `Like` filter.

- `alwaysRun` (`bool`, defaults to `false`) Defines whether the filter should always
  run, irrespectively of whether the corresponding field exists in the request data.

- `filterEmpty` (`bool`, defaults to `false`) Defines whether the filter should not
  run in case the corresponding field in the request is empty. Refer to
  [the Optional fields section](#optional-fields) for additional details.

- `flatten` (`bool`, defaults to `true`) Defines whether values passed from the
  the input form as arrays should be flattened. If the structure of the value array
  should be maintained to ease parsing the passed data with your chosen filter,
  set this to `false`.

- `beforeProcess` (`callable`, defaults to `null`) A callable which can be used
  to modify the query before the main `process()` method of filter is run.
  It receives `$query` and `$args` as arguments. You can use the callback for e.g.
  to setup joins or contains on the query. If the callback returns `false` then
  processing of the filter will be skipped. If it returns `array` it will be used
  as filter arguments.

    ```php
    // PostsTable::initialize()
        $searchManager->like('q', [
            'fields' => ['Posts.title', 'Authors.title'],
            'beforeProcess' => function (\Cake\ORM\Query $query, array $args, \Search\Model\Filter\Base $filter) {
                $query->contain('Authors');
            },
        ]);
    ```

The following options are supported by all filters except `Callback` and `Finder`.

- `aliasField` (`bool`, defaults to `true`) Defines whether the field name should
  be aliased with respect to the alias used by the table class to which the behavior
  is attached to.

- `defaultValue` (`mixed`, defaults to `null`) The default value that is being
  used in case the value passed for the corresponding field is invalid or missing.

#### `Boolean`

- `mode` (`string`, defaults to `OR`) The conditional mode to use when matching
  against multiple fields. Valid values are `OR` and `AND`.

#### `Exists`

- `mode` (`string`, defaults to `OR`) The conditional mode to use when matching
  against multiple fields. Valid values are `OR` and `AND`.
- `nullValue` (`string` or `null`, defaults to `null`). Can be used for non-nullable columns.
Set it to an empty string there to check via `=`/`!=` instead of `IS NULL`/`IS NOT NULL`.

#### `Compare`

- `operator` (`string`, defaults to `>=`) The operator to use for comparison. Valid
  values are `>=`, `<=`, `>` and `<`.

- `mode` (`string`, defaults to `AND`) The conditional mode to use when matching
  against multiple fields. Valid values are `OR` and `AND`.

#### `Like`

- `multiValue` (`bool`, defaults to `false`) Defines whether the filter accepts
  multiple values. If disabled, and multiple values are being passed, the filter
  will fall back to using the default value defined by the `defaultValue` option.

- `multiValueSeparator` (`string`, defaults to `null`) Defines whether the filter should
  auto-tokenize multiple values using a specific separator string. If disabled, the data
  must be an in form of an array.

- `field` (`string|array`), defaults to the name passed to the first argument of the
  add filter method) The name of the field to use for searching. Works like the base
  `field` option but also accepts multiple field names as an array. When defining
  multiple fields, the search term is going to be looked up in all the given fields,
  using the conditional operator defined by the `fieldMode` option.

- `colType` (`array`), An associative array, use to set a custom type for any
  column that needs to be treated as string column despite its actual type.
  This is important for integer fields, for example, if they are part of the
  fields to be searched. Usage example:
   `'colType' => ['id' => 'string']`

- `before` (`bool`, defaults to `false`) Whether to automatically add a wildcard
  *before* the search term.

- `after` (`bool`, defaults to `false`) Whether to automatically add a wildcard
  *after* the search term.

- `fieldMode` (`string`, defaults to `OR`) The conditional mode to use when
  matching against multiple fields. Valid values are `OR` and `AND`.

- `valueMode` (`string`, defaults to `OR`) The conditional mode to use when
  searching for multiple values. Valid values are `OR` and `AND`.

- `comparison` (`string`, defaults to `LIKE`) The comparison operator to use.

- `wildcardAny` (`string`, defaults to `*`) Defines the string that should be
  treated as a _any_ wildcard in case it is being encountered in the search term.
  The behavior will internally replace this with the appropriate SQL compatible
  wildcard. This is useful if you want to pass wildcards inside of the search term,
  while still being able to use the actual wildcard character inside of the search
  term so that it is being treated as a part of the term. For example a search term
  of `* has reached 100%` would be converted to `% has reached 100\%`.
  Additionally see option `escapeDriver`.

- `wildcardOne` (`string`, defaults to `?`) Defines the string that should be
  treated as a _one_ wildcard in case it is being encountered in the search term.
  Behaves similar to `wildcardAny`, that is, the actual SQL compatible wildcard
  (`_`) is being escaped in case used the search term.

- `escaper` (`string`, default to `null`) Defines the escaper that should
  escape `%` and `_`. If no escaper is set (`escapeDriver => 'null'`) the escaper
  is set by database driver. If the driver is `Sqlserver` the `SqlserverEscaper`
  is used (escaping `%` to `[%]` and `_` to `[_]`). In all other cases the
  `DefaultEscaper` is used (escaping `%` to `\%` and `_` to `\_`). You can add an
  own escaper by adding a escaper in `App\Model\Filter\Escaper\OwnEscaper` and
  settings `'escaper' => 'App.Own'`.

#### `Value`

- `multiValue` (`bool`, defaults to `false`) Defines whether the filter accepts
  multiple values. If disabled, and multiple values are being passed, the filter
  will fall back to using the default value defined by the `defaultValue` option.

- `multiValueSeparator` (`string`, defaults to `null`) Defines whether the filter should
  auto-tokenize multiple values using a specific separator string. If disabled, the data
  must be an in form of an array.

- `mode` (`string`, defaults to `OR`) The conditional mode to use when matching
  against multiple fields. Valid values are `OR` and `AND`.

#### `Finder`

- `finder` (`string`) The [find type](https://book.cakephp.org/3.0/en/orm/retrieving-data-and-resultsets.html#custom-finder-methods) to use.
  Use the `map` config array if you need to map your field to a finder key (`'to_field' => 'from_field'`). Use `options` config to pass additional config.

## Optional fields

Sometimes you might want to search your data based on two of three inputs in
your form. You can use the `filterEmpty` search option to ignore any empty fields.

```php
// PostsTable::initialize()
    $searchManager->value('author_id', [
        'filterEmpty' => true
    ]);
```

Be sure to allow empty in your search form, if you're using one.
```php
echo $this->Form->input('author_id', ['empty' => 'Pick an author']);
```

## Empty fields
In some cases, e.g. when posting checkboxes, the empty value is not `''` but `'0'`.
If you want to declare certain values as empty values and prevent the URL of
getting the query string attached for this "disabled" search field, you can set
`emptyValues` in the component:

```php
    $this->loadComponent('Search.Prg', [
        ...
        'emptyValues' => [
            'my_checkbox' => '0',
        ]
    ]);
```

This is needed for the "isSearch" work as expected.

## Custom filter

You can create your own filter by by creating a filter class under `src/Model/Filter`.

```php
<?php
namespace App\Model\Filter;

class MyCustomFilter extends \Search\Model\Filter\Base
{
    /**
     * @return bool
     */
    public function process()
    {
        // return false if you want to skip modifying the query based on some condition.

        // Use $this->query() to get query instance and modify it as needed.

        return true;
    }
}
```

After that you can use your filter as:

```php
$this->searchManager()->add('field', 'MyCustom');
```

## Persisting the Query String

Persisting the query string can be done with the `queryStringWhitelist` option.
The CakePHP's Paginator params `sort` and `direction` when filtering are kept
by default. Simply add all query strings that should be whitelisted.

## Blacklist Query String

You can use `queryStringBlacklist` option of `PrgComponent` to set an array of
form fields that should not end up in the query when extracting params from POST
request and redirecting.

## Filtering and Security component
When the Security component is activated for the whole controller, it should be disabled for the paginated actions:
```php
$this->Security->setConfig('unlockedActions', ['index']);
```

## Tips

### IDE compatibility
For auto-complete and type-hinting on the Search behavior method, using/running the [IdeHelper code completion](https://github.com/dereuromark/cakephp-ide-helper/blob/master/docs/CodeCompletion.md) is recommended.
