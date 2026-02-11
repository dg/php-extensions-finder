<?php declare(strict_types=1);

namespace DG\PhpExtensionsFinder;

use Nette\CommandLine\Command;
use Nette\CommandLine\Console;
use Nette\CommandLine\HelpRenderer;
use Nette\CommandLine\Normalizers;
use Nette\CommandLine\Parser;

$autoload = is_file(__DIR__ . '/../vendor/autoload.php')
	? __DIR__ . '/../vendor/autoload.php'
	: __DIR__ . '/../../../autoload.php';
if (@!include $autoload) { // @ dependencies may not be installed
	echo 'Install packages using `composer update`';
	exit(1);
}


$stdout = new Console(STDOUT);
$stderr = new Console(STDERR);

set_exception_handler(function ($e) use ($stderr) {
	$stderr->writeLine("ERROR: {$e->getMessage()}", 'red');
	exit(1);
});


$command = new Command('php-extensions-finder', 'Finds PHP extensions required by code.');
$command->addFlag('--help', 'Show this help', alias: '-h', standalone: true);
$command->addArgument(
	'path',
	'Directory to scan',
	optional: true,
	normalizer: Normalizers::realPath(...),
	default: getcwd(),
	defaultDescription: 'current directory',
);

$args = (new Parser)->parse($command);
$helpRenderer = new HelpRenderer($stdout);
if ($args['--help']) {
	$helpRenderer->render($command);
	exit;
} elseif ($args->isEmpty()) { // a bare run recalls the usage and then scans the current directory
	$helpRenderer->render($command);
}

$finder = new Finder($stderr);
$list = $finder->scan($args['path']);

$reporter = new Reporter($list);
$stdout->write($reporter->generateReport());

$composer = $reporter->generateComposerJson();
$stdout->write("\nComposer\n--------\n" . json_encode($composer, JSON_PRETTY_PRINT));

$phpIni = $reporter->generatePhpIni();
$stdout->write("\n\nphp.ini\n-------\n" . $phpIni);
