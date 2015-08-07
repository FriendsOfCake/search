# CakePHP Search

Search provides a search module for CakePHP applications.

## Requirements

The master branch has the following requirements:

* CakePHP 3.0.0 or greater.

## Installation

* Install the plugin with composer from your CakePHP Project's ROOT directory
(where composer.json file is located)
```sh
php composer.phar require friendsofcake/search "dev-master"
```

* Load the plugin by adding following to your `config/bootstrap.php`
```php
Plugin::load('Search');
```

or running command
```sh
./bin/cake plugin load Search
```

## Usage

* Add a search config method in your table
```php
use Search\Manager;

...

public function searchConfiguration()
{
    $search = new Manager($this);
    $search
    ->value('author_id', [
        'field' => $this->aliasField('author_id')
    ])
    ->like('q', [
        'before' => true,
        'after' => true,
        'field' => [$this->aliasField('title'), $this->aliasField('title')]
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

* Example of index controller for a model `Article`
```php
public function index()
{
    $query = $this->Countries
        ->find('search', $this->Articles->filterParams($this->request->query))
        ->where(['title !=' => null])
        ->order(['Article.id' => 'asc'])
        ->contain([
            'Comments'
        ]);
    $this->set('articles', $this->paginate($query));
}
```

The `search` finder and the `filterParams()` method are dynamically provided by the
`Search` behavior.

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
    if ($this->request->action === 'index') {
        $this->loadComponent('Search.Prg');
    }
}
```

## Filtering your data
Once you have completed all the setup you can now filter your data by passing
query params in your index method. Using the Article example given above, you
could filter your articles using the following.

`example.com/articles?q=cakephp`

Would filter your list of articles to any article with "cakephp" in the `title`
or `content` field. You might choose to use the helper to make a `get` form
that posts the filter directly to the url, or create links manually.

## Using the helper
The plugin includes a helper `SearchForm` with a function `form()` that you can
use in your application to automagically generate a form based on the
`searchConfiguration()` set up in your table.

First, load the helper in your view (either `app/src/View/AppView.php` or some
custom view):

```php
$this->loadHelper('Search.SearchForm');
```

Then you can use the helper in your template
```php
<?= $this->SearchForm->form('Articles') ?>
```
(or if you want to be lazy and copy-paste the same thing in all of your templates, you can call it like this)
```php
<?= $this->SearchForm->form($this->request->params['controller']) ?>
```

The helper parses your table's `searchConfiguration` and generates a form (this
example is what would be rendered from the `Articles` table example above)
```html
<h3>Search</h3>
<form action="/admin/articles" accept-charset="utf-8" method="post" autocomplete="on">
    <div style="display:none;">
        <input type="hidden" value="POST" name="_method" autocomplete="on">
    </div>
    <div class="input text">
        <label for="id">Id</label>
        <input type="text" id="id" validate="" field="Articles.id" name="id" autocomplete="on">
    </div>
    <div class="input select">
        <label for="author-id">Author</label>
        <input type="hidden" value="" name="author_id" autocomplete="on">
        <select id="author-id" validate="" field="Articles.author_id" name="author_id">
            <option value="1">Author 1</option>
            <option value="2">Author 2</option>
            ...
        </select>
    </div>
    <div class="submit">
        <input type="submit" value="Submit" autocomplete="on">
    </div>
</form>
```

You can also add custom form input attributes to your `searchConfiguration`
fields
```php
->value('author_id', [
    'field' => $this->aliasField('author_id'),
    'multiple' => true, // creates a select where multiple authors can be selected for filtering
])
```

```php
->like('email', [
    'before' => true,
    'after' => true,
    'type' => 'text', // forces this email field to use a text type instead of the default email (so you don't have to enter a full valid email address)
    'field' => $this->aliasField('email')
])
```

Anything valid for `$options` to be passed into [`$this->Form->input()`](http://book.cakephp.org/3.0/en/views/helpers/form.html#creating-form-inputs) works here as well.
