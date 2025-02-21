# Filters

The Search plugin comes with a set of predefined search filters that allow you to
easily create the search results you need. Use:

----------

`Value` to limit results to exact matches

```php
// WHERE category_id = $paramFromRequest
$searchManager->value('category_id');
```

----------

`Like` to produce results containing the search query (`LIKE` or `ILIKE`)

```php
// WHERE name LIKE $paramFromRequest
$searchManager->like('name');
```

----------

`Boolean` to limit results by truthy (by default: 1, true, '1', 'true', 'yes', 'on')
and falsy (by default: 0, false, '0', 'false', 'no', 'off') values which are
passed down to the ORM as true/1 or false/0 or ignored when being neither truthy or falsy.

```php
// WHERE is_active = 1
// or
// WHERE is_active = 0
$searchManager->boolean('is_active');
```

----------

`Exists` to produce results for existing (non-empty) column content.

```php
// WHERE nullable_field IS NOT NULL
$searchManager->exists('nullable_field');
```

----------

`Finder` to produce results using a [(custom)](https://book.cakephp.org/5/en/orm/retrieving-data-and-resultsets.html#custom-find-methods) finder

```php
// executes the findMyFinder() method in your table class
$searchManager->finder('myFinder');
```

----------

`Compare` to produce results requiring operator comparison (`>`, `<`, `>=` and `<=` | default: `>=`)

```php
// WHERE amount >= $paramFromRequest
$searchManager->compare('amount');
```

----------

`Callback` to produce results using your own custom callable function, it
should return bool to specify `isSearch()` (useful when using with `alwaysRun` enabled)

```php
// Completely up to your code inside the callback
$searchManager->callback('category_id', [
        'callback' => function (\Cake\ORM\Query\SelectQuery $query, array $args,  \Search\Model\Filter\Base $filter) {
            // $args contains the values given in the request
            // $query->where([]);
            return true;
        }
```

----------

## Options

### All filters

The following options are supported by all filters.

- `fields` (`string`, defaults to the name passed to the first argument of the
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
        'beforeProcess' => function (\Cake\ORM\Query\SelectQuery $query, array $args, \Search\Model\Filter\Base $filter) {
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

### `Boolean`

- `mode` (`string`, defaults to `OR`) The conditional mode to use when matching
  against multiple fields. Valid values are `OR` and `AND`.

### `Exists`

- `mode` (`string`, defaults to `OR`) The conditional mode to use when matching
  against multiple fields. Valid values are `OR` and `AND`.
- `nullValue` (`string` or `null`, defaults to `null`). Can be used for non-nullable columns.
  Set it to an empty string there to check via `=`/`!=` instead of `IS NULL`/`IS NOT NULL`.

### `Compare`

- `operator` (`string`, defaults to `>=`) The operator to use for comparison. Valid
  values are `>=`, `<=`, `>` and `<`.

- `mode` (`string`, defaults to `AND`) The conditional mode to use when matching
  against multiple fields. Valid values are `OR` and `AND`.

### `Like`

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

### `Value`

- `multiValue` (`bool`, defaults to `false`) Defines whether the filter accepts
  multiple values. If disabled, and multiple values are being passed, the filter
  will fall back to using the default value defined by the `defaultValue` option.

- `multiValueSeparator` (`string`, defaults to `null`) Defines whether the filter should
  auto-tokenize multiple values using a specific separator string. If disabled, the data
  must be an in form of an array.

- `mode` (`string`, defaults to `OR`) The conditional mode to use when matching
  against multiple fields. Valid values are `OR` and `AND`.

- `negationChar` (`string`, defaults to `null`) An alternative to `multiValue`,
  especially if you have a lot of values. The filter accepts any string, but it
  should ideally be a single and unique char as prefix for your search value.
  E.g. `!` for string values or `-` for numeric values. If enabled, the filter
  will negate the expression for this value.

### `Finder`

- `finder` (`string`, defaults to the filter name) The [find type](https://book.cakephp.org/4/en/orm/retrieving-data-and-resultsets.html#custom-finder-methods) to use.

- `map` (`array`, defaults to `[]`) Config array if you need to map your field
  to a finder key (`'to_field' => 'from_field'`).

- `options` (`array`, defaults to `[]`) Additional options to pass to the finder.

- `cast` (`array`, defaults to `[]`) Additional casts to be used on the (mapped
  field values. You can use `'int'`, `'bool'`, `'float'`, etc as strings. You can also
  use callable functions like `function ($value) { ... }` for more complex scenarios.

## Filtering by `belongsToMany` and `hasMany` associations

If you want to filter values related to a `belongsToMany` or `hasMany` association,
your best option is to use a `callback` like so:

```php
$searchManager
    ->callback('category_id', [
        'callback' => function (\Cake\ORM\Query\SelectQuery $query, array $args,  \Search\Model\Filter\Base $filter) {
            $query
                ->innerJoinWith('Categories', function (\Cake\ORM\Query\SelectQuery $query) use ($args) {
                    return $query->where(['Categories.id IN' => $args['category_id']]);
                })
                ->group('Products.id');

            return true;
        }
    ]);
```

Where `$args['category_id']` is an array of IDs like `['1','2']`

## Optional fields

Sometimes you might want to search your data based on two of three inputs in
your form. You can use the `filterEmpty` search option to ignore any empty fields.

```php
// PostsTable::initialize()
    $searchManager->value('author_id', [
        'filterEmpty' => true,
    ]);
```

Be sure to allow empty in your search form, if you're using one.
```php
echo $this->Form->control('author_id', ['empty' => 'Pick an author']);
```

## Empty fields
In some cases, e.g. when posting checkboxes, the empty value is not `''` but `'0'`.
If you want to declare certain values as empty values and prevent the URL of
getting the query string attached for this "disabled" search field, you can set
`emptyValues` in the component:

```php
    $this->loadComponent('Search.Search', [
        ...
        'emptyValues' => [
            'my_checkbox' => '0',
        ],
    ]);
```

This is needed for the "isSearch" work as expected.

## Custom filter

You can create your own filter by by creating a filter class under `src/Model/Filter`.

```php
<?php
declare(strict_types=1);

namespace App\Model\Filter;

class MyCustomFilter extends \Search\Model\Filter\Base
{
    /**
     * @return bool
     */
    public function process(): bool
    {
        // return false if you want to skip modifying the query based on some condition.

        // Use $this->getQuery() to get query instance and modify it as needed.

        return true;
    }
}
```

After that you can use your filter as:

```php
$this->searchManager()->add('name', 'MyCustom');
```

