/* Special function to call module-specific 
 * methods or functions
 * for myownradio.biz project */

var callModuleFunction = function() {
    if(arguments.length === 0) { return false; }
    var ns = Array.prototype.shift.apply(arguments).split(".");
    var fn = ns.pop();
    var context = window;
    for (var i = 0; i < ns.length; i++) {
        context = context[ns[i]];
    }
    if(typeof context === "undefined") {
        console.log("Warning: function \"" + fn + "\" does not exists!");
        return;
    }
    if(typeof context[fn] === "function") {
        //console.log("Called " + context[fn], arguments);
        return context[fn].apply(this, arguments);
    } else { 
        console.log("Warning: function \"" + fn + "\" does not exists!");
    }
};
