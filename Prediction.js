
function ProductPrediction() {
    var ID;
    var institute;
    var predictedManagementFee;
    var optimalManagementFee;
    function fromJsonObj(jsonObj) {
        this.ID = jsonObj.ID;
        this.predictedManagementFee = jsonObj.predictedManagementFee;
        this.optimalManagementFee = jsonObj.optimalManagementFee;
        var institute = new Institute();
        institute.fromJsonObj(jsonObj.institute);
        this.institute = institute;
    }
    this.fromJsonObj = fromJsonObj;
};

function ProductTypePrediction() {
    var ID;
    var name;
    var predictedManagementFee;
    var optimalManagementFee;
    var productPredictions;

    function fromJsonObj(jsonObj) {
        this.ID = jsonObj.ID;
        this.name = jsonObj.name;
        this.predictedManagementFee = jsonObj.predictedManagementFee;
        this.optimalManagementFee = jsonObj.optimalManagementFee;
        this.productPredictions = new Array();
        for (var i = 0; i < jsonObj.productPredictions.length; ++i) {
            var productPrediction = new ProductPrediction();
            productPrediction.fromJsonObj(jsonObj.productPredictions[i]);
            this.productPredictions[this.productPredictions.length] = productPrediction;
        }
    }
    this.fromJsonObj = fromJsonObj;
};

function Prediction() {
    var productTypePredictions;
    function fromJson(jsonStr) {
        this.productTypePredictions = new Array();
        var jsonObj = JSON.parse(jsonStr);
        for (var i = 0; i < jsonObj.productTypePredictions.length; ++i) {
            var productTypePrediction = new ProductTypePrediction();
            productTypePrediction.fromJsonObj(jsonObj.productTypePredictions[i]);
            this.productTypePredictions[this.productTypePredictions.length] = productTypePrediction;
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