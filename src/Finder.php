<?php

namespace DG\PhpExtensionsFinder;

use PhpParser;


class Finder
{
	private $coreExtensions = ['Core', 'SPL', 'Reflection', 'standard', 'date', 'pcre'];


	/**
	 * @return void
	 */
	public function go($dir)
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

		foreach ($this->coreExtensions as $ext) {
			unset($collector->list[$ext]);
		}

		foreach ($collector->list as $ext => $info) {
			echo "\n$ext\n--------\n";
			foreach ($info as $token => $usages) {
				foreach ($usages as $file => $lines) {
					foreach ($lines as $line) {
						echo "$file:$line $token\n";
					}
				}
			}
		}

		$json = [];
		foreach ($collector->list as $ext => $info) {
			$json['require']["ext-$ext"] = '*';
		}
		echo "\nComposer\n--------\n", json_encode($json, JSON_PRETTY_PRINT);
	}
}
