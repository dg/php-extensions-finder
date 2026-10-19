<?php declare(strict_types=1);

namespace DG\PhpExtensionsFinder;

use Nette\CommandLine\{Attributes as Cli, Command, Console, HelpRenderer, Normalizers, Parser, ParseResult};

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


$command = Command::from(#[Cli\Command('Finds PHP extensions required by code.', name: 'php-extensions-finder')] function (
	ParseResult $args,
	Command $command,
	#[Cli\Argument('Directory to scan', normalizer: [Normalizers::class, 'realPath'], defaultDescription: 'current directory')]
	?string $path = null,
) use ($stdout, $stderr): void {
	if ($args->isEmpty()) { // a bare run recalls the usage and then scans the current directory
		(new HelpRenderer($stdout))->render($command);
	}

	$finder = new Finder($stderr);
	$list = $finder->scan($path ?? (string) getcwd());

	$reporter = new Reporter($list);
	$stdout->write($reporter->generateReport());

	$composer = $reporter->generateComposerJson();
	$stdout->write("\nComposer\n--------\n" . json_encode($composer, JSON_PRETTY_PRINT));

	$phpIni = $reporter->generatePhpIni();
	$stdout->write("\n\nphp.ini\n-------\n" . $phpIni);
});
$command->addFlag('--help', 'Show this help', alias: '-h', standalone: true);

$args = (new Parser)->parse($command);
if ($args['--help']) {
	(new HelpRenderer($stdout))->render($command);
	exit;
}

exit($args->run());
