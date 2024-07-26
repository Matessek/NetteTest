<?php

declare(strict_types=1);

namespace App\Core;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;
		//$router->addRoute('<presenter>/<action>[/<id>]', 'Home:default');
		
		$router->addRoute('post','Post:default');
		$router->addRoute('get','Get:default');
		$router->addRoute('<param .+>', 'Post:default');
		
		return $router;
	}
}
