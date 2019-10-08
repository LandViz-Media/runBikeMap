<!DOCTYPE html>
<html>
<head>
<title>GPX loader 2019</title>
<script>

</script>
</head>

<body>
<?php
if ($_POST == null OR $_POST == "") {
?>
	<form action="gpxLoader2019.php" method="post" enctype="multipart/form-data">



	  Filename: <input type="text" name="filename"/><br>

	<br><br>
	  <input type="submit" value="Submit">

	<hr>
	</form>

<?php
}else{

//header('Content-type: text/plain');
require("../../conn1.php");

$mysqli = new mysqli($hostname, $username, $password, $database);
// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}


$table = 'running_cjs_stats';


$sql = "SELECT name FROM $table";


$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
	    $name = $row["name"];
    }
   print $name."<br><br>";
}

//$mysqli->close();







	//$name = $_POST['name'];
	$name = "Chris";
	$type = "run";
	//$filename = $_POST['filename'];
	$filename ="sample.gpx";
	$gpx = simplexml_load_file($filename);
	//$gpx = simplexml_load_file("sample/".$filename.".gpx");

	$firstPoint = true;


		foreach ($gpx->trk as $trk) {
		  foreach ($trk->trkseg as $trkseg) {
		      foreach ($trkseg->trkpt as $pt) {
			  	$datetime = '';
			  	$gpxDatetime = (string) $pt->time;
			  	$date = substr($gpxDatetime, 0, 10);
			  	$time = substr($gpxDatetime, 11, 8);

				$datetime = $date." ".$time;
				$latitude = (string) $pt['lat'];
				$longitude  = (string) $pt['lon'];
				$elevation = (string) $pt->ele;



print $gpxDatetime."---".$date." ".$time." ".$latitude." ".$longitude." ".$elevation."<br>";

/*
				//need to add all raw data to table of all points
			    $sql="INSERT INTO ky_route_all (datetime, type, latitude, longitude, name, filename) VALUES ('$datetime', '$type', $latitude, $longitude, '$name', '$filename' )";
				if ($mysqli->query($sql)) {
					//could print somethign to screen
	        	} else {
					echo "Error: " . $sql ."<br>" .mysqli_error($mysqli);
	    		}
*/





				if ($firstPoint == true) {
					//no need to calculate distance - just add
				    $firstPoint = false;
				    $distLastPointMeters = 0;
				    $maxSpeed = 0;
				    $sql="INSERT INTO $table (datetime, type, latitude, longitude, name, distLastPointMeters, filename, secondsLastPoint, mph ) VALUES ('$datetime', '$type', $latitude, $longitude, '$name', $distLastPointMeters, '$filename', 0, 0)";


					if ($mysqli->query($sql)) {
						//could print somethign to screen
		        	} else {
						echo "Error!!!!!: " . $sql ."<br>" .mysqli_error($mysqli);
		    		}

					$latitudeLast = $latitude;
					$longitudeLast = $longitude;
					$datetimeLast = $datetime;
					$startTime = $datetime;

					//$distance = 0;

				}else{
					$lat1 = $latitude;
					$lon1 = $longitude;
					$lat2 = $latitudeLast;
					$lon2 = $longitudeLast;

					$unit = "METERS"; //meters

					$theta = $lon1 - $lon2;
					$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
					$dist = acos($dist);
					$dist = rad2deg($dist);
					$miles = $dist * 60 * 1.1515;

					$milesTotal = $milesTotal + $miles;
					$unit = strtoupper($unit);

					if ($unit == "K") {
						$distance = ($miles * 1.609344);
					} else if ($unit == "METERS") {
					    $distance = ($miles * 1.609344 * 1000);
					} else if ($unit == "N") {
					    $distance = ($miles * 0.8684);
					} else {
					    $distance = $miles;
					}
					//'M' is statute miles (default)
					//'K' is kilometers
					//'N' is nautical miles


					$distanceInterval = 10; //this is how many units to cover before recording a point in the DB
					if ($distance > $distanceInterval) {
			        	//echo distance($latitude, $longitude, $latitudeLast, $longitudeLast, "M")."<br>";
						//print $distance;

						//Calculate Speed
						$secondsLastPoint = strtotime($datetime) - strtotime($datetimeLast);
						$metersPerSecond = ($distance / $secondsLastPoint);
						$mph = $metersPerSecond * 2.236936;

						if ($mph > $maxSpeed) {
							$maxSpeed = $mph;
							$newTopSpeed = 1;//bolds the line on the page.
							$message = "<strong>".$secondsLastPoint." seconds; ".round($distance, 2)." meters; ".round($metersPerSecond, 3)." meters per sec; ".round($mph, 2)." miles per hour</strong><br><br>";
						}else{
							$newTopSpeed = 0;
							$message = $secondsLastPoint." seconds; ".round($distance, 2)." meters; ".round($metersPerSecond, 3)." meters per sec; ".round($mph, 2)." miles per hour<br><br>";
						}
						echo $message;

						if ($mph > 0 AND $mph < 4){
							$walkScore++;
						}elseif ($mph >= 5 AND $mph < 15){
							$bikeScore++;
						}else {
							$otherScore++;
						}

						$latitudeLast = $latitude;
						$longitudeLast = $longitude;
						$distLastPointMeters = round($distance, 2);

						$distLastPointMeters = round($distance, 2);
						$mph = round($mph, 2);
						$datetimeLast = $datetime;

						$sql="INSERT INTO $table (datetime, type, latitude, longitude, name, distLastPointMeters, filename, secondsLastPoint, mph, newTopSpeed) VALUES ('$datetime', '$type', '$latitude', '$longitude', '$name', '$distLastPointMeters', '$filename', '$secondsLastPoint', '$mph', '$newTopSpeed')";

						if ($mysqli->query($sql)) {
							//could print somehtign to screen
			        	} else {
			            	echo "Error: " . $sql . "<br>" . mysqli_error($mysqli);
			        	};
			    	};//end if distance > $distanceInterval
			    }//end testing of distance;
			};
		};
	};




	$totalScore = $walkScore + $bikeScore + $otherScore;

	$walkScorePercent = ($walkScore / $totalScore)*100;
	$bikeScorePercent = ($bikeScore / $totalScore)*100;
	$otherScorePercent = ($otherScore / $totalScore)*100;

$totalTime = strtotime($datetime) - strtotime($startTime);
$totalTime = $totalTime /60;
print $totalTime."<br>";
$totalTime_Min = floor($totalTime);
$totalTime_Sec = ($totalTime - $totalTime_Min)*60;
$totalTime = $totalTime_Min.":".$totalTime_Sec;


$totalDistance = $milesTotal;

print "<hr>Total Distance: ".$totalDistance."<br>Total Time: ".$totalTime;


	print "<br><br>---------- The max speed was ".round($maxSpeed,2). " MPH ----------<br> Percent walking  (under 4 MPH): ".round($walkScorePercent,2)."<br> Percent biking (5 to 15 MPH): ".round($bikeScorePercent,2)."<br> Other: ".round($otherScorePercent,2)."<br><br>";


/*

	$sql = "UPDATE $table SET typeDerived='$typeDerived' WHERE filename='$filename'";

	if ($mysqli->query($sql) === TRUE) {
	    echo "Records for derivedType updated successfully in table ky_route! <br><br>";
	} else {
	    echo "Error updating record: " . $mysqli->error;
	};

	$sql = "UPDATE ky_route_all SET typeDerived='$typeDerived' WHERE filename='$filename'";

	if ($mysqli->query($sql) === TRUE) {
	    echo "Records for derivedType updated successfully in table ky_route_all!<br>";
	} else {
	    echo "Error updating record: " . $mysqli->error;
	};

*/


unset($gpx);
};




/*
	//http://www.geodatasource.com/developers/javascript

function distance($lat1, $lon1, $lat2, $lon2, $unit) {

$theta = $lon1 - $lon2;
$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
$dist = acos($dist);
$dist = rad2deg($dist);
$miles = $dist * 60 * 1.1515;
$unit = strtoupper($unit);

if ($unit == "K") {
  return ($miles * 1.609344);
  } else if ($unit == "M") {
        return ($miles * 1.609344 * 1000);
  } else if ($unit == "N") {
      return ($miles * 0.8684);
  } else {
      return $miles;
  }
}

//echo distance(32.9697, -96.80322, 29.46786, -98.53506, "M") . " Miles<br>";
//echo distance(32.9697, -96.80322, 29.46786, -98.53506, "K") . " Kilometers<br>";
//echo distance(32.9697, -96.80322, 29.46786, -98.53506, "N") . " Nautical Miles<br>";


:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
::                                                                         ::
::  This routine calculates the distance between two points (given the     ::
::  latitude/longitude of those points). It is being used to calculate     ::
::  the distance between two locations using GeoDataSource(TM) Products    ::
::                                                                         ::
::  Definitions:                                                           ::
::    South latitudes are negative, east longitudes are positive           ::
::                                                                         ::
::  Passed to function:                                                    ::
::    lat1, lon1 = Latitude and Longitude of point 1 (in decimal degrees)  ::
::    lat2, lon2 = Latitude and Longitude of point 2 (in decimal degrees)  ::
::    unit = the unit you desire for results                               ::
::           where: 'M' is statute miles (default)                         ::
::                  'K' is kilometers                                      ::
::                  'N' is nautical miles                                  ::
::  Worldwide cities and other features databases with latitude longitude  ::
::  are available at http://www.geodatasource.com                          ::
::                                                                         ::
::  For enquiries, please contact sales@geodatasource.com                  ::
::                                                                         ::
::  Official Web site: http://www.geodatasource.com                        ::
::                                                                         ::
::         GeoDataSource.com (C) All Rights Reserved 2015                      ::
::                                                                         ::
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
*/

  ?>


  </body>
</html>