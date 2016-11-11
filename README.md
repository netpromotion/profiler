# [Profiler] with adapters for [Tracy] and [PSR Log]

This repository contains lightweight, very quick and easy to use [Profiler] with adapters for [Tracy] and [PSR Log].


## Usage

If you wish to profile a block of code, simply encapsulate it between `Profiler::start` and `Profiler::finish` calls.

```php
<?php // index.php

if (/* Is debug mode enabled? */) {
    Profiler::enable();
}

Profiler::start();
require(__DIR__ . "/required_file.php");
Profiler::finish();
```

```php
<?php // required_file.php

// If you wish to use default labels, call functions without parameters
Profiler::start(/* sprintf("%s#%s", __FILE__, __LINE__) */);
/* your code goes here */
Profiler::finish(/* sprintf("%s#%s", __FILE__, __LINE__) */);

// If you wish to use static labels, place label as first parameter
Profiler::start("static label");
/* your code goes here */
Profiler::finish("static label");

// If you wish to use dynamic labels, call functions like sprintf
Profiler::start(/* sprintf( */ "line %s", __LINE__ /* ) */);
/* your code goes here */
Profiler::finish(/* sprintf( */ "line %s", __LINE__ /* ) */);

// If you wish to create more detailed profiles, start new profile inside another one
Profiler::start("Profile 1");
    /* your code goes here */
    Profiler::start("Profile 1.1");
        Profiler::start("Profile 1.1.1");
            /* your code goes here */
        Profiler::finish("Profile 1.1.1");
        /* your code goes here */
        Profiler::start("Profile 1.1.2");
            /* your code goes here */
        Profiler::finish("Profile 1.1.2");
        /* your code goes here */
    Profiler::finish("Profile 1.1");
Profiler::finish("Profile 1");
```

If you wish to know more about [Profiler], please visit [Profiler's README.md].

### PSR Logger Adapter

PSR Logger Adapter is universal adapter and you must call it manually.

```php
<?php

use Netpromotion\Profiler\Adapter\PsrLoggerAdapter;

/** @var Psr\Log\LoggerInterface $logger */
$adapter = new PsrLoggerAdapter($logger);
/* your profiled code goes here */
$adapter->log(); // logs known profiles
```


## How to install

Run `composer require netpromotion/profiler` in your project directory.

![Adapter for Tracy](https://raw.githubusercontent.com/netpromotion/profiler/master/demo/tracy.png)


### [Nette]

Add extension `Netpromotion\Profiler\Extension\ProfilerNetteExtension` into your configuration, it is not necessary to call `Profiler::enable`.

```neon
extensions:
    profiler: Netpromotion\Profiler\Extension\ProfilerNetteExtension
```

If you wish to profile before the container is ready, call `Profiler::enable` manually.

#### Configuration

```neon
profiler:
    profile:
        createService: false  # or true
    bar:
        primaryValue: effective  # or absolute
        show:
            memoryUsageChart: true  # or false
            shortProfiles: true  # or false
            timeLines: true  # or false
```

There is a live demo available - run `make demo` and [click here](http://127.0.0.1:8080/nette/).


### [Lumen], pure PHP and everything else

Add panel `Netpromotion\Profiler\Adapter\TracyBarAdapter` to your bar via `Bar::addPanel` method manually or use [netpromotion/tracy-wrapper].

```php
tracy_wrap(function() {
    /* your code goes here */
}, [new TracyBarAdapter([
    "primaryValue" => "effective", // or "absolute"
    "show" => [
        "memoryUsageChart" => true, // or false
        "shortProfiles" => true, // or false
        "timeLines" => true // or false
    ]
])]);
```

There is a live demo available - run `make demo` and [click here](http://127.0.0.1:8080/lumen/).



[Profiler]:https://packagist.org/packages/petrknap/php-profiler
[Tracy]:https://tracy.nette.org/
[PSR Log]:https://github.com/php-fig/log
[Profiler's README.md]:https://github.com/petrknap/php-profiler/blob/master/README.md
[Nette]:https://nette.org/
[Lumen]:https://lumen.laravel.com/
[netpromotion/tracy-wrapper]:https://github.com/netpromotion/tracy-wrapper
