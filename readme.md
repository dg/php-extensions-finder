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
