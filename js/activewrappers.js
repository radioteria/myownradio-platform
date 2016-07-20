/*!
 * ActiveWrappers v1.0.3
 *
 * Copyright (c) 2014, Roman Gemini <roman@homefs.biz>
 * Licensed under the Apache v2 License.
 *
 */

/** * @license Apache v2
 */

"use strict";

/**
 * EventHolder helper object
 */
var EventHolderHelper = {
    buildEventHolder: /*Object*/ function (/*Array*/ map) {
        var obj = {};
        map.forEach(function (event) {
            obj[event] = [];
        });
        return obj;
    },
    /**
     *  EventType Constants
     */
    eventType: {
        ADD: 1,      // 00000001
        REMOVE: 1 << 1, // 00000010
        UPDATE: 1 << 2, // 00000100
        CLEAR: 1 << 3, // 00001000
        SWAP: 1 << 4, // 00010000
        MOVE: 1 << 5, // 00100000
        SET: 1 << 6, // 01000000
        TOUCH: 0x0B,   // 00001011
        ANY: 0xFF    // 11111111
    },
    mergeObjects: function (objA, objB) {
        var tmp = objA;
        for (var key in objB) {
            tmp[key] = objB[key];
        }
        return tmp;
    }
};

/**
 *  Objects
 */
var ActiveWrapper = (function () {

    var Constr;

    Constr = function () {
    };

    Constr.prototype = {
        on: function (/*String*/ event, /*Function*/ callback, /*Number*/ filter) {
            return this.eventHolder.bind(event, callback, filter);
        }
    };

    return Constr;

}());


var EventHolder = (function () {

    var Constr;

    Constr = function (/*Array*/ events) {
        if (!(this instanceof Constr)) {
            throw new Error("Use 'new' operator");
        }
        this.eventPattern = events;
        this.init();
    };

    Constr.prototype = EventHolderHelper.mergeObjects(
        new ActiveWrapper, {
            bind: function (/*String*/ event, /*Function*/ callback, /*Number*/ filter) {
                this.globalTop(event);
                filter = filter || EventHolderHelper.eventType.ANY;
                var len = this.eventHolderMap[event].push({ f: filter, c: callback });
                var that = this;
                return {
                    unbind: function () {
                        that.eventHolderMap[event].splice(len - 1, 1);
                    }
                };
            },
            init: function () {
                this.eventHolderMap = EventHolderHelper.buildEventHolder(this.eventPattern);
            },
            call: function (/*String*/ event, /*Array*/ args, /*Number*/ filter) {
                this.globalTop(event);
                var map = this.eventHolderMap[event];

                ("undefined" == typeof filter
                    ? map
                    : map.filter(function (obj) {
                    return obj.f & filter;
                })
                    ).forEach(function (obj) {
                        obj.c.apply(null, args);
                    });
            },
            globalTop: function (/*String*/ event) {
                if (this.eventHolderMap[event] === undefined) {
                    throw new RangeError("Event '" + event + "' not exists!");
                }
            }
        });

    return Constr;

}());

var ActiveObject = (function (object) {

    var Constr;

    Constr = function (object) {
        if (!(this instanceof Constr)) {
            return new Constr(object);
        }
        this.eventHolder = new EventHolder(["remove", "clear", "set", "global"]);
        this.objectKeeper = ("object" == typeof object) ? object : {};
    };

    Constr.prototype = {
        onSet: function (/*Function*/ callback) {
            return this.eventHolder.bind("set", callback);
        },
        onRemove: function (/*Function*/ callback) {
            return this.eventHolder.bind("remove", callback);
        },
        onClear: function (/*Function*/ callback) {
            return this.eventHolder.bind("clear", callback);
        },
        onGlobal: function (/*Function*/ callback, /*Number*/ filter) {
            return this.eventHolder.bind("global", callback, filter);
        },
        set: function (/*Object*/ key, /*Object*/ newValue) {
            var oldValue = this.objectKeeper[key];
            this.objectKeeper[key] = newValue;
            this.eventHolder.call("set", [key, newValue, oldValue]);
            this.eventHolder.call("global",
                [EventHolderHelper.eventType.SET, [key, newValue, oldValue]],
                EventHolderHelper.eventType.SET);
            return this;
        },
        remove: function (/*Object*/ key) {
            if (typeof this.objectKeeper[key] !== "undefined") {
                delete this.objectKeeper[key];
                this.eventHolder.call("remove", [key]);
                this.eventHolder.call("global",
                    [EventHolderHelper.eventType.REMOVE, [key]],
                    EventHolderHelper.eventType.REMOVE);
            }
            return this;
        },
        clear: function () {
            this.objectKeeper = {};
            this.eventHolder.call("clear", []);
            this.eventHolder.call("global",
                [EventHolderHelper.eventType.CLEAR],
                EventHolderHelper.eventType.CLEAR);
        },

        // Getters
        get: function (/*Object*/ key) {
            return this.objectKeeper[key];
        },
        getKeys: function () {
            var keys = [];
            for (var key in this.objectKeeper) {
                keys.push(key);
            }
            return keys;
        },
        getObject: function () {
            return this.objectKeeper;
        },
        each: function (/*Function*/ callback) {
            var that = this;
            this.getKeys().forEach(function (/*Object*/ key) {
                callback.apply(null, [key, that.get(key)]);
            });
        },

        count: function () {
            return this.getKeys().length;
        },

        restore: function (selector, callback) {
            var that = this.objectKeeper;
            $(selector).livequery(function () {
                callback(this, that);
            });
        }
    };

    return Constr;

}());

var ActiveArray = (function () {

    var Constr;

    Constr = function (/*Array*/ array) {
        if (!(this instanceof Constr)) {
            return new Constr(array);
        }
        this.eventHolder = new EventHolder(["add", "update", "remove", "clear", "swap", "move", "global"]);
        this.arrayKeeper = "array" == typeof array ? array : [];
    };



    Constr.prototype = EventHolderHelper.mergeObjects(
        new ActiveWrapper(), {
            onAdd: function (/*Function*/ callback) {
                return this.eventHolder.bind("add", callback);
            },
            onUpdate: function (/*Function*/ callback) {
                return this.eventHolder.bind("update", callback);
            },
            onRemove: function (/*Function*/ callback) {
                return this.eventHolder.bind("remove", callback);
            },
            onClear: function (/*Function*/ callback) {
                return this.eventHolder.bind("clear", callback);
            },
            onSwap: function (/*Function*/ callback) {
                return this.eventHolder.bind("swap", callback);
            },
            onMove: function (/*Function*/ callback) {
                return this.eventHolder.bind("move", callback);
            },
            onGlobal: function (/*Function*/ callback, /*Number*/ filter) {
                return this.eventHolder.bind("global", callback, filter);
            },

            add: function (/*Object*/ value) {
                this.arrayKeeper.push(value);
                this.eventHolder.call("add", [value]);
                this.eventHolder.call("global",
                    [EventHolderHelper.eventType.ADD, [value]],
                    EventHolderHelper.eventType.ADD);
                return this;
            },
            addAll: function (/*Array*/ values) {
                var i;
                for (i = 0; i < values.length; i += 1) {
                    this.arrayKeeper.push(values[i]);
                }
                this.eventHolder.call("add", [values]);
                this.eventHolder.call("global",
                    [EventHolderHelper.eventType.ADD, [values]],
                    EventHolderHelper.eventType.ADD);
                return this;
            },
            addEvery: function (/*Array*/ values) {
                var i;
                for (i = 0; i < values.length; i += 1) {
                    this.add(values[i]);
                }
                return this;
            },
            remove: function (/*Number*/ index) {
                if (typeof this.arrayKeeper[index] == "undefined") {
                    throw new RangeError("Index out of bounds");
                }
                this.arrayKeeper.splice(index, 1);
                this.eventHolder.call("remove", [index]);
                this.eventHolder.call("global",
                    [EventHolderHelper.eventType.REMOVE, [index]],
                    EventHolderHelper.eventType.REMOVE);
                return this;
            },
            update: function (/*Number*/ index, /*Object*/ newValue) {
                if (typeof this.arrayKeeper[index] == "undefined") {
                    throw new RangeError("Index out of bounds");
                }
                var oldValue = this.arrayKeeper[index];
                this.arrayKeeper[index] = newValue;
                this.eventHolder.call("update", [index, newValue, oldValue]);
                this.eventHolder.call("global",
                    [EventHolderHelper.eventType.UPDATE, [index, newValue, oldValue]],
                    EventHolderHelper.eventType.UPDATE);
                return this;
            },
            swap: function (/*Number*/ one, /*Number*/ two) {
                if (typeof this.arrayKeeper[one] == "undefined" || typeof this.arrayKeeper[two] == "undefined") {
                    throw new RangeError("Index out of bounds");
                }

                var temp = this.arrayKeeper[one];
                this.arrayKeeper[one] = this.arrayKeeper[two];
                this.arrayKeeper[two] = temp;
                this.eventHolder.call("swap", [one, two]);
                this.eventHolder.call("global",
                    [EventHolderHelper.eventType.SWAP, [one, two]],
                    EventHolderHelper.eventType.SWAP);
                return this;
            },
            move: function (/*Number*/ from, /*Number*/ to) {
                if (typeof this.arrayKeeper[from] == "undefined" || typeof this.arrayKeeper[to] == "undefined") {
                    throw new RangeError("Index out of bounds");
                }

                var temp = this.arrayKeeper[from],
                    i = from;

                if (to < from) {
                    for (; i > to; i -= 1) {
                        this.arrayKeeper[i] = this.arrayKeeper[i - 1];
                    }
                } else {
                    for (; i < to; i += 1) {
                        this.arrayKeeper[i] = this.arrayKeeper[i + 1];
                    }
                }

                this.arrayKeeper[to] = temp;

                this.eventHolder.call("move", [from, to]);
                this.eventHolder.call("global",
                    [EventHolderHelper.eventType.MOVE, [from, to]],
                    EventHolderHelper.eventType.MOVE);
                return this;
            },
            clear: function () {
                this.arrayKeeper = [];
                this.eventHolder.call("clear", []);
                this.eventHolder.call("global",
                    [EventHolderHelper.eventType.CLEAR],
                    EventHolderHelper.eventType.CLEAR);
                return this;
            },

            // Getters
            get: function (/*Number*/ index) {
                if (typeof this.arrayKeeper[index] == "undefined") {
                    throw new RangeError("Index out of bounds");
                }
                return this.arrayKeeper[index];
            },
            each: function (/*Function*/ callback) {
                var i;
                for (i = 0; i < this.arrayKeeper.length; i += 1) {
                    callback(this.arrayKeeper[i]);
                }
            },

            // Special
            count: function () {
                return this.arrayKeeper.length;
            },
            toString: function () {
                var buffer = "array[", i;
                for (i = 0; i < this.arrayKeeper.length; i += 1) {
                    buffer += this.arrayKeeper[i].toString();
                }
                return buffer + "]";
            },

            restore: function (selector, callback) {
                var that = this.arrayKeeper;
                $(selector).livequery(function () {
                    callback(this, that);
                });
            }
        });

    return Constr;

}());

var ActiveVariable = (function () {

    var Constr;

    Constr = function (value) {
        if (!(this instanceof Constr)) {
            return new Constr(value);
        }
        this.eventHolder = new EventHolder(["set"]);
        this.variableKeeper = value;
    };

    Constr.prototype = EventHolderHelper.mergeObjects(new ActiveWrapper, {
        onSet: function (/*Function*/ callback) {
            return this.eventHolder.bind("set", callback);
        },

        set: function (/*Object*/ newValue) {
            var oldValue = this.variableKeeper;
            this.variableKeeper = newValue;
            this.eventHolder.call("set", [newValue, oldValue]);
            return this;
        },
        get: function () {
            return variableKeeper;
        },
        toString: function () {
            return this.variableKeeper.toString();
        },
        restore: function (selector, callback) {
            var that = this.variableKeeper;
            $(selector).livequery(function () {
                callback(this, that);
            });
        }
    });

    return Constr;

}());
