# Wordclass\Init

### ::autoloader()
Initialize the Wordclass autoloader.  
**Please note:** this autoloader is only needed when not using Composer, or in other cases when you need to manually activate autoloading.

#### Example
```php
require 'absolute/path/to/src/Init.php';
Wordclass\Init::autoloader();
```

### ::constants()
Define some useful constants, that make things shorter and/or less ambiguous.

#### Example
```php
Wordclass\Init::constants();

// Absolute paths to the template/stylesheet directory
echo TEMPLATE_PATH;
echo STYLESHEET_PATH;

// URI paths to the template/stylesheet directory
echo TEMPLATE_URI;
echo STYLESHEET_URI;
```