
var Promise = function (callback) {

    var that = function (callback) {

        var success = function () {
                return onSuccess.apply(null, arguments);
            },
            reject = function () {
                return onReject.apply(null, arguments);
            },
            onSuccess = function () {},
            onReject = function () {};

        callback(success, reject);

        return {
            then: function (s, r) {
                if (typeof s == "function") {
                    onSuccess = s;
                }
                if (typeof r == "function") {
                    onReject = r;
                }
            }
        }

    };

    return that(callback);

};

var func = function (success, rejext) {
    window.setTimeout(function () {
        console.log("Hello");
        success("Hello");
    }, 1000);
};

Promise(func).then(function () {
    return Promise(func);
});