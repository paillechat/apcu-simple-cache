# Enum


[![Build Status](https://scrutinizer-ci.com/g/paillechat/apcu-simple-cache/badges/build.png?b=master)](https://scrutinizer-ci.com/g/paillechat/apcu-simple-cache/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/paillechat/apcu-simple-cache/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/paillechat/apcu-simple-cache/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/paillechat/apcu-simple-cache/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/paillechat/apcu-simple-cache/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/paillechat/apcu-simple-cache/version.png)](https://packagist.org/packages/paillechat/apcu-simple-cache)
[![Total Downloads](https://poser.pugx.org/paillechat/apcu-simple-cache/downloads.png)](https://packagist.org/packages/paillechat/apcu-simple-cache)

A PHP 7+ enumeration library.

## Why?
SplEnum not supported in php 7.

## Installation
```
composer require paillechat/apcu-simple-cache
```

## Usage
```php
$ttl = 1;
$cache = new ApcuCache();

$cache->set('foo', 'bar', $ttl);
$foo = $cache->get('foo');
```
