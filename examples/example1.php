<?php
// Example 1: Using dynamic table definitions

use Jivoo\Data\Database\DatabaseDefinitionBuilder;
use Jivoo\Data\Database\DatabaseSchema;
use Jivoo\Data\Database\Loader;
use Jivoo\Data\DefinitionBuilder;
use Jivoo\Log\CallbackHandler;
use Jivoo\Log\Logger;

// Include Jivoo by using composer:
require '../vendor/autoload.php';

// Initialize database loader
$loader = new Loader();

// Log database queries to output 
$logger = new Logger();
$logger->addHandler(new CallbackHandler(function (array $record) {
  if (isset($record['context']['query'])) {
    echo 'query: ' . $record['context']['query'] . PHP_EOL; 
  }
}));
$loader->setLogger($logger);

// Initialize definition for User-table
$definition = new DatabaseDefinitionBuilder();
$definition->addDefinition('User', DefinitionBuilder::auto(['username', 'created']));

// Connect to database
$schema = new DatabaseSchema($loader->connect(
    [
        'driver' => 'PdoMysql',
        'server' => 'localhost',
        'username' => 'jivoo',
        'database' => 'jivoo'
    ],
    $definition
));


echo '<pre>';

// Get data for root user:
print_r($schema->User->where('username = %s', 'root')->first()->getData());

// List names of users created after 2015-01-01
$users = $schema->User
  ->where('created > %d', '2015-01-01')  // Converts date using strtotime()
  ->orderBy('created');

foreach ($users as $user) {
  echo $user->username . PHP_EOL;
}

echo '</pre>';