<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
	->setRiskyAllowed(true)
	->setRules([
		'@PSR12' => true,
		'class_attributes_separation' => true,
		'ordered_class_elements' => [
			'sort_algorithm' => 'alpha',
		],
		'no_unused_imports' => true,
		'ordered_imports' => [
			'imports_order' => ['const', 'class', 'function'],
		],
		'not_operator_with_successor_space' => true,
		'declare_strict_types' => true,
	])
	->setFinder(
		Finder::create()
			->in(__DIR__)
			->exclude([
				'bin',
				'scratch',
				'templates',
				'tests/fixtures',
			])
	);
