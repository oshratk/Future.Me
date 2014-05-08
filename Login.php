<?php
    
include 'Common.php';
include 'User.php';
//get the login data
$jsonStr = $_GET['loginData'];//get the encoded person    
//connect to the dabase
//$conn = mysqli_connect("localhost","root","futureme","SaveTheFuture");
//$conn = mysqli_connect("aaeg2vjwjgn39p.cx9qdt4hoxlq.us-east-1.rds.amazonaws.com","root","futureme","ebdb");
$queryResult = FALSE;
$user = new User();
if( $conn ) 
{
  
    $user->fromJson($jsonStr);
    
    $queryResult = $user->fromDb($conn);
   
}
else
{
     echo "Failed to connect to MySQL: " . mysqli_connect_error();
     die( print_r( sqlsrv_errors(), true));
}

if ($queryResult)
{
    echo($user->toJson());
}
else
{

    echo("הנתונים שהזנת שגויים");
}
//close connection
mysqli_close ($conn);


?>

