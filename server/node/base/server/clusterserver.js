"use strict";

const cluster = require('cluster');

const MasterServer = require('./masterserver.js');
const WorkerServer = require('./workerserver.js');

/**
 * Cluster server runs workers manager by master
 *
 * @method ClusterServer
 * @param {Object} conf
 * @param {Object} services
 */
let ClusterServer = function (conf) {
  this.conf = conf;
  this.server = null;
}

/**
 * Send message from woker to master server
 *
 * @method sendToMaster
 * @param {Object} msg message send to master server
 * @return {Boolean}
 */
ClusterServer.sendToMaster = function (msg){
  msg.msgType = protocols.MessageType.w2mMsg;
  return process.send(msg);
}

/**
 * Send message from master to worker
 *
 * @method sendToWorker
 * @param {Object} workerid worker id
 * @param {Object} msg message send to worker
 * @return {Boolean}
 */
ClusterServer.sendToWorker = function (workerid, msg){
  msg.msgType = protocols.MessageType.m2wMsg;
  return cluster.workers[workerid].send(msg);
}

/**
 * Broadcast message from master to woker
 *
 * @method sendToWorker
 * @param {Object} msg message send to worker
 * @return {int} success count
 */
ClusterServer.broadcastToWorker = function (msg){
  msg.msgType = protocols.MessageType.m2wMsg;
  let sent = 0;
  for (const id in cluster.workers) {
    if(cluster.workers[id].send(msg)){
      ++sent;
    }
  }
  return sent;
}

/**
 * Get cluster server config
 *
 * @method getConf
 * @return {Object} returns config of this master server
 */
ClusterServer.prototype.getConf = function () {
  return this.conf;
}

// /**
//  * Transfer session from one worker service to another
//  *
//  * @method transferService
//  * @param {Object} skey session id
//  * @param {Object} servicename service name
//  * @param {Object} par par
//  */
// ClusterServer.prototype.transferService = function (skey, servicename, par) {
//   this.server.transferService(skey, servicename, par);
// }

// /**
//  * Get session data store in session
//  *
//  * @method getSessionData
//  * @param {Object} skey session id
//  * @param {Object} key data key
//  * @return {Object} returns session data index by key
//  */
// ClusterServer.prototype.getSessionData = function (skey, key) {
//   return this.server.getSessionData(skey, key);
// }


// /**
//  * Store data in session
//  *
//  * @method setSessionData
//  * @param {Object} skey session id
//  * @param {Object} key data key
//  * @param {Object} data data
//  */
// ClusterServer.prototype.setSessionData = function (skey, key, data) {
//   this.server.setSessionData(skey, key, data);
// }

/**
 * Go to work
 *
 * @method startLoooop
 */
ClusterServer.prototype.startLoooop = function () {
  /*process.env.NODE_DEBUG='net';*/
  if(cluster.isMaster){
    // is master
    this.server = new MasterServer(conf);
    this.server.startLoooop();
  }else{
    // is worker
    this.server = new WorkerServer(conf['worker']);
    this.server.startLoooop();
  }
}

module.exports = ClusterServer;