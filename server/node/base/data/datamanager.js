"use strict";

let _redis   = require('redis');
let _redisClient  = null;

let _redisErrHandler = function(error) {
    // TO DO : redis error handler
    console.error(`redis on error:${error}`);
}

/**
 * DataManager
 *
 * @method DataManager
 */
const DataManager = {

};

/**
 * Initialize Data Manager
 *
 * @method init
 * @return {Boolean}
 */
DataManager.init = function (conf) {

    // connect to redis
    _redisClient = _redis.createClient(conf.redisPort, conf.redisHost);
    _redisClient.on("error", _redisErrHandler);

    return true;
}

module.exports = DataManager;