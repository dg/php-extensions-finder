<?php

declare(strict_types=1);

namespace DG\PhpExtensionsFinder;

use PhpParser;


class Finder
{
	// https://www.php.net/manual/en/extensions.membership.php
	private array $coreExtensions = ['Core', 'SPL', 'Reflection', 'standard', 'date', 'pcre', 'hash', 'json', 'random'];


	public function go($dir): void
	{
		$parser = (new PhpParser\ParserFactory)->create(PhpParser\ParserFactory::PREFER_PHP7);
		$collector = new Collector;
		$traverser = new PhpParser\NodeTraverser;
		$traverser->addVisitor(new PhpParser\NodeVisitor\NameResolver);
		$traverser->addVisitor($collector);

		foreach (\Nette\Utils\Finder::findFiles('*.php')->from($dir) as $file) {
			$collector->file = (string) $file;
			try {
				$nodes = $parser->parse(file_get_contents($collector->file));
			} catch (PhpParser\Error $e) {
				echo $file . ': ' . $e->getMessage() . "\n";
				continue;
			}
			$traverser->traverse($nodes);
		}

		$json = [];
		foreach ($collector->list as $ext => $info) {
			if (in_array($ext, $this->coreExtensions, true)) {
				continue;
			}

			$json['require']["ext-$ext"] = '*';
			echo "\n$ext\n--------\n";
			foreach ($info as $token => $usages) {
				foreach ($usages as $file => $lines) {
					foreach ($lines as $line) {
						echo "$file:$line $token\n";
					}
				}
			}
		}

		echo "\nComposer\n--------\n", json_encode($json, JSON_PRETTY_PRINT);
	}
}
