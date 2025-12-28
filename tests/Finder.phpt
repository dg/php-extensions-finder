<?php

declare(strict_types=1);

use DG\PhpExtensionsFinder\Finder;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test('finds extensions in directory', function () {
	$finder = new Finder;
	$list = $finder->scan(__DIR__ . '/fixtures');

	// Should find PDO
	Assert::true(isset($list['PDO']));

	// Should find curl
	Assert::true(isset($list['curl']));

	// Should find mbstring
	Assert::true(isset($list['mbstring']));
});


test('returns all extensions including core', function () {
	$finder = new Finder;
	$list = $finder->scan(__DIR__ . '/fixtures');

	// Core extensions should be in the list (Finder doesn't filter)
	Assert::type('array', $list);

	// Should contain both regular and core extensions
	Assert::true(count($list) > 0);
});


test('returns file and line information', function () {
	$finder = new Finder;
	$list = $finder->scan(__DIR__ . '/fixtures');

	// Should have structure: extension -> token -> file -> lines[]
	Assert::true(isset($list['PDO']['PDO']));
	Assert::type('array', $list['PDO']['PDO']);

	// Check that we have file paths and line numbers
	foreach ($list['PDO']['PDO'] as $file => $lines) {
		Assert::type('string', $file);
		Assert::type('array', $lines);
		Assert::true(count($lines) > 0);
		foreach ($lines as $line) {
			Assert::type('int', $line);
		}
	}
});


test('handles parse errors gracefully', function () {
	// Create a file with syntax error
	$tempDir = sys_get_temp_dir() . '/php-ext-finder-test-' . uniqid();
	mkdir($tempDir);
	file_put_contents($tempDir . '/invalid.php', '<?php this is not valid PHP syntax');

	$finder = new Finder;

	ob_start();
	$list = $finder->scan($tempDir);
	$output = ob_get_clean();

	// Should print error message but continue
	Assert::contains('invalid.php', $output);

	// Should return empty list for directory with only invalid file
	Assert::same([], $list);

	// Cleanup
	unlink($tempDir . '/invalid.php');
	rmdir($tempDir);
});


test('handles empty directory', function () {
	$tempDir = sys_get_temp_dir() . '/php-ext-finder-empty-' . uniqid();
	mkdir($tempDir);

	$finder = new Finder;
	$list = $finder->scan($tempDir);

	// Should return empty array
	Assert::same([], $list);

	// Cleanup
	rmdir($tempDir);
});
