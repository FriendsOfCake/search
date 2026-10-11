# CakePHP Search

[![Build Status](https://img.shields.io/github/actions/workflow/status/FriendsOfCake/search/ci.yml?branch=master&style=flat-square)](https://github.com/FriendsOfCake/search/actions?query=workflow%3ACI+branch%3Amaster)
[![Coverage Status](https://img.shields.io/codecov/c/github/FriendsOfCake/search.svg?style=flat-square)](https://codecov.io/github/FriendsOfCake/search)
[![Latest Stable Version](https://poser.pugx.org/friendsofcake/search/v/stable.svg)](https://packagist.org/packages/friendsofcake/search)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofcake/search.svg?style=flat-square)](https://packagist.org/packages/friendsofcake/search)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](https://packagist.org/packages/friendsofcake/search)

Search provides a simple interface to create paginate-able filters for your CakePHP application.

This branch is for CakePHP **6.x** and requires **PHP 8.5 or newer**. For details see [version map](https://github.com/FriendsOfCake/search/wiki#cakephp-version-map).

## Upgrading from CakePHP 5

Call behavior methods through `$table->getBehavior('Search')`, for example
`$table->getBehavior('Search')->searchManager()` or
`$table->getBehavior('Search')->isSearch()`. The `find('search', search: $params)`
API is unchanged.

Custom subclasses must remove leading underscores from overridden protected
methods and property references (for example, `$_defaultConfig` becomes
`$defaultConfig`). Fluent methods now return `static`.

The filter collection generator requires Bake 4 and TwigView 3. Its command
methods now use CakePHP 6's `$this->args` and `$this->io` properties.

## Installation

* Install the plugin with composer from your CakePHP project's ROOT directory
(where composer.json file is located)

```sh
php composer.phar require friendsofcake/search
```

* Load the plugin by running command

```sh
bin/cake plugin load Search
```

## Documentation
See [Documentation](/docs) section.
