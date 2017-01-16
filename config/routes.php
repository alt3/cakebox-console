<?php
/**
 * Routes configuration
 *
 */

use Cake\Core\Plugin;
use Cake\Routing\Router;

Router::scope('/', function ($routes) {

    /**
     * Enable RESTful routes for our controllers.
     */
    $routes->extensions(['json']);

    /**
     * Connect /dashboard URLs to DashboardsController and disable /dashboards route.
     */
    $routes->connect('/dashboards/*', ['controller' => null]);
    $routes->connect('/dashboard', ['controller' => 'Dashboards', 'action' => 'index'], ['routeClass' => 'InflectedRoute']);
    $routes->connect('/dashboard/:action/*', ['controller' => 'Dashboards'], ['routeClass' => 'InflectedRoute']);

    /**
     * Here, we are connecting '/' (base path) to a controller called 'Pages',
     * its action called 'display', and we pass a param to select the view file
     * to use (in this case, src/Template/Pages/home.ctp)...
     */
    $routes->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);

    /**
    * Connect a route for the index action of any controller.
    * And a more general catch all route for any action.
    * Using InflectedRoute so HtmlHelper::Link() will generate lowercase URL's
    */
    $routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'InflectedRoute']);
    $routes->connect('/:controller/:action/*', [], ['routeClass' => 'InflectedRoute']);

    /**
    * Load all plugin routes.  See the Plugin documentation on
    * how to customize the loading of plugin routes.
    */
    Plugin::routes();
});
