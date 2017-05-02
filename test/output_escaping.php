<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8" /> 
	<title>Output escaping sample</title>
</head>

<body>

    <h1>Output escaping sample</h1>

    <?php
    	//Display the result of submitting the form
    	if($_SERVER['REQUEST_METHOD']==='POST') {
    		echo "Hello, " . htmlspecialchars($_POST['name']);
    	}
    ?>

    <form method="post" action="">
	    <div>
	    	<label for="name">Your Name</label>
	    	<input id="name" name="name" autofocus />
	    </div>
	    <div>
	    	<button type="submit">Submit</button>
	    </div> 
    </form>


</body>
</html>