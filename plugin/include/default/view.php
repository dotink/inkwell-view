<?php

	return Affinity\Action::create(['core'], function($app, $broker) {
		$root_directory = $app['engine']->fetch('view', 'root_directory', 'user/templates');

		$broker->define('Inkwell\View', [
			':root_directory' => $app->getDirectory($root_directory)
		]);
	});
