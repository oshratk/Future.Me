function main() 
{
    //initializations
}

//client pressed the Login button, send a login request to the server
function OnClientLogin() 
{
    user.fromHTML();
    jsonStr = user.toJSon(); //JSON.stringify(key);
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () { if (xmlhttp.readyState == 4 && xmlhttp.status == 200) OnServerLogin(xmlhttp.responseText); }
    xmlhttp.open("GET", "Login.php?loginData=" + jsonStr + "", true);
    xmlhttp.send();
}

//OnServerLogin: Server sent the login respond
function OnServerLogin(serverRespond) 
{
    if (serverRespond.search("{") != -1) //if we have a JSON string object, that mean the login is succefull
    {   
        //succeeded - rederict to 'main entry'
        user.fromJSon(serverRespond);
        var userType = user.userType;
        sessionStorage.setItem("user", "" + user.toJSon());
        if (userType == 0) {//saver
            var newWindow = window.open("SaverHome.html", "_self");
        }
        else if (userType == 1) {//instituional
            var newWindow = window.open("InstituteHome.html", "_self");
        }
    }
    else 
    {
        //login failed - the server respond contains the reason, show it 
        document.getElementById("result").innerHTML = serverRespond;
    }
}
$(document).ready(main); //main entry: attach the document-ready event to the main() method