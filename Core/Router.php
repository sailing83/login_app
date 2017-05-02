<?php

namespace Core;

class Router{
    protected $routes = [];    //Associative array of routes(the routing table)
    protected $params = [array()];    //Parameters from the matched route
    
    
    /**
     * Add a route to the routing table
     * @param string $route The route URL
     * @param array  $params Parameters(controller, action, etc.)
     * @return void
     * */
    public function add($route, $params = []){
        //Convert the route to a regular expression: escape forward slashes
        //e.g. {controller}/{action} => {controller}\/{action}
        $route = preg_replace('/\//', '\\/', $route);
        
        //Convert variables
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<$1>[a-z-]+)', $route);
        
        //Convert variables with custom regular expression e.g. {id:\d+}
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<$1>$2)', $route);
        
        //Add start and end delimiters and case insensitive flat
        $route = '/^' . $route . '$/i';
        
        $this->routes[$route] = $params; 
    }
    
    /**
     * Get all the routes from the routing table
     * @return array $this->routes the routing table
     * */
    public function getRoutes(){
        return $this->routes;
    }
    
    /**
     * Match the route to the routes in the routing table, 
     * setting the $params property if a route is found.
     * @param string $url The route URL from the query string
     * @return boolean true if a match found, false otherwise 
     * */ 
    public function match($url) {
        /*
        foreach($this->routes as $route=>$params) {
            if($url == $route) {
                $this->params = $params;
                return true;
            }
        }
        return false;
        */
        //Improve the above code from simple string comparison to regular expression match
        
        //Match to the fixed URL format /controller/action
        //$reg_exp = "/^(?<controller>[a-z-_]+)\/(?<action>[a-z-_]+)$/";
        foreach($this->routes as $route=>$params) {
            if(preg_match($route, $url, $matches)) {
                //Get named capture group values
                //$params = array();
                //print_r($matches);
                foreach($matches as $key=>$match) {
                    if(is_string($key)) {
                        $params[$key] = $match;
                    }
                }
                $this->params = $params;
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get the currently matched parameters
     * @return array
     * */
    public function getParams() {
        return $this->params;
    }
    
    /**
     * Dispatch the route, creating the controller object and running the
     * action method, controller is the class name/action is the method name in Controller class
     *
     * @param string $url The route URL
     *
     * @return void
     */
    
	 public function dispatch($url) {
	   
          $url = $this->removeQueryStringVariables($url);
       
		  if($this->match($url)) {
				$controller = $this->params['controller'];
				$controller = $this->convertToStudlyCaps($controller);  
                //$controller = "App\Controllers\\$controller";  //get controller under its namespace
				$controller = $this->getNamespace() . $controller; 
                //echo $controller . '<br/>';
                if(class_exists($controller))	{
					$controller_object = new $controller($this->params);
					//print_r($controller_object);
					$action = $this->params['action'];
					$action = $this->convertToCamelCase($action);	//get action name
					
					if(is_callable(array($controller_object, $action))) {
						$controller_object->$action();	//Execute method named by action
					} else { 
						//exit("Method $action in controller $controller not found");	
                        throw new \Exception("Method $action in controller $controller not found");				
					}  
				} else {
					//exit("Controller class $controller not found");
                    throw new \Exception("Controller class $controller not found");
				}	  
		  } else {
		  		//exit('No route matched');
                throw new \Exception("No route matched", 404);
		  }	 
	 }    
    
    
    /**
    * Convert the string with hyphens to StudlyCaps, 
    * e.g. post-authors => PostAuthors
    *
    * @param string $string The string to convert
    * @return string after conversion
    */
    protected function convertToStudlyCaps($string) {
	 	  return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }
    
    /**
     * Convert the string with hyphens to camelCase,
     * e.g. add-new => addNew
     *
     * @param string $string The string to convert
     *
     * @return string after conversion
     */
    protected function convertToCamelCase($string) {
		  return lcfirst($this->convertToStudlyCaps($string)) ;    
    }
    
    
    /**
     * Remove the query string variables from the URL (if any). As the full
     * query string is used for the route, any variables at the end will need
     * to be removed before the route is matched to the routing table. For
     * example:
     *
     *   URL                           $_SERVER['QUERY_STRING']  Route
     *   -------------------------------------------------------------------
     *   localhost                     ''                        ''
     *   localhost/?                   ''                        ''
     *   localhost/?page=1             page=1                    ''
     *   localhost/posts?page=1        posts&page=1              posts
     *   localhost/posts/index         posts/index               posts/index
     *   localhost/posts/index?page=1  posts/index&page=1        posts/index
     *
     * A URL of the format localhost/?page (one variable name, no value) won't
     * work however. (NB. The .htaccess file converts the first ? to a & when
     * it's passed through to the $_SERVER variable).
     *
     * @param string $url The full URL
     *
     * @return string The URL with the query string variables removed
     */
    protected function removeQueryStringVariables($url)
    {
        if ($url != '') {
            $parts = explode('&', $url, 2);

            if (strpos($parts[0], '=') === false) {
                $url = $parts[0];
            } else {
                $url = '';
            }
        }

        return $url;
    }
    
    /**
     * Get the namespace for the controller class. The namespace defined in the
     * route parameters is added if present.
     *
     * @return string The request URL
     */
    protected function getNamespace() {
        $namespace = 'App\Controllers\\';

        if(array_key_exists('namespace', $this->params)) {
            $namespace .= $this->params['namespace'] . '\\';
        }
        return $namespace;
    }

}

?>