<?php

use Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;


/**
 * Router factory.
 */
class RouterFactory
{

	/**
	 * @return Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();
		// Changelog Router
		$changelogRouter = new RouteList('Changelog');
		$changelogRouter[] = new Route('changelog/<presenter>/<action>[/<id>]', 'Changelog:default');
		$router[] = $changelogRouter;

		$adminRouter = new RouteList('Admin');
		$adminRouter[] = new Route('admin/<presenter>/<action>[/<id>]', 'Homepage:default');
		$router[] = $adminRouter;

		$frontendRouter = new RouteList('Front');
		$frontendRouter[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
		$frontendRouter[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
		$router[] = $frontendRouter;
		return $router;
	}

}
