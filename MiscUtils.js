//transpose an object to a presentable table, and return the table's string

function toTable(userWithPropertiesOnly) {
    //in case its an array - we'll just concat tables of each of the array's cells:
    var isArray = userWithPropertiesOnly instanceof Array;
    if (isArray) {
        var tableStr = "<table>";
        for (var i = 0; i < userWithPropertiesOnly.length; ++i) {
            tableStr += "<tr><td>";
            tableStr += toTable(userWithPropertiesOnly[i]);
            tableStr += "</td></tr>";
        }
        tableStr += "</table>";
        return tableStr;
    }
    //get the properties names and values of this object
    var propertyNames = Object.getOwnPropertyNames(userWithPropertiesOnly);
    var propertyValues = Object.keys(userWithPropertiesOnly);

    //write a table that represent it
    var tableStr = "<table border=1 align=right>";
    //print the row for the properties names
    tableStr += "<tr align=right>";
    for (var i in propertyNames) {
        tableStr += "<td align=right>" + propertyNames[i] + "</td>";
    }
    tableStr += "</tr>"

    //print the row for the properties values
    tableStr += "<tr>";
    for (var i in propertyNames) {

        tableStr += "<td align=right>";
        var propertyAsStr = userWithPropertiesOnly[propertyValues[i]];
        var str = (propertyAsStr == null) ? "null" : propertyAsStr.toString();
        if (str.search("Object") != -1) {
            tableStr += toTable(userWithPropertiesOnly[propertyValues[i]]);
        }
        else {
            tableStr += str;
        }
        tableStr += "</td>";
    }
    tableStr += "</tr>";
    tableStr += "</table>";
    return tableStr;
}

function objToTable(obj)
{
    var jsonStr = JSON.stringify(obj);
    var objWithPropertiesOnly = JSON.parse(jsonStr);
    return toTable(objWithPropertiesOnly);
}