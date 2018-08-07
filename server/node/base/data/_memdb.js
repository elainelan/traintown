"use strict";

let redis   = require('redis');

/**
 * RedisDB
 *
 * @method RedisDB
 */
const RedisDB = function (conf, db) {
    this.conf = conf;
    this.ready = false;

    let self = this;

    // connect to redis
    this.client = _redis.createClient(conf.redisPort, conf.redisHost);
    client.on("error", function(error) {
        // TO DO : redis error handler
        console.error(`redis on error:${error}`);
    });
    client.select(db, function(error){
        if(error) {
            console.error(`redis select db error:${error}`);
        } else {
            self.ready = true;
        }
    });
}

/**
 * Get cluster server config
 *
 * @method getConf
 * @return {Object} returns data by key
 */
RedisDB.prototype.getData = function (key) {
    return null;
}

module.exports = RedisDB;