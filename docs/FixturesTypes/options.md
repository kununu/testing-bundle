# FixturesAwareTestCase Options

The methods to load fixtures on [FixturesAwareTestCase](/src/Test/FixturesAwareTestCase.php) need to receive a parameter `$options`.

## OptionsInterface

That object must implement the interface [OptionsInterface](/src/Test/Options/OptionsInterface.php) which defines the following methods:

```php
public function append(): bool;

public function clear(): bool;
```

###### append

If this method returns `false` then the fixtures storage will be purged before loading your fixtures

###### clear

If this method returns `true` then it will clear any previous loaded fixtures classes

## Options

The class [Options](/src/Test/Options/Options.php) is provided, and it implements the `OptionsInterface` and also provides a builder pattern.

```php
use Kununu\TestingBundle\Test\Options\Options;

// Create object with default values:
//
// append => false;
// clear => true

$options = Options::create();

// Build with "append" set to true
$options = Options::create()->withAppend();

// Build with "append" set to false
$options = Options::create()->withoutAppend();

// Build with "clear" set to true
$options = Options::create()->withClear();

// Build with "clear" set to false
$options = Options::create()->withoutClear();

// Can be mixed to achieve your desired configuration
$options = Options::create()->withoutClear()->withAppend();

```

## DbOptionsInterface

To load fixtures with database connections (`loadDbFixtures`) it is required to pass a more specialized instance of options.

That object must implement the interface [DbOptionsInterface](/src/Test/Options/DbOptionsInterface.php) which is an extension of `OptionsInterface` (thus having all the same methods) and adds the following method:

```php
public function transactional(): bool;
```

###### transactional

If this method returns `true` then it will use a transactional executor, otherwise the fixtures will be loaded without database transactions.

## DbOptions

The class [DbOptions](/src/Test/Options/DbOptions.php) is provided, and it implements the `DbOptionsInterface` and also provides a builder pattern.

It extends the `Options` class so all the methods available there are still in this class.

```php
use Kununu\TestingBundle\Test\Options\DbOptions;


// Create object with default values:
//
// append => false;
// clear => true
// transactional => true
$options = DbOptions::create();

// Create object with default values (non-transactional)
//
// append => false;
// clear => true
// transactional => false
$nonTransactionalOptions = DbOptions::createNonTransactional();

// Build with "transactional" set to true
$options = DbOptions::create()->withTransactional();

// Build with "transactional" set to false
$options = DbOptions::create()->withoutTransactional();

// All methods from parent class still available and can be mixed to achieve
// your desired configuration
$options = DbOptions::create()->withoutClear()->withAppend()->withoutTransactional();
```
