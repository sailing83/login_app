<?php

/**
 * Front Controller
 * */

//ini_set('session.cookie_lifetime', 864000);  //10 days

/**
 * Using Twig template engine
 */
//require_once dirname(__DIR__) . '/vendor/Twig/lib/Twig/Autoloader.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';
Twig_Autoloader::register();


//Error and Exception handling
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

session_start();	//start session

$router = new Core\Router();
//echo get_class($router);

//Add routes
$router->add('admin/{controller}/{action}', ['namespace' => 'Admin']);
$router->add('', ['controller'=>'Home', 'action'=>'index']);
$router->add('login', ['controller'=>'Login', 'action'=>'new']);
$router->add('logout', ['controller'=>'Login', 'action'=>'destroy']);
$router->add('password/reset/{token:[\da-f]+}', ['controller' => 'Password', 'action'=>'reset']);
$router->add('signup/activate/{token:[\da-f]+}', ['controller' => 'Signup', 'action'=>'activate']);
//$router->add('posts', ['controller'=>'Posts', 'action'=>'index']);
//$router->add('posts/new', ['controller'=>'Posts', 'action'=>'new']);

$router->add('{controller}/{action}');      
//Route pattern: any url like xxx/aa/bb, the application will treat 'aa' as controller, 'bb' as action 
//$router->add('admin/{action}/{controller}');    //Route pattern
$router->add('{controller}/{id:\d+}/{action}');
/*
//Display the routing table
echo '<h2>Route table</h2>';
echo '<pre>';
print_r($router->getRoutes());
echo '</pre>';
*/
//Match the requested route
$url = $_SERVER['QUERY_STRING'];
/*
if($router->match($url)) {
    echo '<h2>Route params</h2>';
    print_r($router->getParams());
    echo '<br/>------------我是分隔线-------------<br/>';
} else {
    echo "No route found for URL '$url'";
}
echo '<br/>';
*/
$router->dispatch($_SERVER['QUERY_STRING']);

?>
