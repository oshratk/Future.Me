<?php
include 'Common.php';
include 'User.php';
include 'Tracy.php';

class ProductTender
{
    var $ID;//the product id
    var $openTime;
    var $closureTime;
    var $payment;
    var $sBirthDate;
    var $sStatus;
    var $sGender;
    var $sOccupation;
    var $managementFee;
    var $employer;
    var $employee;
    var $totalValue;
    //var $isMine;
    var $status;
    
    function __construct($ID)
    {
        $this->ID = $ID;
    }
    function fromDb($conn, $user)
    {
        //get the saver id
        
        $sql_saverId = "select * from Tenders where ProductID = " . $this->ID;
        

        $result = mysqli_query($conn, $sql_saverId);
        $resultNumber = mysqli_num_rows($result);
        if ($resultNumber != 1)
        {
            OH_NO("Failed to get the user for for this product " . $this->ID);
            return FALSE;
        }
        $row = mysqli_fetch_array($result);   
        $saverId = $row['UserID'];
        $this->openTime = $row['OpenTime'];
        $this->closureTime = $row['CloseTime'];
        $this->payment = $row['Payment'];
      
        
        //get the details about that user id
        $sql_detailsOnSaverId = "select * from users where ID = " . $saverId;
        $result = mysqli_query($conn, $sql_detailsOnSaverId);
        $resultNumber = mysqli_num_rows($result);
        if ($resultNumber != 1)
        {
            OH_NO("Failed to get the user details for for this user " . $saverId);
            return FALSE;
        }
        $row = mysqli_fetch_array($result);   
        $this->sBirthDate = $row['BirthDate'];
        $this->sStatus = $row['Status'];
        $this->sGender = $row['Gender'];
        $this->sOccupation = $row['Occupotion'];
        
        
        //get the last management fee of this user for this product:
        $sql_lastManagementFee = "select value from product_events where ProductID = " . $this->ID . " and userID = " . $saverId . " and eventType = 2 order by date DESC limit 1";
        $result = mysqli_query($conn, $sql_lastManagementFee);
        $resultNumber = mysqli_num_rows($result);
        if ($resultNumber != 1)
        {
            OH_NO("Failed to get the last managment fee for this product " . $this->ID);
            return FALSE;
        }
        $row = mysqli_fetch_array($result);   
        $this->managementFee = $row['value'];

        //get the last empoyer commit
        $sql_lastEmployerCommit = "select value from product_events where ProductID = " . $this->ID . " and userID = " . $saverId . " and eventType = 0 order by date DESC limit 1";
        $result = mysqli_query($conn, $sql_lastEmployerCommit);
        $resultNumber = mysqli_num_rows($result);
        if ($resultNumber != 1)
        {
            OH_NO("Failed to get the last employer commit for this product " . $this->ID);
            return FALSE;
        }
        $row = mysqli_fetch_array($result);   
        $this->employer = $row['value'];

        //get the last employee commit
        $sql_lastEmployeeCommit = "select value from product_events where ProductID = " . $this->ID . " and userID = " . $saverId . " and eventType = 1 order by date DESC limit 1";
        $result = mysqli_query($conn, $sql_lastEmployeeCommit);
        $resultNumber = mysqli_num_rows($result);
        if ($resultNumber != 1)
        {
            OH_NO("Failed to get the last employee commit for this product " . $this->ID);
            return FALSE;
        }
        $row = mysqli_fetch_array($result);   
        $this->employee = $row['value'];
        
        //get the total value of this product id for this saver 
        $sql_totalValue =  "select Total from products where UserID = " . $saverId . " and ProductID = " . $this->ID;
        $result = mysqli_query($conn, $sql_totalValue);
        $resultNumber = mysqli_num_rows($result);
        if ($resultNumber != 1)
        {
            OH_NO("Failed to get the total value for this product " . $this->ID);
            return FALSE;
        }
        $row = mysqli_fetch_array($result);   
        $this->totalValue = $row['Total'];
        
        //is this product is mine ? get the institution id of this product
        $sql_institutionIdForThisProduct =  "select InstitutionalID from products where UserID = " . $saverId  . " and ProductID = " . $this->ID;
        $result = mysqli_query($conn, $sql_institutionIdForThisProduct);
        $resultNumber = mysqli_num_rows($result);
        if ($resultNumber != 1)
        {
            OH_NO("Failed to get the institute id for this product " . $this->ID);
            return FALSE;
        }
        $row = mysqli_fetch_array($result);   
        $productInsituteId = $row['InstitutionalID'];
        //and compare it with the user's institute
        $userInstituteId = $user->institute->ID;
        //figure out - why not presented as table ? $this->isMine = ($userInstituteId == $productInsituteId);
        
        
        //check the status of that tender for the user's institute according to the offers' table
        $sql_offerStatus =   "select OfferStatus from offers where ProductID = " . $this->ID . " and InstituteID = " . $userInstituteId;
        $result = mysqli_query($conn, $sql_offerStatus);
        $row = mysqli_fetch_array($result); 
        $this->status = $row['OfferStatus'];
        if ($this->status == NULL) $this->status = -1;//no offers submitted by my institute
        
        return TRUE;

    }
};
class ProductTypeTender
{
    var $ID;//the product type
    var $name;
    var $productTenders = array();
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


        //get all products tenders for this product type
        $sql_searchProducts = "select distinct ProductID from tenders where ProductID in (select distinct ProductID from products where ProductTypeID = " . $this->ID . ")";
        $result = mysqli_query($conn, $sql_searchProducts);
        while($row = mysqli_fetch_array($result))
        {
            $ProductID = $row['ProductID'];
            $productTender = new ProductTender($ProductID);
            $productTender->fromDb($conn, $user);
            $this->productTenders[count($this->productTenders)] = $productTender;
        }
        return TRUE;
    }
};

class Tenders
{
    var $productTypeOffers = array();
    function fromDb($conn, $user)
    {
        //get all tenders product-types
         $sql_searchProcutTypes = "select distinct ProductTypeID from products where ProductID in (select distinct ProductID from tenders)";
         $result = mysqli_query($conn,$sql_searchProcutTypes);

         //for each product type, create a ProductTypeOffer object
         while($row = mysqli_fetch_array($result))
         {
            $productTypeId = $row['ProductTypeID'];
            $productTypeTender = new ProductTypeTender($productTypeId);
            $productTypeTender->fromDb($conn, $user);
            $this->productTypeOffers[count($this->productTypeOffers)] = $productTypeTender;
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
    $tenders = new Tenders();
    $tenders->fromDb($conn, $user);
    $jsonStr = $tenders->toJson();
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


