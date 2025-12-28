<?php

declare(strict_types=1);

namespace DG\PhpExtensionsFinder;

use Nette;
use PhpParser;


class Finder
{
	public function scan($dir): array
	{
		$parser = (new PhpParser\ParserFactory)->createForNewestSupportedVersion();
		$collector = new Collector;
		$traverser = new PhpParser\NodeTraverser;
		$traverser->addVisitor(new PhpParser\NodeVisitor\NameResolver);
		$traverser->addVisitor($collector);

		foreach (Nette\Utils\Finder::findFiles('*.php')->from($dir) as $file) {
			$collector->file = (string) $file;
			try {
				$nodes = $parser->parse(file_get_contents($collector->file));
			} catch (PhpParser\Error $e) {
				echo $file . ': ' . $e->getMessage() . "\n";
				continue;
			}
			$traverser->traverse($nodes);
		}

		return $collector->list;
	}
}
