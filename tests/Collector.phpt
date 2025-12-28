<?php

declare(strict_types=1);

use DG\PhpExtensionsFinder\Collector;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test('detects classes from extensions', function () {
	$code = '<?php $pdo = new PDO("mysql:host=localhost", "user", "pass");';

	$parser = (new ParserFactory)->createForNewestSupportedVersion();
	$collector = new Collector;
	$collector->file = 'test.php';

	$traverser = new NodeTraverser;
	$traverser->addVisitor(new NameResolver);
	$traverser->addVisitor($collector);

	$nodes = $parser->parse($code);
	$traverser->traverse($nodes);

	Assert::same(
		['PDO' => ['test.php' => [1]]],
		$collector->list['PDO'],
	);
});


test('detects functions from extensions', function () {
	$code = '<?php curl_init("https://example.com");';

	$parser = (new ParserFactory)->createForNewestSupportedVersion();
	$collector = new Collector;
	$collector->file = 'test.php';

	$traverser = new NodeTraverser;
	$traverser->addVisitor(new NameResolver);
	$traverser->addVisitor($collector);

	$nodes = $parser->parse($code);
	$traverser->traverse($nodes);

	Assert::same(
		['curl_init' => ['test.php' => [1]]],
		$collector->list['curl'],
	);
});


test('detects constants from extensions', function () {
	$code = '<?php $opt = CURLOPT_RETURNTRANSFER;';

	$parser = (new ParserFactory)->createForNewestSupportedVersion();
	$collector = new Collector;
	$collector->file = 'test.php';

	$traverser = new NodeTraverser;
	$traverser->addVisitor(new NameResolver);
	$traverser->addVisitor($collector);

	$nodes = $parser->parse($code);
	$traverser->traverse($nodes);

	Assert::same(
		['CURLOPT_RETURNTRANSFER' => ['test.php' => [1]]],
		$collector->list['curl'],
	);
});


test('tracks multiple usages across multiple files', function () {
	$code1 = '<?php $pdo1 = new PDO("", "", "");';
	$code2 = '<?php $pdo2 = new PDO("", "", "");';

	$parser = (new ParserFactory)->createForNewestSupportedVersion();
	$collector = new Collector;

	$traverser = new NodeTraverser;
	$traverser->addVisitor(new NameResolver);
	$traverser->addVisitor($collector);

	// First file
	$collector->file = 'file1.php';
	$nodes = $parser->parse($code1);
	$traverser->traverse($nodes);

	// Second file
	$collector->file = 'file2.php';
	$nodes = $parser->parse($code2);
	$traverser->traverse($nodes);

	Assert::same(
		[
			'PDO' => [
				'file1.php' => [1],
				'file2.php' => [1],
			],
		],
		$collector->list['PDO'],
	);
});


test('tracks multiple line numbers in same file', function () {
	$code = '<?php
$pdo1 = new PDO("", "", "");
$pdo2 = new PDO("", "", "");
$pdo3 = new PDO("", "", "");
';

	$parser = (new ParserFactory)->createForNewestSupportedVersion();
	$collector = new Collector;
	$collector->file = 'test.php';

	$traverser = new NodeTraverser;
	$traverser->addVisitor(new NameResolver);
	$traverser->addVisitor($collector);

	$nodes = $parser->parse($code);
	$traverser->traverse($nodes);

	Assert::same([2, 3, 4], $collector->list['PDO']['PDO']['test.php']);
});


test('ignores non-existent classes', function () {
	$code = '<?php $obj = new NonExistentClass();';

	$parser = (new ParserFactory)->createForNewestSupportedVersion();
	$collector = new Collector;
	$collector->file = 'test.php';

	$traverser = new NodeTraverser;
	$traverser->addVisitor(new NameResolver);
	$traverser->addVisitor($collector);

	$nodes = $parser->parse($code);
	$traverser->traverse($nodes);

	Assert::same([], $collector->list);
});


test('ignores non-existent functions', function () {
	$code = '<?php non_existent_function();';

	$parser = (new ParserFactory)->createForNewestSupportedVersion();
	$collector = new Collector;
	$collector->file = 'test.php';

	$traverser = new NodeTraverser;
	$traverser->addVisitor(new NameResolver);
	$traverser->addVisitor($collector);

	$nodes = $parser->parse($code);
	$traverser->traverse($nodes);

	Assert::same([], $collector->list);
});


test('detects static method calls', function () {
	$code = '<?php PDO::getAvailableDrivers();';

	$parser = (new ParserFactory)->createForNewestSupportedVersion();
	$collector = new Collector;
	$collector->file = 'test.php';

	$traverser = new NodeTraverser;
	$traverser->addVisitor(new NameResolver);
	$traverser->addVisitor($collector);

	$nodes = $parser->parse($code);
	$traverser->traverse($nodes);

	Assert::same(
		['PDO' => ['test.php' => [1]]],
		$collector->list['PDO'],
	);
});


test('detects class constants', function () {
	$code = '<?php $attr = PDO::ATTR_ERRMODE;';

	$parser = (new ParserFactory)->createForNewestSupportedVersion();
	$collector = new Collector;
	$collector->file = 'test.php';

	$traverser = new NodeTraverser;
	$traverser->addVisitor(new NameResolver);
	$traverser->addVisitor($collector);

	$nodes = $parser->parse($code);
	$traverser->traverse($nodes);

	Assert::same(
		['PDO' => ['test.php' => [1]]],
		$collector->list['PDO'],
	);
});
