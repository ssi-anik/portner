#!/usr/bin/env php
<?php

use Portner\Commands\Applications\ApplicationAddCommand;
use Portner\Commands\Applications\ApplicationListCommand;
use Portner\Commands\Applications\ApplicationRemoveCommand;
use Portner\Commands\Applications\ApplicationSearchCommand;
use Portner\Commands\Services\ServiceAddCommand;
use Portner\Commands\Services\ServiceListCommand;
use Portner\Commands\Services\ServiceRemoveCommand;
use Portner\Service\Storage;
use Symfony\Component\Console\Application;

require __DIR__ . '/vendor/autoload.php';
$home = getenv('HOME');
$storageDirectory = ".portner";
$storageFile = ".portner.storage.json";

$storageFileAbsolutePath = sprintf("%s/%s/%s", $home, trim($storageDirectory, "/"), $storageFile);
$path = pathinfo($storageFileAbsolutePath)['dirname'];
if (!file_exists(($path))) {
	if (!mkdir($path, 0755, true)) {
		die("Cannot create storage directory");
	}
}

if (!file_exists($storageFileAbsolutePath)) {
	$file = fopen($storageFileAbsolutePath, "w");
	fwrite($file, json_encode([ Storage::SERVICES_KEY => [], Storage::APPLICATION_KEY => [] ]));
	fclose($file);
}

$stored = json_decode(file_get_contents($storageFileAbsolutePath), true);

if (json_last_error() !== JSON_ERROR_NONE) {
	die('Invalid JSON format. File corrupted.');
}
$storage = new Storage($storageFileAbsolutePath, $stored);

$application = new Application('portner', '1.0');
$application->add(new ServiceListCommand($storage));
$application->add(new ServiceAddCommand($storage));
$application->add(new ServiceRemoveCommand($storage));

$application->add(new ApplicationListCommand($storage));
$application->add(new ApplicationAddCommand($storage));
$application->add(new ApplicationSearchCommand($storage));
$application->add(new ApplicationRemoveCommand($storage));

$application->run();