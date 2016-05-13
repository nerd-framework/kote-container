# kote-container
Small IoC container with dependency injection.

Get the container:

```php
$container = new \Kote\Container\Container();
// or singleton
$container = app();
```

Bind class constructor:

```php
$container->bind('foo', Foo::class);
$container->bind(Bar::class);
$container->bind(Baz::class, BazImplementation::class);
```

Bind callable factory:

```php
$container->bind('factory', function () {
  return new Factory();
});
```

Bind singleton:

```php
$container->singleton('single', SingletonService::class);
```

Retrieve resources from container:

```php
$foo = $container->get('foo');
$baz = $container->get(Bar::class);
```

Invoke function, class method or class constructor with dependency injection:

```php
$result = $container->invoke(function ($foo, Bar $other) {
  // $foo will be injected using parameter name
  // $other will be injected using Bar type hint
});
```

Pass additional resources into invoke() method:

```php
$result = $container->invoke(function ($foo, $a, $b) {
  //
}, ["a" => "Hello", "b" => "World"]);
```

Add resource resolver:

```php
$container->addResolver(function ($id, $type, $container) {
    return new $type($id);
}, SomeClass::class);
```

