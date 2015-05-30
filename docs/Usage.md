## Usage

* Add a search config method in your table
```php
use Search\Manager;

...

public function searchConfiguration()
{
    $search = new Manager($this);
    $search
    ->add('currency_id', 'value', [
        'field' => $this->alias() . '.currency_id'
    ])
    ->add('name', 'like', [
        'before' => true,
        'after' => true,
        'field' => [$this->alias() . '.name']
    ]);
    return $search;
}
```

* Add The Behavior in your initialize method
```php
public function initialize(array $config)
{
    ...
    $this->addBehavior('Search.Search');
    ...
}
```

* Example of index controller for a model Country
```php
public function index()
{
    $query = $this->Country
    ->find('search', $this->request->query)
    ->where(['name !=' => null])
    ->order(['Country.id' => 'asc'])
    ->contain([
        'Cities'
    ]);
    $this->set('countries', $this->paginate($query));
}
```

* Then add the component search in the necessary methods (for our example index)
```php
public function index()
{
    $this->loadComponent('Search.Prg');
}
```

* Instead, you can add this in your AppController to enable component in all index methods
```php
public function initialize()
{
    parent::initialize();
    if ($this->request->action === 'index'):
      $this->loadComponent('Search.Prg');
    endif;
}
```

## Filtering your data
Once you have completed all the setup you can now filter your data by passing
query params in your index method. Using the Country example given above, you
could filter your countries using the following.

`example.com/countries/index?name=gu`

Would filter your list of countries to any country with "gu" in the name. You
might chose to make a `get` form which posts the filter directly to the url, or
create links manually.
