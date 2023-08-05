<?php

declare(strict_types=1);

namespace DG\PhpExtensionsFinder;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\NodeVisitorAbstract;


class Collector extends NodeVisitorAbstract
{
	public string $file;
	public array $list = [];


	public function enterNode(Node $node)
	{
		if ($node instanceof Expr\StaticCall
			|| $node instanceof Expr\StaticPropertyFetch
			|| $node instanceof Expr\ClassConstFetch
			|| $node instanceof Expr\New_
		) {
			if ($node->class instanceof Node\Name) {
				$name = (string) $node->class;
				if (class_exists($name, false)) {
					$this->addExtension(
						(new \ReflectionClass($name))->getExtensionName(),
						$name,
						$node->class->getAttribute('startLine'),
					);
				}
			}

		} elseif ($node instanceof Expr\FuncCall && $node->name instanceof Node\Name) {
			$name = (string) $node->name;
			if (function_exists($name)) {
				$this->addExtension(
					(new \ReflectionFunction($name))->getExtensionName(),
					$name,
					$node->name->getAttribute('startLine'),
				);
			}

		} elseif ($node instanceof Expr\ConstFetch) {
			$name = (string) $node->name;
			$all = get_defined_constants(true);
			foreach ($all as $ext => $consts) {
				if (isset($consts[$name])) {
					$this->addExtension(
						$ext,
						$name,
						$node->name->getAttribute('startLine'),
					);
					break;
				}
			}
		}
	}


	private function addExtension(string|false $extName, string $token, int $line): void
	{
		if ($extName) {
			$this->list[$extName][$token][$this->file][] = $line;
		}
	}
}
