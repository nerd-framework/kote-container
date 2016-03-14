# kote/container
Small IoC container with dependency injection.

Examples:

```php
// Bind services
$container = new \Kote\Container\Container();

$container->bind('foo', function () {
  return new \Foo();
};

$container->singleton('bar', function () {
  return new \Bar();
});

$container->bind('baz', \Baz::class);

// Bind scalar values
$container->bind('appToken', 'f82d3ae98304278766ca0a80379b1972');

// Get resources from container
$foo = $container->get('foo');
$bar = $container->get('bar');
$token = $container->get('appToken');

// Invoke function
$container->invoke(function ($foo, $bar, $appToken) {
  //
});

// Invoke class static method
$container->invoke([\MyClass::class, "myMethod"]);

// Invoke object method
$container->invoke([$obj, "method"]);

// Invoke class constructor
$container->invoke(\MyClass::class);
```
