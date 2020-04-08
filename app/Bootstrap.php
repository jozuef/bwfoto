<?php

declare(strict_types=1);
namespace App;

use Nette\Configurator;





class Bootstrap
{
	public static function boot(): Configurator
	{
		$configurator = new Configurator;

		//$configurator->setDebugMode(TRUE);
    //$configurator->setDebugMode(FALSE);
		$configurator->enableTracy(__DIR__ . '/../log');
    $configurator->enableDebugger();
    error_reporting(~E_USER_DEPRECATED);

		$configurator->setTimeZone('Europe/Prague');
		$configurator->setTempDirectory(__DIR__ . '/../temp');


		$configurator->createRobotLoader()
			->addDirectory(__DIR__)
			->register();


		$configurator->addConfig(__DIR__ . '/config/config.neon');
                if(file_exists(__DIR__ . '/config/config.local.neon')){
                  $configurator->addConfig(__DIR__ . '/config/config.local.neon');
                }
                return $configurator;
	}
}
