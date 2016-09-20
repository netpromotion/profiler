# [Profiler] with adapter for [Tracy]

This repository contains lightweight, very quick and easy to use [Profiler] with adapter for [Tracy].


## Usage

If you wish to profile a block of code, simply encapsulate it between `Profiler::start` and `Profiler::finish` calls.

```php
Profiler::start();
// code to profile
Profiler::finish();
```

If you wish to know more about [Profiler], please visit [Profiler's README.md].


## How to install

Run `composer require netpromotion/profiler` in your project directory.


### [Nette]

Add extension `Netpromotion\Profiler\Extension\ProfilerNetteExtension` into your configuration. 

```neon
extensions:
    profiler: Netpromotion\Profiler\Extension\ProfilerNetteExtension
```

If you wish to profile before the container is ready, call `ProfilerNetteExtension::enable` manually.

![Adapter for Tracy](https://raw.githubusercontent.com/netpromotion/profiler/master/demo/nette.png)

There is a live demo available - run `make demo` and [click here](http://127.0.0.1:8080/nette/).



[Profiler]:https://packagist.org/packages/petrknap/php-profiler
[Tracy]:https://tracy.nette.org/
[Profiler's README.md]:https://github.com/petrknap/php-profiler/blob/master/README.md
[Nette]:https://nette.org/
