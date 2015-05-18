<?php
use Cake\Core\Plugin;

$findRoot = function ($root) {
	do {
		$lastRoot = $root;
		$root = dirname($root);
		if (is_dir($root . '/vendor/cakephp/cakephp')) {
			return $root;
		}
	} while ($root !== $lastRoot);
	throw new \Exception('Cannot find the root of the application, unable to run tests');
};

$root = $findRoot(__FILE__);
unset($findRoot);
chdir($root);
if (file_exists($root . '/config/bootstrap.php')) {
	//require $root . '/config/bootstrap.php';
	//return;
}
require $root . '/vendor/cakephp/cakephp/tests/bootstrap.php';
$loader = require $root . '/vendor/autoload.php';

$loader->setPsr4('Cake\\', './vendor/cakephp/cakephp/src');
$loader->setPsr4('Cake\Test\\', './vendor/cakephp/cakephp/tests');

\Cake\Core\Configure::write('EmailTransport', [
	'default' => [
		'className' => 'Mail',
		// The following keys are used in SMTP transports
		'host' => 'localhost',
		'port' => 25,
		'timeout' => 30,
		'username' => 'user',
		'password' => 'secret',
		'client' => null,
		'tls' => null,
	],
]);
\Cake\Core\Configure::write('Email', [
	'default' => [
		'transport' => 'default',
		'from' => 'you@localhost',
		//'charset' => 'utf-8',
		//'headerCharset' => 'utf-8',
	],
]);

Plugin::load('Search', [
	'path' => dirname(dirname(__FILE__)) . DS,
	'autoload' => true,
	'bootstrap' => false
]);
