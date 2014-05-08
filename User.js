//an object represent a user
function User() 
{
    //user properties
    this.firstName = "";
    this.lastName = "";
    this.ID = "";
    this.password = "";
    this.userType = -1;
    this.institute = null;
    //deserialize a user from HTML input tags
    function fromHTML() 
    {
        this.firstName = $("#firstName").val();
        this.lastName = $("#lastName").val();
        this.ID = $("#ID").val();
        this.password = $("#password").val();
    }
    this.fromHTML = fromHTML;

    //serialize user into a JSON string
    function toJSon() 
    {
        jsonStr = JSON.stringify(this);
        return jsonStr;
    }
    this.toJSon = toJSon;
                
    //deserialize user from JSON string
    function fromJSon(jsonStr) 
    {
        var jsonObj = JSON.parse(jsonStr);
        this.firstName = jsonObj.firstName;
        this.lastName = jsonObj.lastName;
        this.ID = jsonObj.ID;
        this.password = jsonObj.password;
        this.userType = jsonObj.userType;
        if (jsonObj.institute != null) {
            this.institute = new Institute();
            this.institute.fromJsonObj(jsonObj.institute);
        }
        else
        {
            this.institute = -1;
        }
    }
    this.fromJSon = fromJSon;

    //deserialize the user from the session variable
    function fromSession()
    {
        var jsonStr = sessionStorage.getItem("user");
        this.fromJSon(jsonStr);
    }
    this.fromSession = fromSession;

}
user = new User;