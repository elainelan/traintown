"use strict";

/**
 * Session
 *
 * @method Session
 */
const Session = function (sid, tm) {
    this.sid = sid;
    this.tm = tm;
    this.data = new Object();
}

Session.prototype.setData = function (key, d) {
    data[key] = d;
}

Session.prototype.getData = function (key) {
    return data[key];
}

module.exports = Session;