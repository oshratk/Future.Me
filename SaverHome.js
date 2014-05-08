//this method triggered when the server return the products list
function OnServerSentProducts(productsStr) {
    //deserialzie the products from the json string
    var products = new UserProducts();
    products.fromJson(productsStr);

    //preset them as a table
    var jsonStr = products.toJSon();
    var userWithPropertiesOnly = JSON.parse(jsonStr);
    document.getElementById("products").innerHTML = toTable(userWithPropertiesOnly);
}
function main() 
{
    //print the user data
    user.fromSession(); //deserialize the user from the session
    var tableStr = objToTable(user); //serialize the user into table
    document.getElementById("user details").innerHTML = tableStr; //preset the table in the HTML doc

    //print the products data
    var xmlhttp;
    if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    }
    else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function () { if (xmlhttp.readyState == 4 && xmlhttp.status == 200) OnServerSentProducts(xmlhttp.responseText); }
    xmlhttp.open("POST", "SaverProducts.php", true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send("userData=" + user.toJSon());
}
$(document).ready(main); //main entry: attach the document-ready event to the main() method