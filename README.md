[![Build Status](https://scrutinizer-ci.com/g/scones/resque-logger/badges/build.png?b=master)](https://scrutinizer-ci.com/g/scones/resque-logger/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/scones/resque-logger/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/scones/resque-logger/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/scones/resque-logger/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/scones/resque-logger/?branch=master)

# Resque Logger

This is a plugin to log all events from scones/resque via a psr logger.

## Install

In most cases it should suffice to just install it via composer.

`composer require scones/resque-logger "*@stable"`

## Usage

You will need to have a psr-14 listener provider configured ($listenerProvider).
You will need to have a psr-14 task processor configured (using the same listener provider, added to resque and worker and job).
You will need to have a psr/log logger configured ($logger).

Having that, it will boil down to:

```php
$resqueLogger = new ResqueLogger($logger, $listenerProvider);
$resqueLogger->register();
```

You can also always inspect the examples: https://github.com/scones/resque-examples
