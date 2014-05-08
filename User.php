<?php
include 'Institute.php';    
class User
{
    var $firstName;
    var $lastName;
    var $ID;
    var $password;
    var $userType;
    var $institute;
    function fromJson($jsonStr)
    {
        $jsonUser = json_decode($jsonStr);//decode it into a php object
        $this->firstName = $jsonUser->firstName;
        $this->lastName = $jsonUser->lastName;
        $this->ID = $jsonUser->ID;
        $this->password = $jsonUser->password;
        $this->userType = -1;
        $this->institute = ($jsonUser->institute == -1 || $jsonUser->institute == NULL) ? NULL : $jsonUser->institute;
        //echo(var_dump($this));
    }
    function toJson()
    {
        $jsonStr = json_encode($this);
        return $jsonStr;
    }
    function fromDb($conn)
    {
        $sql_searchLogin = "select * from users where FirstName='" . $this->firstName . "' and LastName='" . $this->lastName . "' and ID = " . $this->ID;
        //echo("searching for this: " . $sql_searchLogin);
        $result = mysqli_query($conn,$sql_searchLogin);
        $resultNumber = mysqli_num_rows($result);
        if ($resultNumber != 1)
        {
            //we didn't find the user we were looking for, fill a bad user
            $this->firstName = "";
            $this->lastName = "";
            $this->ID =-1;
            $this->password = "";
            $this->userType = -1;
            return FALSE;
        }
        else
        {
            $row = mysqli_fetch_array($result);
            $this->firstName = $row['FirstName'];
            $this->lastName = $row['LastName'];
            $this->ID = $row['ID'];
            $this->password = $row['Password'];
            $this->userType = $row['UserType'];
            if ($this->userType == 1)//institutional user - get also the institute data
            {
                $instituteId = $row['InstituteID'];
                $institute = new Institute($instituteId);
                $institute->fromDb($conn);
                $this->institute = $institute;
            }
            else
            {
                $this->institute = NULL;
            }
            return TRUE;
        }
    }
 
};

?>
