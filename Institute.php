<?php
class Institute
{
    var $ID;
    var $name;
    function __construct($ID)
    {
        $this->ID = $ID;
    }
    function fromDb($conn)
    {
        //get the institute id and name
        $sql_searchInstitute = "select * from institutions where InstituteID = " . $this->ID;
        //echo("searching for this: " . $sql_searchInstitute);
        $result = mysqli_query($conn, $sql_searchInstitute); 
        $resultNumber = mysqli_num_rows($result);
        if ($resultNumber == 1)
        {
            $row = mysqli_fetch_array($result);
            $this->name = $row['InstituteName_He'];
            return TRUE;
        }
        else
        {
            OH_NO("Failed to find institute id " . $this->ID);
            return FALSE;//error, failed to find the 
        }
    }  
};

?>

