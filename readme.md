PHP Extensions Finder for Composer
==================================

This tool finds which PHP extensions are required by source code.

It requires PHP 5.4.0 or newer. The best way how to install it is to use Composer:

```
composer create-project dg/php-extensions-finder
```

Usage:

```
php php-extensions-finder [<path>]
```

Or install globally via:
```
composer global require db/php-extensions-finder
```

And use via:
```
php-extensions-finder [<path>]
```
Make sure your global vendor binaries directory is in your `$PATH` environment variable. (https://getcomposer.org/doc/03-cli.md#global)


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
