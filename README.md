# [Profiler] with adapter for [Tracy]

This repository contains lightweight, very quick and easy to use [Profiler] with adapter for [Tracy].


## Usage

If you wish to profile block of code, simply encapsulate it between `Profiler::start` and `Profiler::finish` calls.

```php
Profiler::start();
// code to profile
Profiler::finish();
```

If you wish to known more about [Profiler], please visit [Profiler's README.md].


### [Nette]

Add extension `Netpromotion\Profiler\Extension\ProfilerNetteExtension` into your configuration. 

```neon
extensions:
    profiler: Netpromotion\Profiler\Extension\ProfilerNetteExtension
```

If you wish to profile before container is ready, call `ProfilerNetteExtension::enable` manually.

![Adapter for Tracy](https://raw.githubusercontent.com/netpromotion/profiler/master/demo/nette.png)

There is available live demo - run `make demo` and [click here](http://127.0.0.1:8080/nette/).


## How to install

Run `composer require netpromotion/profiler` or merge this JSON code with your project `composer.json` file manually and run `composer install`. Instead of `dev-master` you can use [one of released versions].

```json
{
    "require": {
        "netpromotion/profiler": "dev-master"
    }
}
```

Or manually clone this repository via `git clone https://github.com/netpromotion/profiler.git` or download [this repository as ZIP] and extract files into your project.


[Profiler]:https://packagist.org/packages/petrknap/php-profiler
[Tracy]:https://tracy.nette.org/
[Profiler's README.md]:https://github.com/petrknap/php-profiler/blob/master/README.md
[Nette]:https://nette.org/
[one of released versions]:https://github.com/netpromotion/profiler/releases
[this repository as ZIP]:https://github.com/netpromotion/profiler/archive/master.zip
