<?php
include 'Common.php';
include 'User.php';
include 'Tracy.php';


class ProductPrediction
{
    var $ID;  
    var $institute;
    var $predictedManagementFee;
    var $optimalManagementFee;
    function __construct($ID)
    {
        $this->ID = $ID;
    }
    function fromDb($conn, $user)
    {
        //get the product's insititute
        $sql_searchProcuts = "select * from products where UserID = " . $user->ID . " and ProductID = " . $this->ID;
        //echo("searching for this: " . $sql_searchProcuts);
        $result = mysqli_query($conn, $sql_searchProcuts);
        $resultNumber = mysqli_num_rows($result);
        if ($resultNumber == 1)
        {
            $row = mysqli_fetch_array($result);
            $instituteId = $row['InstitutionalID'];
            $institute = new Institute($instituteId);
            $institute->fromDb($conn);
            $this->institute = $institute;
        }
        else
        {
            OH_NO("Failed to find product id " . $this->ID);
            return FALSE;//error, failed to find the 
        }
        
        //get the optimal management fee for this product:
        $sql_optimalManagementFee = "select value from product_events where ProductID = " . $this->ID . " and eventType = 2 order by value DESC limit 1";
        $result = mysqli_query($conn, $sql_optimalManagementFee);
        $resultNumber = mysqli_num_rows($result);
        if ($resultNumber != 1)
        {
            OH_NO("Failed to get the optimial managment fee for this product " . $this->ID);
            return FALSE;
        }
        $row = mysqli_fetch_array($result);   
        $optimalManagementFee = $row['value'];

        //get the last management fee of this user for this product:
        $sql_lastManagementFee = "select value from product_events where ProductID = " . $this->ID . " and userID = " . $user->ID . " and eventType = 2 order by date DESC limit 1";
        $result = mysqli_query($conn, $sql_lastManagementFee);
        $resultNumber = mysqli_num_rows($result);
        if ($resultNumber != 1)
        {
            OH_NO("Failed to get the last managment fee for this product " . $this->ID);
            return FALSE;
        }
        $row = mysqli_fetch_array($result);   
        $lastManagementFee = $row['value'];
        
        //get the birthday of this user
        $sql_getAge = "select BirthDate from users where ID = " . $user->ID;
        $result = mysqli_query($conn, $sql_getAge);
        $resultNumber = mysqli_num_rows($result);
        if ($resultNumber != 1)
        {
            OH_NO("Failed to get age of user " . $user->ID);
            return FALSE;
        }
        $row = mysqli_fetch_array($result);   
        $birthDate = $row['BirthDate'];//get the date in mysql format
        $birthDateUnix = strtotime($birthDate);//convert to unix-timesamp
        $retirementDate = new  Datetime();//convert into php datetime object
        $retirementDate->setTimestamp($birthDateUnix);
        $retirementDate->add(DateInterval::createFromDateString('67 years'));//add 67 years from birth time
        
        //calculate the diff between the retirement date and today (in month)
        $today = getdate()[0];
        $yearRetirementDate = $retirementDate->format('Y');
        $yearToday = date('Y', $today);
        $monthRetirementDate = $retirementDate->format('m');
        $monthToday  = date('m', $today);
        $diff = (($yearRetirementDate - $yearToday) * 12) + ($monthRetirementDate - $monthToday);
        
        $this->predictedManagementFee = -$lastManagementFee*$diff;
        $this->optimalManagementFee = -$diff*$optimalManagementFee;
        return TRUE;
       
    }
};
class ProductTypePrediction
{
    var $ID;
    var $name;
    var $predictedManagementFee;
    var $optimalManagementFee;
    var $productPredictions = array();
    function __construct($ID)
    {
        $this->ID = $ID;
    }
    function fromDb($conn, $user)
    {
        $this->predictedManagementFee = 0;
        $this->optimalManagementFee = 0;
        //get the product type name using the product type id
        $sql_searchProcutTypeName = "select ProductTypeName_He from producttypes where ProductTypeID = " . $this->ID;
        //echo("searching for this: " . $sql_searchProcutType);
        $result = mysqli_query($conn,$sql_searchProcutTypeName);
        $resultNumber = mysqli_num_rows($result);
        if ($resultNumber == 1)
        {
            $row = mysqli_fetch_array($result);
            $this->name = $row['ProductTypeName_He'];
        }
        else
        {
            OH_NO("Failed to find the product type id of " . $this->ID);
            return FALSE;//error, failed to find the 
        }


        //get all products of this product type for this user
        $sql_searchProducts = "select ProductID from products where UserID = " . $user->ID . " and ProductTypeID = " . $this->ID;
        $result = mysqli_query($conn, $sql_searchProducts);
        while($row = mysqli_fetch_array($result))
        {
            $ProductID = $row['ProductID'];
            $productPrediction = new ProductPrediction($ProductID);
            $productPrediction->fromDb($conn, $user);
            $this->predictedManagementFee += $productPrediction->predictedManagementFee;
            $this->optimalManagementFee += $productPrediction->optimalManagementFee;
            $this->productPredictions[count($this->productPredictions)] = $productPrediction;
        }
        
    }
};

class Prediction
{
    var $productTypePredictions = array();
    function fromDb($conn, $user)
    {
        //get the products of this user
         $sql_searchProcutTypes = "select ProductTypeID from products where UserID = " . $user->ID;
         $result = mysqli_query($conn,$sql_searchProcutTypes);

         //get the product types managed for this user
         $productTypeIds = array();
         $productTypeIdsLastIndex = 0;
         while($row = mysqli_fetch_array($result))
         {
            $productTypeID = $row['ProductTypeID'];
            if (!in_array($productTypeID, $productTypeIds))
            {
                $productTypeIds[$productTypeIdsLastIndex] = $row['ProductTypeID'];
                ++$productTypeIdsLastIndex;
            }
         }

         //for each product type, create a ProductTypePrediction object
         for($i=0; $i < count($productTypeIds); ++$i)
         {
            $productTypeId = $productTypeIds[$i];
            $productTypePrediction = new ProductTypePrediction($productTypeId);
            $productTypePrediction->fromDb($conn, $user);
            $this->productTypePredictions[count($this->productTypePredictions)] = $productTypePrediction;
          }
         
    }
    function toJson()
    {
        $jsonStr = json_encode($this);
        return $jsonStr; 
    }
};


//connect to the dabase
//$conn = mysqli_connect("aaeg2vjwjgn39p.cx9qdt4hoxlq.us-east-1.rds.amazonaws.com","root","futureme","ebdb");
$queryResult = FALSE;

if( $conn ) 
{
    $jsonStr = $_POST['userData'];//get the encoded person    
    $user = new User();
    $user->fromJson($jsonStr);
    $prediction = new Prediction();
    $prediction->fromDb($conn, $user);
    $jsonStr = $prediction->toJson();
    echo($jsonStr);
    
}
else
{
     echo "Failed to connect to MySQL: " . mysqli_connect_error();
     die( print_r( sqlsrv_errors(), true));
}

//close connection
mysqli_close ($conn);
?>


