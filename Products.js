
//define a product event - a commit of an employer/employee, a management-fee, or the total sum for a particular date
function ProductEvent() {
    var eventType; //0: empoyer, 1: employee, 2: management fee, 3: total
    var value;//the value of this event
    var date;//the date of the event

    function fromJsonObj(jsonObj) {
        this.eventType = jsonObj.eventType;
        this.value = jsonObj.value;
        this.date = jsonObj.date;
    }
    this.fromJsonObj = fromJsonObj;
};

//define a product
function Product() {
    var ID;//the id of the product
    var total;//the current total sum of this product
    var institute;//the institute object
    var events;//an array of the last XX (10 by default) events
    var grade;//the grade [0-5] given by the server
    function fromJsonObj(jsonObj) {
        //get the product's total and insititute
        this.ID = jsonObj.ID;
        this.total = jsonObj.total;
        this.grade = jsonObj.grade;
        this.institute = new Institute();
        this.institute.fromJsonObj(jsonObj.institute);
        this.events = new Array();
        for (var i = 0; i < jsonObj.events.length; ++i) {
            var productEvent = new ProductEvent();
            productEvent.fromJsonObj(jsonObj.events[i]);
            this.events[i] = productEvent;
        }
    }
    this.fromJsonObj = fromJsonObj;
};

//define a product type, and its products
function ProductType() {
    var ID;//id of product type
    var name;//its name 
    var productsOfThisType;//an array of prodcuts assocaited with this product type
    var grade;//the grade given by the server for this type
    function fromJsonObj(jsonObj) {

        this.ID = jsonObj.ID;
        this.name = jsonObj.name;
        this.grade = jsonObj.grade;
        this.productsOfThisType = new Array();
        for (var i = 0; i < jsonObj.productsOfThisType.length; ++i) {
            var product = new Product();
            product.fromJsonObj(jsonObj.productsOfThisType[i]);
            this.productsOfThisType[i] = product;
        }
    }
    this.fromJsonObj = fromJsonObj;

};

//a container for all the user products, by means of products types
function UserProducts() {
    this.productTypes;//an array of products types objects

    function fromJson(jsonStr) {
        this.productTypes = new Array();
        var jsonObj = JSON.parse(jsonStr);
        for (var i = 0; i < jsonObj.productTypes.length; ++i) {
            var productType = new ProductType();
            productType.fromJsonObj(jsonObj.productTypes[i]);
            this.productTypes[this.productTypes.length] = productType;
        }
        return;

    }
    this.fromJson = fromJson;

    function toJSon() {
        jsonStr = JSON.stringify(this);
        return jsonStr;
    }
    this.toJSon = toJSon;
};
