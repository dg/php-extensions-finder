<?php

require is_file(__DIR__ . '/../vendor/autoload.php')
	? __DIR__ . '/../vendor/autoload.php'
	: __DIR__ . '/../../../autoload.php';


set_exception_handler(function($e) {
	echo "ERROR: {$e->getMessage()}\n";
	exit(1);
});


$cmd = new Nette\CommandLine\Parser(<<<XX
Usage:
	php php-extensions-finder [<path>]

XX
, [
	'path' => [Nette\CommandLine\Parser::VALUE => getcwd()],
]);

$options = $cmd->parse();
if ($cmd->isEmpty()) {
	$cmd->help();
}

$cleaner = new DG\PhpExtensionsFinder\Finder($options['--test']);
$cleaner->go($options['path']);
