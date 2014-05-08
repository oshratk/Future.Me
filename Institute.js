//define an institute - ID and name in the approrpiate language
function Institute() {
    var ID;
    var name;

    function fromJsonObj(jsonObj) {
        this.ID = jsonObj.ID;
        this.name = jsonObj.name;
    }
    this.fromJsonObj = fromJsonObj;
};
