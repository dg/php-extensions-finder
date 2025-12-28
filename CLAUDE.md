# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PHP Extensions Finder is a command-line tool that analyzes PHP source code to identify which PHP extensions are required. It uses nikic/php-parser to traverse PHP AST nodes and uses reflection to determine which extensions provide the detected classes, functions, and constants.

## Usage

Run the tool from the command line:

```bash
# Analyze current directory
./php-extensions-finder

# Analyze specific path
./php-extensions-finder /path/to/code
```

The tool outputs:
1. Detailed list of extensions found with file:line references
2. JSON-formatted composer.json require block
3. php.ini format with extension directives

## Architecture

### Core Components

- **`php-extensions-finder`**: Executable entry point that loads bootstrap
- **`bootstrap.php`**: Sets up autoloading, exception handling, and CLI argument parsing
- **`Finder`**: Main orchestrator that coordinates parsing
  - Maintains list of core PHP extensions to filter out
  - Iterates through PHP files and manages the parsing pipeline
  - Delegates report generation to Reporter
- **`Collector`**: AST visitor (extends NodeVisitorAbstract) that detects extension usage
  - Tracks static calls, property fetches, class constants, new instances, function calls, and constant usage
  - Uses reflection to map detected symbols to their extensions
  - Stores findings by extension → token → file → line numbers
- **`Reporter`**: Generates output reports from collected data
  - Takes collected extension data and filters out core extensions in constructor
  - Generates three formats: detailed list, composer.json, and php.ini
  - Designed to be extensible for additional output formats

### Extension Detection Strategy

The Collector uses reflection after detecting usage:
- Classes/interfaces: `(new \ReflectionClass($name))->getExtensionName()`
- Functions: `(new \ReflectionFunction($name))->getExtensionName()`
- Constants: Matches against `get_defined_constants(true)` grouped by extension

This means detection happens at **analysis time** - the tool must run in an environment where the detected classes/functions/constants are available.

### Core Extensions List

The `$coreExtensions` array in Finder lists extensions that are always available and should be filtered from output:
- Core, SPL, Reflection, standard, date, pcre, hash, json, random

Update this list when PHP's bundled extensions change.

## Development Notes

### Dependencies

- PHP 8.0+ required
- nette/command-line: CLI argument parsing
- nette/utils: File system utilities (Finder)
- nikic/php-parser: PHP AST parsing

### Code Style

Follows standard PHP formatting with `declare(strict_types=1)` in all files.

### Testing

```bash
# Run all tests
composer run tester
# or
vendor/bin/tester tests -s -C

# Run specific test file
vendor/bin/tester tests/Foo.phpt -s -C
```
