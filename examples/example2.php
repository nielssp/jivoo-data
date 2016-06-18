<?php

use Jivoo\Data\Database\DatabaseDefinitionBuilder;
use Jivoo\Data\Database\DatabaseSchema;
use Jivoo\Data\Database\Loader;
use Jivoo\Data\DataType;
use Jivoo\Data\DefinitionBuilder;
use Jivoo\Log\CallbackHandler;
use Jivoo\Log\Logger;

// Include Jivoo by using composer:
require '../vendor/autoload.php';

// Initialize database loader
$loader = new Loader();

class User {
    public static function getDefinition() {
        $def = new DefinitionBuilder();
        $def->addAutoIncrementId(); // Autoincrementing INT id
        $def->username = DataType::string(255); // Username VARCHAR(255)
        $def->password = DataType::string(255); // Password VARCHAR(255)
        $def->addtimeStamps(); // Timestamps: 'created' and 'updated'
        $def->addUnique('username', 'username'); // A unique index on the username field
        return $def;
    }
}

// Log database queries to output 
$logger = new Logger();
$logger->addHandler(new CallbackHandler(function (array $record) {
  if (isset($record['context']['query'])) {
    echo 'query: ' . $record['context']['query'] . PHP_EOL; 
  }
}));
$loader->setLogger($logger);

// Create schema for database using the above user table schema
$definition = new DatabaseDefinitionBuilder([
    'User' => User::getDefinition()
]);

// Connect to database
$db = $loader->connect(
    [
        'driver' => 'PdoMysql',
        'server' => 'localhost',
        'username' => 'jivoo',
        'database' => 'jivoo'
    ],
    $definition
);

echo '<pre>';

// Delete table if it exists
if ($db->User->exists()) {
  $db->User->drop();
}

// Create table
$db->User->create();

$schema = new DatabaseSchema($db);

// Insert a user (array style)
$schema->User->insert([
  'username' => 'root',
  'password' => 'secret',
  'created' => time(),
  'updated' => time()
]);

// Insert a user (active record style)
$user = $schema->User->create();
$user->username = 'guest';
$user->password = 'secret';
$user->created = time();
$user->updated = time();
$user->save();

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
