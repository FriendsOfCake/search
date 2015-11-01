# CakePHP Search


[![Build Status](https://img.shields.io/travis/FriendsOfCake/search/master.svg?style=flat-square)](https://travis-ci.org/FriendsOfCake/search)
[![Coverage Status](https://img.shields.io/coveralls/FriendsOfCake/search/master.svg?style=flat-square)](https://coveralls.io/r/FriendsOfCake/search?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofcake/search.svg?style=flat-square)](https://packagist.org/packages/friendsofcake/search)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](https://packagist.org/packages/friendsofcake/search)

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
The plugin has three main parts which you will need to configure and include in
your application.

### Table class
There are three tasks during setup in your table class. Firstly you must add a
`use` statement for the `Search\Manager`. Next you need to attach the `Search`
behaviour to your table class. Lastly you must add a `searchConfiguration`
method to your table class so that you can configure how the search will work.

```php
use Search\Manager;

class ExampleTable extends Table {

	public function initialize(array $config)
	{
		// Add the behaviour to your table
		$this->addBehavior('Search.Search');
	}

	// Configure how you want the search plugin to work with this table class
    public function searchConfiguration()
    {
        $search = new Manager($this)
            ->value('author_id', [
                'field' => $this->aliasField('author_id')
            ])
            // Here we will alias the 'q' query param to search the `Articles.title`
            // field and the `Articles.content` field, using a LIKE match, with `%`
            // both before and after.
            ->like('q', [
                'before' => true,
                'after' => true,
                'field' => [$this->aliasField('title'), $this->aliasField('content')]
            ]);

        return $search;
    }
```

### Controller class
In order for the Search plugin to work it will need to process the query params
which are passed in your url. So you will need to edit your `index` method to
accomodate this.

```php
public function index()
{
    $query = $this->Articles
    	// Use the plugins 'search' custom finder and pass in the
    	// processed query params
        ->find('search', $this->Articles->filterParams($this->request->query))
        // You can add extra things to the query if you need to
        ->contain(['Comments'])
        ->where(['title IS NOT' => null]);

    $this->set('articles', $this->paginate($query));
}
```

The `search` finder and the `filterParams()` method are dynamically provided by
the `Search` behavior.

### Component
Then add the Search Prg component to the necessary methods in your controller.

:warning: Make sure,
* That you add this in the controller's `initialize` method.
* That you only add this to methods which are using search, such as your `index` method.

```php
public function initialize()
{
    parent::initialize();

    if ($this->request->action === 'index') {
        $this->loadComponent('Search.Prg');
    }
}
```

The `Search.Prg` component will allow your filtering forms to be populated using
the data in the query params. It uses the [Post, redirect, get pattern](https://en.wikipedia.org/wiki/Post/Redirect/Get).

## Filtering your data
Once you have completed all the setup you can now filter your data by passing
query params in your index method. Using the `Article` example given above, you
could filter your articles using the following.

`example.com/articles?q=cakephp`

Would filter your list of articles to any article with "cakephp" in the `title`
or `content` field. You might choose to make a `get` form which posts the filter
directly to the url, but if you're using the `Search.Prg` component, you'll want
to use `POST`.

### Creating your form
In most cases you'll want to add a form to your index view which will search
your data.

```php
    echo $this->Form->create();
    // You'll need to populate $authors in the template from your controller
    echo $this->Form->input('author_id');
    // Match the search param in your table configuration
    echo $this->Form->input('q');
    echo $this->Form->button('Filter', ['type' => 'submit']);
    echo $this->Html->link('Reset', ['action' => 'index']);
    echo $this->Form->end();
```

If you are using the `Search.Prg` component the forms current values will be
populated from the query params.

## Types

The Search plugin comes with a set of predefined search filters that allow you to
easily create the search results you need. Use:

- ``value`` to limit results to exact matches
- ``like`` to produce results containing the search query (``LIKE``)
- ``finder`` to produce results using a [(custom)](http://book.cakephp.org/3.0/en/orm/retrieving-data-and-resultsets.html#custom-find-methods) finder
- ``compare`` to produce results requiring operator comparison (
    ``>``, ``<``, ``>=`` and ``<=``)
- ``callback`` to produce results using your own custom callable function

## Optional fields
Sometimes you might want to search your data based on two of three inputs in
your form. You can use the `filterEmpty` search option to ignore any empty fields.

```php
// ExampleTable.php
// Inside your searchConfiguration() method
    $search->value('author_id', [
        'filterEmpty' => true
    ]);
```

Be sure to allow empty in your search form, if you're using one.
```php
echo $this->Form->input('author_id', ['empty' => 'Pick an author']);
```
