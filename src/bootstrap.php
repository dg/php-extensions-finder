<?php

declare(strict_types=1);

$autoload = is_file(__DIR__ . '/../vendor/autoload.php')
	? __DIR__ . '/../vendor/autoload.php'
	: __DIR__ . '/../../../autoload.php';
if (@!include $autoload) {
	echo 'Install packages using `composer update`';
	exit(1);
}


set_exception_handler(function ($e) {
	echo "ERROR: {$e->getMessage()}\n";
	exit(1);
});


$cmd = new Nette\CommandLine\Parser(
	<<<'XX'
		Usage:
			php php-extensions-finder [<path>]

		XX,
	[
		'path' => [Nette\CommandLine\Parser::VALUE => getcwd()],
	],
);

$options = $cmd->parse();
if ($cmd->isEmpty()) {
	$cmd->help();
}

$finder = new DG\PhpExtensionsFinder\Finder;
$finder->go($options['path']);
