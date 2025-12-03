# Additional documentation

## Using a Form class for your search form

While not mandatory, you can configure the `SearchComponent` to use a
[Form class](https://book.cakephp.org/5/en/core-libraries/form.html) for your search form.

```php
$this->loadComponent('Search.Search', [
    'formClass' => 'MySearch', // Or use the FQCN: App\Form\MySearch::class
]);
```

The search component will auto set a `$searchForm` view variable containing the
form instance, for your search actions. You can use the form in your template as:

```php
echo $this->Form->create($searchForm, ['valueSources' => ['query', 'data']]);
// Add your search fields here
```

Using a form class has the advantage of being able to define validation rules
and form field types. Validation will be done when the form is submitted
and in case of validation errors the component will not perform a redirect with
the query params, but instead the view will be rendered with validation errors.

## Persisting the Query String

Persisting the query string can be done with the `queryStringWhitelist` option.
The CakePHP's Paginator params `sort` and `direction` when filtering are kept
by default. Simply add all query strings that should be whitelisted.

## Blacklist Query String

You can use `queryStringBlacklist` option of `SearchComponent` to set an array of
form fields that should not end up in the query when extracting params from POST
request and redirecting.

## Emptiness based on more than one field.
If you need to determine `emptyValues` dynamically or based on multiple fields
(e.g. price range min/max), you can use closures for it and pass this to the `SearchComponent` config:
```php
$checkEmpty = function ($value, array $params) use (Price $minPrice, Price $maxPrice): bool {
    $minValue = (int)$minPrice->price;
    $maxValue = (int)ceil((float)$maxPrice->price);

    if (empty($params['price_min']) || empty($params['price_max'])) {
        return true;
    }

    if ((string)$minValue === $params['price_min'] && (string)$maxValue === $params['price_max']) {
        return true;
    }

    return false;
};
$this->Search->setConfig('emptyValues', [
    'price_min' => $checkEmpty,
    'price_max' => $checkEmpty,
]);
```
It will evaluate the two fields together.

## Filtering and FormProtection component
When the FormProtection component is activated for the whole controller, it should be disabled for the paginated actions:
```php
$this->FormProtection->setConfig('unlockedActions', ['index']);
```

## Bake Filters

With the `filter_collection` bake task, you can generate filter collection classes easily.

## Tips

### IDE compatibility
For auto-complete and type-hinting on the Search behavior method, using/running the [IdeHelper code completion](https://github.com/dereuromark/cakephp-ide-helper/blob/master/docs/CodeCompletion.md) is recommended.

### Additional Resources
For more complex callbacks with custom finders see [Tags plugin docs](https://github.com/dereuromark/cakephp-tags/tree/master/docs#searchfilter).
