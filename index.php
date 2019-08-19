 <?php
 header("Access-Control-Allow-Origin: *");

 $foo = "Chris";

 ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>runBikeMaps</title>

    <style>

    </style>

	<script src="http://code.jquery.com/jquery-2.2.4.js"></script>

    <script>
		$( "#result" ).load( "http://www.strava.com/athletes/34470752" );


		$( "#result" ).load( "page2.php" );

		  $('#content').load("page2.php");

    </script>

</head>

<body>


<div id="result"><?php print $foo; ?></div>


<div id="content"></div>

</body>

</html>