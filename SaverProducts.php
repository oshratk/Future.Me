<?php
include 'Common.php';
include 'User.php';
include 'Tracy.php';
class ProductEvent
{
    var $eventType;//0: empoyer, 1: employee, 2: management fee, 3: total
    var $value;
    var $date;

    function __construct($eventType, $value, $date)
    {
        $this->eventType = $eventType;
        $this->value = $value;
        $this->date = $date;
    }
};
class Product
{
    var $ID;  
    var $total;
    var $institute;
    var $events = array();
    var $grade;
    function __construct($ID)
    {
        $this->ID = $ID;
    }
    function fromDb($conn, $user)
    {
        //get the product's total and insititute
        $sql_searchProcuts = "select * from products where UserID = " . $user->ID . " and ProductID = " . $this->ID;
        //echo("searching for this: " . $sql_searchProcuts);
        $result = mysqli_query($conn, $sql_searchProcuts);
        $resultNumber = mysqli_num_rows($result);
        if ($resultNumber == 1)
        {
            $row = mysqli_fetch_array($result);
            $this->total = $row['Total'];
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
        
        //get the last 10 events of this products, for each event type
        $eventType = 0;
        while ($eventType < 4)
        {
            $sql_searchEventsOfType = "select eventType,value,date from product_events where userID = " . $user->ID . " and ProductID = " . $this->ID . " and eventType = ". $eventType . " order by date DESC limit 10";
            //echo($sql_searchEventsOfType);
            $result = mysqli_query($conn, $sql_searchEventsOfType);
            $resultNumber = mysqli_num_rows($result);
            while($row = mysqli_fetch_array($result))
            {
                $eventType = $row['eventType'];
                $value = $row['value'];
                $date = $row['date'];
                $productEvent = new ProductEvent($eventType, $value, $date);
                $this->events[count($this->events)] = $productEvent;
            }
            $eventType++;
        }
        
        //determine the grade of this product, based on the optimial management fee:
        //first, get the optimal management fee for this product:
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

        //second, get the last management fee of this user for this product:
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
        $ratio = $lastManagementFee/$optimalManagementFee;
        if ($ratio <= 1) $ratio = 0;
        if ($ratio > 5) $ratio = 5;
        $this->grade = 5 - $ratio;

        return TRUE;
       
    }
};
class ProductType
{
    var $ID;
    var $name;
    var $grade;
    var $productsOfThisType = array();
    function __construct($ID)
    {
        $this->ID = $ID;
    }
    function fromDb($conn, $user)
    {
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
        //echo("searching for this: " . $sql_searchProducts);
        $result = mysqli_query($conn, $sql_searchProducts);
        $totalGrade = 0;
        while($row = mysqli_fetch_array($result))
        {
            $ProductID = $row['ProductID'];
            $product = new Product($ProductID);
            $product->fromDb($conn, $user);
            $totalGrade += $product->grade;
            $this->productsOfThisType[count($this->productsOfThisType)] = $product;
        }
        if (count($this->productsOfThisType) > 0)
        {
            $this->grade =  $totalGrade / count($this->productsOfThisType);
        }
    }
};

class UserProducts
{
    var $productTypes = array();
    function fromDb($conn, $user)
    {
         $sql_searchProcutTypes = "select ProductTypeID from products where UserID = " . $user->ID;
         //echo("searching for this: " . $sql_searchProcutTypes);//@oror
         $result = mysqli_query($conn,$sql_searchProcutTypes);
         //$resultNumber = mysqli_num_rows($result);//@oror
         //echo("found " . $resultNumber . " rows");//@oror
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

         for($i=0; $i < count($productTypeIds); ++$i)
         {
            $productTypeId = $productTypeIds[$i];
            $productType = new ProductType($productTypeId);
            $productType->fromDb($conn, $user);
            //var_dump($productType);
            $this->productTypes[count($this->productTypes)] = $productType;
            
          }
          //echo(var_dump($productTypes));
    }
    function toJson()
    {
        //return var_dump($this);
        
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
    $products = new UserProducts();
    $products->fromDb($conn, $user);
    $jsonStr = $products->toJson();
    echo($jsonStr);
    
}
else
{
     echo "Failed to connect to MySQL: " . mysqli_connect_error();
     die( print_r( sqlsrv_errors(), true));
}
/*
if ($queryResult)
{
    echo($user->toJson());
}
else
{
    echo("הנתונים שהזנת שגויים");
}
*/
//close connection
mysqli_close ($conn);



?>


