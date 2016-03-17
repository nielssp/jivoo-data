<?php
use Jivoo\Store\Document;
use Jivoo\Data\DataType;
use Jivoo\Data\Database\DatabaseSchemaBuilder;
use Jivoo\Data\Database\SchemaBuilder;
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
    'database' => 'jivoo',
    'tablePrefix' => 'test_'
  )
)));

// Schema for a a user table
class UserSchema extends SchemaBuilder {
  protected function createSchema() {
    $this->addAutoIncrementId(); // Autoincrementing INT id
    $this->username = DataType::string(255); // Username VARCHAR(255)
    $this->password = DataType::string(255); // Password VARCHAR(255)
    $this->addtimeStamps(); // Timestamps: 'created' and 'updated'
    $this->addUnique('username', 'username'); // A unique index on the username field
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
$schema = new DatabaseSchemaBuilder(array(new UserSchema));

// Connect to "default":
$db = $loader->connect('default', $schema);

echo '<pre>';

// Delete table if it exists
if ($db->tableExists('User')) {
  $db->dropTable('User');
}

// Create table
$db->createTable('User');

// Insert a user (array style)
$db->User->insert(array(
  'username' => 'root',
  'password' => 'secret',
  'created' => time(),
  'updated' => time()
));

// Insert a user (active record style)
$user = $db->User->create();
$user->username = 'guest';
$user->password = 'secret';
$user->created = time();
$user->updated = time();
$user->save();

// Get data for root user:
print_r($db->User->where('username = %s', 'root')->first()->getData());

// List names of users created after 2015-01-01
$users = $db->User
  ->where('created > %d', '2015-01-01')  // Converts date using strtotime()
  ->orderBy('created');

foreach ($users as $user) {
  echo h($user->username) . PHP_EOL;
}

echo '</pre>';
