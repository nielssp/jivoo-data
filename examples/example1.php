<?php
// Example: Using dynamic table definitions

use Jivoo\Store\Document;
use Jivoo\Data\Database\Loader;
use Jivoo\Log\Logger;
use Jivoo\Log\CallbackHandler;

// Include Jivoo by using composer:
require '../vendor/autoload.php';

// Initialize database loader with connection settings for "default" database:
$loader = new Loader(new Document(array(
  'default' => array(
    'driver' => 'PdoMysql',
    'server' => 'localhost',
    'username' => 'jivoo',
    'database' => 'jivoo'
  )
)));

// Log database queries to output 
$logger = new Logger();
$logger->addHandler(new CallbackHandler(function (array $record) {
  if (isset($record['context']['query'])) {
    echo 'query: ' . $record['context']['query'] . PHP_EOL; 
  }
}));
$loader->setLogger($logger);

// Connect to "default":
$db = new \Jivoo\Data\Database\DatabaseSchema($loader->connect('default'));

echo '<pre>';

// Get data for root user:
print_r($db->User->where('username = %s', 'root')->first()->getData());

// List names of users created after 2015-01-01
$users = $db->User
  ->where('created > %d', '2015-01-01')  // Converts date using strtotime()
  ->orderBy('created');

foreach ($users as $user) {
  echo $user->username . PHP_EOL;
}

echo '</pre>';