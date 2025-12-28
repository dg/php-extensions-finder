<?php

declare(strict_types=1);

namespace DG\PhpExtensionsFinder;

use Nette;


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

$finder = new Finder;
$list = $finder->scan($options['path']);

$reporter = new Reporter($list);
echo $reporter->generateReport();

$composer = $reporter->generateComposerJson();
echo "\nComposer\n--------\n", json_encode($composer, JSON_PRETTY_PRINT);

$phpIni = $reporter->generatePhpIni();
echo "\n\nphp.ini\n-------\n", $phpIni;
