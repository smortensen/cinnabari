<?php

call_user_func(function () {
	$classes = array(
		'Datto\\Cinnabari\\Tests' => __DIR__ . '/src',
		'Datto\\Cinnabari' => dirname(__DIR__) . '/src',
		'SpencerMortensen\\RegularExpressions' => dirname(__DIR__) . '/vendor/spencer-mortensen/regular-expressions/src',
		'SpencerMortensen\\Parser' => dirname(__DIR__) . '/vendor/spencer-mortensen/parser/src',
	);

	foreach ($classes as $namespacePrefix => $libraryPath) {
		$namespacePrefix .= '\\';
		$namespacePrefixLength = strlen($namespacePrefix);

		$autoloader = function ($class) use ($namespacePrefix, $namespacePrefixLength, $libraryPath) {
			if (strncmp($class, $namespacePrefix, $namespacePrefixLength) !== 0) {
				return;
			}

			$relativeClassName = substr($class, $namespacePrefixLength);
			$relativeFilePath = strtr($relativeClassName, '\\', '/') . '.php';
			$absoluteFilePath = "{$libraryPath}/{$relativeFilePath}";

			if (is_file($absoluteFilePath)) {
				include $absoluteFilePath;
			}
		};

		spl_autoload_register($autoloader);
	}
});
