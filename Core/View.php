<?php

namespace Core;

/**
 * View class
 * */
class View {
    
    /**
     * Render a view file
     * 
     * @param string $view The view file
     * @return void
     * */
    public static function render($view, $args=[]) {

        extract($args, EXTR_SKIP);
        
        $file = "../App/Views/$view";   //relative to core directory
        
        if(is_readable($file)) {
            require $file;
        } else {
            //exit("$file not found");
            throw new \Exception("$file not found");
        }
    }

    /**
     * Render a view template using Twig
     *
     * @param string $template The template file
     * @param array $args Associaive array of data to display in the view
     *
     * @return void
     */
    public static function renderTemplate($template, $args = []) {
        echo static::getTemplate($template, $args);
    }

    /**
     * Render a view template using Twig
     *
     * @param string $template The template file
     * @param array $args Associaive array of data to display in the view
     *
     * @return void
     */
    public static function getTemplate($template, $args = []) {
        static $twig = null;

        if($twig === null) {
            $loader = new \Twig_Loader_Filesystem(dirname(__DIR__) . '/App/Views');
            $twig = new \Twig_Environment($loader);
            //$twig->addGlobal('session', $_SESSION);
            //$twig->addGlobal('is_logged_in', \App\Auth::isLoggedIn());
            $twig->addGlobal('current_user', \App\Auth::getUser());
            $twig->addGlobal('flash_messages', \App\Flash::getMessages());
        } 
        return $twig->render($template, $args);
    }
}

?>