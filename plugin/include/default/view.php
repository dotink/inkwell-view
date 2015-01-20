<?php

	return Affinity\Action::create(['core'], function($app, $container) {
		$root_directory = $app['engine']->fetch('view', 'root_directory', 'user/templates');

		$container->define('Inkwell\View', [
			':root_directory' => $app->getDirectory($root_directory)
		]);
	});
