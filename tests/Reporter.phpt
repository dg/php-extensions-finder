<?php

declare(strict_types=1);

use DG\PhpExtensionsFinder\Reporter;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test('generates detailed report', function () {
	$list = [
		'PDO' => [
			'PDO' => [
				'file1.php' => [10, 20],
				'file2.php' => [15],
			],
		],
		'curl' => [
			'curl_init()' => [
				'file1.php' => [5],
			],
			'CURLOPT_RETURNTRANSFER' => [
				'file1.php' => [6],
			],
		],
	];

	$reporter = new Reporter($list);
	$report = $reporter->generateReport();

	Assert::contains('PDO', $report);
	Assert::contains('file1.php:', $report);
	Assert::contains('file2.php:', $report);
	Assert::contains('curl', $report);
	Assert::contains('curl_init()', $report);
	Assert::contains('CURLOPT_RETURNTRANSFER', $report);
});


test('filters out core extensions from report', function () {
	$list = [
		'PDO' => [
			'PDO' => ['file1.php' => [10]],
		],
		'Core' => [
			'strlen()' => ['file1.php' => [5]],
		],
		'standard' => [
			'array_map()' => ['file1.php' => [8]],
		],
	];

	$reporter = new Reporter($list);
	$report = $reporter->generateReport();

	Assert::contains('PDO', $report);
	Assert::contains('file1.php:', $report);
	Assert::notContains('Core', $report);
	Assert::notContains('standard', $report);
	Assert::notContains('strlen()', $report);
	Assert::notContains('array_map()', $report);
});


test('generates composer.json structure', function () {
	$list = [
		'PDO' => [
			'PDO' => ['file1.php' => [10]],
		],
		'curl' => [
			'curl_init()' => ['file1.php' => [5]],
		],
		'mbstring' => [
			'mb_strlen()' => ['file1.php' => [8]],
		],
	];

	$reporter = new Reporter($list);
	$json = $reporter->generateComposerJson();

	Assert::same([
		'require' => [
			'ext-PDO' => '*',
			'ext-curl' => '*',
			'ext-mbstring' => '*',
		],
	], $json);
});


test('filters core extensions from composer.json', function () {
	$list = [
		'PDO' => [
			'PDO' => ['file1.php' => [10]],
		],
		'Core' => [
			'strlen()' => ['file1.php' => [5]],
		],
		'SPL' => [
			'ArrayIterator' => ['file1.php' => [7]],
		],
	];

	$reporter = new Reporter($list);
	$json = $reporter->generateComposerJson();

	Assert::same([
		'require' => [
			'ext-PDO' => '*',
		],
	], $json);

	Assert::false(isset($json['require']['ext-Core']));
	Assert::false(isset($json['require']['ext-SPL']));
});


test('handles empty list', function () {
	$reporter = new Reporter([]);
	$report = $reporter->generateReport();
	$json = $reporter->generateComposerJson();

	Assert::same('', $report);
	Assert::same([], $json);
});
