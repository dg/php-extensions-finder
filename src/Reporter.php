<?php

declare(strict_types=1);

namespace DG\PhpExtensionsFinder;


class Reporter
{
	// https://www.php.net/manual/en/extensions.membership.php
	private array $coreExtensions = ['Core', 'SPL', 'Reflection', 'standard', 'date', 'pcre', 'hash', 'json', 'random'];


	public function __construct(
		private array $list,
	) {
		// Remove core extensions from list
		foreach ($this->coreExtensions as $core) {
			unset($this->list[$core]);
		}
	}


	public function generateReport(): string
	{
		$res = '';
		foreach ($this->list as $ext => $info) {
			$res .= "\n$ext\n" . str_repeat('-', strlen($ext)) . "\n";

			// Reorganize data: file -> [tokens]
			$byFile = [];
			foreach ($info as $token => $usages) {
				foreach ($usages as $file => $lines) {
					if (!isset($byFile[$file])) {
						$byFile[$file] = [];
					}
					$byFile[$file][] = $token;
				}
			}

			foreach ($byFile as $file => $tokens) {
				$tokens = array_unique($tokens);
				sort($tokens);
				$res .= "$file:\n- " . implode(', ', $tokens) . "\n\n";
			}
		}

		return $res;
	}


	public function generateComposerJson(): array
	{
		$json = [];
		foreach ($this->list as $ext => $info) {
			$json['require']["ext-$ext"] = '*';
		}

		return $json;
	}
}
