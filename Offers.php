<?php
include 'Common.php';
include 'User.php';
include 'Tracy.php';
class Offer
{
    var $institute;
    var $managementFee;
    var $offerStatus;
    var $submittion;
    function __construct($institute, $managementFee, $offerStatus, $submittion)
    {
        $this->institute = $institute;
        $this->managementFee = $managementFee;
        $this->offerStatus = $offerStatus;
        $this->submittion = $submittion;
    }
};

class ProductOffer
{
    var $ID;//the product id
    var $institute;
    var $currentManagementFee;
    var $offers = array();
    function __construct($ID)
    {
        $this->ID = $ID;
    }
    function fromDb($conn, $user)
    {
        //get the product's insititute
        $sql_searchProducts = "select * from products where UserID = " . $user->ID . " and ProductID = " . $this->ID;
        //echo("searching for this: " . $sql_searchProducts);
        $result = mysqli_query($conn, $sql_searchProducts);
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
        $this->currentManagementFee = $row['value'];
        
        //get all the alternative offers for this product id
        $sql_alternatives = "select * from offers where userId = " . $user->ID . " and ProductID = " . $this->ID;
        $result = mysqli_query($conn, $sql_alternatives);
        while($row = mysqli_fetch_array($result))
        {
            $instituteID = $row['InstituteID'];
            $institute = new Institute($instituteID);
            $institute->fromDb($conn);
            $managementFee = $row['ManagementFee'];
            $offerStatus = $row['OfferStatus'];
            $submittion = $row['Submittion'];
            $submittionUnix = strtotime($submittion);//convert to unix-timesamp
            $offer = new Offer($institute, $managementFee, $offerStatus, $submittion);
            $this->offers[count($this->offers)] = $offer;
        }
        return TRUE;
       
    }
};
class ProductTypeOffer
{
    var $ID;//the product type
    var $name;
    var $productOffers = array();
    function __construct($ID)
    {
        $this->ID = $ID;
    }
    function fromDb($conn, $user)
    {
        //get the product type name using the product type id
        $sql_searchProcutTypeName = "select ProductTypeName_He from producttypes where ProductTypeID = " . $this->ID;
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


        //get all products offers for this product type, for this user
        $sql_searchProducts = //$user->institute == NULL ? 
            "select distinct ProductID from offers where userID = " . $user->ID . " and ProductID in (select distinct ProductID from products where userID = " . $user->ID . " and ProductTypeID = " . $this->ID . ")" ;//:
            //"select distinct Pro-ductID from offers where InstituteID = " . $user->institute->ID . " and ProductID in (select distinct ProductID from products where InstituteID = " . $user->institute->ID .  " and ProductTypeID = " . $this->ID . ")";
        $result = mysqli_query($conn, $sql_searchProducts);
        while($row = mysqli_fetch_array($result))
        {
            $ProductID = $row['ProductID'];
            $productOffer = new ProductOffer($ProductID);
            $productOffer->fromDb($conn, $user);
            $this->productOffers[count($this->productOffers)] = $productOffer;
        }
        return TRUE;
    }
};

class Offers
{
    var $productTypeOffers = array();
    function fromDb($conn, $user)
    {
        //get all offers product-types
         $sql_searchProcutTypes = 
            //$user->institute == NULL ? 
            "select distinct ProductTypeID from products where ProductID in (select distinct ProductID from offers where userId = " . $user->ID . ")" ;//:
            //"select distinct ProductTypeID from products where ProductID in (select distinct ProductID from offers where InstituteID = " . $user->institute->ID . ")";
         $result = mysqli_query($conn,$sql_searchProcutTypes);

         //for each product type, create a ProductTypeOffer object
         while($row = mysqli_fetch_array($result))
         {
            $productTypeId = $row['ProductTypeID'];
            $productTypeOffer = new ProductTypeOffer($productTypeId);
            $productTypeOffer->fromDb($conn, $user);
            $this->productTypeOffers[count($this->productTypeOffers)] = $productTypeOffer;
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
    $offers = new Offers();
    $offers->fromDb($conn, $user);
    $jsonStr = $offers->toJson();
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


