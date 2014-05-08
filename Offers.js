//this method triggered when the server return the products list
function OnServerSentResult(resultStr) {

    //present them as a table
    //var jsonStr = offers.toJSon();
    var objWithPropertiesOnly = JSON.parse(resultStr);
    document.getElementById("offers").innerHTML = toTable(objWithPropertiesOnly);
    //document.getElementById("offers").innerHTML = resultStr;

}
function main() {
    //print the user data
    user.fromSession(); //deserialize the user from the session
    var tableStr = objToTable(user); //serialize the user into table
    document.getElementById("user details").innerHTML = tableStr; //preset the table in the HTML doc

    //print the prediction data
    var xmlhttp;
    if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    }
    else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function () { if (xmlhttp.readyState == 4 && xmlhttp.status == 200) OnServerSentResult(xmlhttp.responseText); }
    xmlhttp.open("POST", "Offers.php", true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send("userData=" + user.toJSon());
                
}
$(document).ready(main); //main entry: attach the document-ready event to the main() method