Mongroove
========
Mongroove is an ODM (Object Document Mapper) for MongoDB.
This ODM is compatible with PHP 5.2 and after and it respect PEAR class naming convention.

__Warning this is an alpha version, do not use in a production environment !__

Simple usage:
--------
__Autoload and initialise the manager__
```php
require_once 'includes/Mongroove.php';
spl_autoload_register(array('Mongroove', 'autoload'));

$manager = Mongroove_Manager::getInstance();
```

__Open a new connection with the database__
```php
$manager->openConnection('host=localhost:27017;dbname=admin');
```

__Retrieve a document__
```php
$cursor = Mongroove::getCollection('users')->createQuery()->execute();
print_r($cursor->toArray());
```
