PHP Extensions Finder for Composer
==================================

This tool finds which PHP extensions are required by source code.


Usage
-----

```
php-extensions-finder [<path>]
```

It generates result like:

```
{
	"require": {
		"ext-json": "*",
		"ext-tokenizer": "*",
		"ext-gd": "*",
		"ext-openssl": "*",
		"ext-iconv": "*",
		"ext-mbstring": "*"
	}
}
```


Installation
------------

It requires PHP 5.4.0 or newer.

Install it via Composer. This project is not meant to be run as a dependency, so install it as standalone:

```
composer create-project dg/php-extensions-finder
```

Or install it globally via:

```
composer global require dg/php-extensions-finder
```

and make sure your global vendor binaries directory is in [your `$PATH` environment variable](https://getcomposer.org/doc/03-cli.md#global).


Support Project
---------------

Do you like PHP Extensions Finder? Are you looking forward to the new features?

[![Donate](https://files.nette.org/icons/donation-1.svg?)](https://nette.org/make-donation?to=php-extensions-finder)
