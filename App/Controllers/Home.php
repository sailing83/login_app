<?php 

namespace App\Controllers; 
use \Core\View;
use \App\Auth;

class Home extends \Core\Controller {
	
	  /**
     * Show the index page
     *
     * @return void
     */
	public function indexAction() {
		//echo 'Hello from the index action in the Home controller!';	
	    /*
        View::render('Home/index.php', [
                'name' => 'fan',
                'colors' => ['red', 'green', 'blue']
            ]);
        */
        // Test sending email
        //\App\Mail::send('tempyf@sina.com', 'Test', 'This is a test', '<h1>This is a test</h1>');
        View::renderTemplate('Home/index.html');
    }
    
    protected function before() {
        //echo '[before]';
        //return false;
    }
    
    protected function after() {
        //echo '[after]';
    }
	

}

?>