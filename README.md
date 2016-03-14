# kote-container
Small IoC container with dependency injection.

Get singleton instance of container using function:

```php
$container = container();
```

Bind class constructor:

```php
$container->bind('foo', Foo::class);
$container->bind(Bar::class);
$container->bind(Baz::class, BazImplementation::class);
```

Bind callable factory:

```php
$container->bind('factroy', function () {
  return new Factory();
});
```

Bind singleton:

```php
$container->bind('singleton', SingletonService::class);
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
$result = $container->invoke(function ($a, $b) {
  echo "$a, $b!"
}, ["a" => "Hello", "b" => "World"]);
```
