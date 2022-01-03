# nerd-container
[![Build Status](https://app.travis-ci.com/nerd-framework/nerd-container.svg?branch=master)](https://app.travis-ci.com/nerd-framework/nerd-container)
[![Coverage Status](https://coveralls.io/repos/github/nerd-framework/nerd-container/badge.svg?branch=master)](https://coveralls.io/github/nerd-framework/nerd-container?branch=master)
[![StyleCI](https://styleci.io/repos/53842620/shield?branch=master)](https://styleci.io/repos/53842620)

Container with Dependency Injection for Nerd Framework.

Get the container:

```php
$container = new \Nerd\Framework\Container\Container();
```

Bind class constructor:

```php
$container->bind('foo', Foo::class);
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
```

Invoke function, class method or class constructor with dependency injection:

```php
$result = $container->invoke(function (FooFactoryInterface $factory) {
  // $foo will be injected using parameter name
  // $other will be injected using Bar type hint
  return $factory->makeFoo();
});
```

Pass additional resources into invoke() method:

```php
$result = $container->invoke(function ($foo, $a, $b) {
  //
}, ["a" => "Hello", "b" => "World"]);
```

### Resource resolver
Not documented yet.
