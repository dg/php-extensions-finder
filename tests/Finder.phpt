<?php

declare(strict_types=1);

use DG\PhpExtensionsFinder\Finder;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test('finds extensions in directory', function () {
	$finder = new Finder;

	ob_start();
	$finder->go(__DIR__ . '/fixtures');
	$output = ob_get_clean();

	// Should find PDO
	Assert::contains('PDO', $output);

	// Should find curl
	Assert::contains('curl', $output);

	// Should find mbstring
	Assert::contains('mbstring', $output);

	// Should generate composer.json output
	Assert::contains('Composer', $output);
	Assert::contains('ext-PDO', $output);
	Assert::contains('ext-curl', $output);
	Assert::contains('ext-mbstring', $output);
});


test('filters out core extensions', function () {
	$finder = new Finder;

	ob_start();
	$finder->go(__DIR__ . '/fixtures');
	$output = ob_get_clean();

	// Core extensions should not appear
	Assert::notContains('ext-Core', $output);
	Assert::notContains('ext-standard', $output);
	Assert::notContains('ext-SPL', $output);
	Assert::notContains('ext-Reflection', $output);
	Assert::notContains('ext-date', $output);
	Assert::notContains('ext-pcre', $output);
	Assert::notContains('ext-hash', $output);
	Assert::notContains('ext-json', $output);
	Assert::notContains('ext-random', $output);
});


test('shows file and line information', function () {
	$finder = new Finder;

	ob_start();
	$finder->go(__DIR__ . '/fixtures');
	$output = ob_get_clean();

	// Should contain file:line format
	Assert::contains('sample-with-extensions.php:', $output);
	Assert::contains('PDO', $output);
	Assert::contains('curl_init', $output);

	// Check that output matches expected format (file:number token)
	Assert::true((bool) preg_match('~sample-with-extensions\.php:\d+ PDO~', $output));
	Assert::true((bool) preg_match('~sample-with-extensions\.php:\d+ curl_init~', $output));
});


test('handles parse errors gracefully', function () {
	// Create a file with syntax error
	$tempDir = sys_get_temp_dir() . '/php-ext-finder-test-' . uniqid();
	mkdir($tempDir);
	file_put_contents($tempDir . '/invalid.php', '<?php this is not valid PHP syntax');

	$finder = new Finder;

	ob_start();
	$finder->go($tempDir);
	$output = ob_get_clean();

	// Should contain error message
	Assert::contains('invalid.php', $output);

	// Cleanup
	unlink($tempDir . '/invalid.php');
	rmdir($tempDir);
});


test('handles empty directory', function () {
	$tempDir = sys_get_temp_dir() . '/php-ext-finder-empty-' . uniqid();
	mkdir($tempDir);

	$finder = new Finder;

	ob_start();
	$finder->go($tempDir);
	$output = ob_get_clean();

	// Should still output Composer section even if empty
	Assert::contains('Composer', $output);

	// Cleanup
	rmdir($tempDir);
});


test('generates valid JSON output', function () {
	$finder = new Finder;

	ob_start();
	$finder->go(__DIR__ . '/fixtures');
	$output = ob_get_clean();

	// Extract JSON part
	$lines = explode("\n", $output);
	$jsonStarted = false;
	$jsonLines = [];

	foreach ($lines as $line) {
		if (str_contains($line, 'Composer')) {
			$jsonStarted = true;
			continue;
		}

		if ($jsonStarted && (str_starts_with($line, '{') || str_starts_with($line, ' ') || str_starts_with($line, '}'))) {
			$jsonLines[] = $line;
		}
	}

	$json = implode("\n", $jsonLines);

	// Should be valid JSON
	$decoded = json_decode($json, true);
	Assert::type('array', $decoded);
	Assert::true(isset($decoded['require']));

	// Should have ext- prefix
	foreach (array_keys($decoded['require']) as $key) {
		Assert::true(str_starts_with($key, 'ext-'));
	}
});
