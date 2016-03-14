# kote-container
Small IoC container with dependency injection.

Examples:

```php
$container = new \Kote\Container\Container();

// Bind resource using constructor callback
$container->bind('foo', function () {
  return new Foo();
};

// Bind resource using class name
$container->bind('bar', Bar::class);

// Any data except callables and class names will be stored in the container as is
$container->bind('appToken', 'f82d3ae98304278766ca0a80379b1972');

// Bind singleton resource
$container->singleton('mySingleton', function () {
  return new Singleton();
});
$container->singleton('mySingleton', Singleton::class);

// Bind resource using class name as resource id
$container->bind(MyInterface::class, MyImplementation::class);

// Bind resource using it's own class name
$container->bind(MyService::class);

// Get resources from container
$foo = $container->get('foo');
$myService = $container->get(MyService::class);
$token = $container->get('appToken');

// Invoke function with resource injection
// Using argument names
$container->invoke(function ($foo, $bar, $appToken) {
  // All dependencies will be injected using its names
});

// Using type hinting
$container->invoke(function (MyInterface $first, MyService $service, $appToken) {
  // Firstly, container will search resource using type hint. If nothing found it will search
  // resource using argument name
});

// Invoke class static method
$container->invoke([\MyClass::class, "myMethod"]);

// Invoke object method
$container->invoke([$obj, "method"]);

// Invoke class constructor
$container->invoke(MyController::class);
```

If you need global container you can always access it using following function:

```php
$foo = container()->get('foo');
// or
$foo = container('foo');
```

Also you can pass array with contextual resources:

```php
$context = [
  'hello' => 'World',
  'test' => 'True',
  OtherFoo::class
];
$container->invoke(function ($hello, $test, $foo, OtherFoo $otherFoo) {
  // 
}, $context);
```
