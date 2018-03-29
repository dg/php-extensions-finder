<?php

namespace DG\PhpExtensionsFinder;

class Finder
{
	private $coreExtensions = ['Core', 'SPL', 'standard', 'date', 'pcre'];

	/** @var array */
	private $list;


	/**
	 * @return void
	 */
	public function go($dir)
	{
		$this->list = [];

		foreach (\Nette\Utils\Finder::findFiles('*.php')->from($dir) as $file) {
			$this->parseFile((string) $file);
		}

		foreach ($this->coreExtensions as $ext) {
			unset($this->list[$ext]);
		}

		foreach ($this->list as $ext => $info) {
			echo "\n$ext\n--------\n";
			foreach ($info as $function => $usages) {
				foreach ($usages as $file => $lines) {
					foreach ($lines as $line) {
						echo "$file:$line $function()\n";
					}
				}
			}
		}

		$json = [];
		foreach ($this->list as $ext => $info) {
			$json['require']["ext-$ext"] = '*';
		}
		echo "\nComposer\n--------\n", json_encode($json, JSON_PRETTY_PRINT);
	}


	private function parseFile($file)
	{
		$tokens = token_get_all(file_get_contents($file));
		foreach ($tokens as $i => $token) {
			if (is_array($token) && $token[0] === T_WHITESPACE) {
				unset($tokens[$i]);
			}
		}
		$tokens = array_values($tokens);

		foreach ($tokens as $i => $token) {
			if ($token === '('
				&& $tokens[$i - 1][0] === T_STRING
				&& $tokens[$i - 2][0] !== T_DOUBLE_COLON
				&& $tokens[$i - 2][0] !== T_OBJECT_OPERATOR
				&& $tokens[$i - 2][0] !== T_FUNCTION
				&& $tokens[$i - 2][0] !== T_NEW
				&& $tokens[$i - 2][0] !== T_NS_SEPARATOR
				&& $tokens[$i - 2] !== '&'
				&& function_exists($func = $tokens[$i - 1][1])
				&& ($extName = (new \ReflectionFunction($func))->getExtensionName())
			) {
				$this->list[$extName][$func][$file][] = $tokens[$i - 1][2];
			}
		}
	}
}
