"use strict";

const cluster = require('cluster');

const Listener = require('./network/listener.js');

let _Workers = function () {
    this.workers = [];
}

_Workers.prototype.addWorker = function (worker) {

    let w = {
        worker : worker,
        sessionCount : 0,
        initializing : true,
        closing : false,
        pendingMsg : new Array(), // store pending message when worker is closing
    };
    this.workers.push(w);

    return w;
}

_Workers.prototype.getWorker = function (index) {
    return this.workers[index];
}

_Workers.prototype.getWorkerCount = function () {
    return this.workers.length;
}

_Workers.prototype.findWorker = function (w) {
    return this.workers.findIndex(function(e) {
        return e.worker == w;
    });
}

_Workers.prototype.findWorkerByID = function (id) {
    return this.workers.findIndex(function(e) {
        return e.worker.id == id;
    });
}

_Workers.prototype.findLowestLoadWorker = function () {
    
    // find lowest load worker
    let minLoadWorker = 0;
    let minLoadWorkerSes = 0x7fffffff;
    let wNode = null;
    for(let i=0; i<this.workers.length;++i){
        wNode = this.workers[i];
        if(wNode.closing){
            // worker is closing, move on
            continue;
        }
        if(wNode.sessionCount < minLoadWorkerSes){
            minLoadWorkerSes = wNode.sessionCount;
            minLoadWorker = i;
        }
    }

    return minLoadWorker;
}

_Workers.prototype.removeWorker = function (index) {
    return this.workers.splice(index, 1);
}

_Workers.prototype.replaceWorker = function (index, worker) {
    let w = {
        worker : worker,
        sessionCount : 0,
        initializing : true,
        closing : false,
        pendingMsg : new Array(),
    };
    return this.workers.splice(index, 1, w);
}


/**
 * Master server run workers
 *
 * @method MasterServer
 * @param {Object} conf
 * @param {Object} services
 */
let MasterServer = function (conf) {
  this.conf = conf;
  this.workers = {};

  // initialize msg sent map
  this.wokerMsgIDSeed = 0;
  this.wokerMsgMap = {};

  // initialize session to worker map
  this.sessionWorkerMap = {};

  // initialize session pending message map, when session transfer from one worker service to another
  this.sessioinPendingMsg = {}; // key by session id
}

/**
 * Get server config
 *
 * @method getConf
 * @return {Object} returns config of this master server
 */
MasterServer.prototype.getConf = function () {
  return this.conf;
}

/**
 * Transfer session from one worker service to another
 *
 * @method transferService
 * @param {Object} skey session id
 * @param {Object} servicename service name
 * @param {Object} par par
 */
MasterServer.prototype.transferService = function (skey, servicename, par) {
    let s2wNode = self.sessionWorkerMap[skey];
    let wNode = self.workers[s2wNode.name][s2wNode.index]; 

    if(wNode.closing){
        // old worker is closing, move to new worker directly
        s2wNode.name = servicename;
        s2wNode.index = self.workers[s2wNode.name].findLowestLoadWorker();
    }
    else{
        wNode.worker.send({__mcmd:"__ack_transserv", __skey:skey, __sname:servicename, __par:par});
        this.sessioinPendingMsg[skey] = new Array(); // new pending message array
    }
}

/**
 * Get session data store in session
 *
 * @method getSessionData
 * @param {Object} skey session id
 * @param {Object} key data key
 * @return {Object} returns session data index by key
 */
MasterServer.prototype.getSessionData = function (skey, key) {
    // serviceListener is a proxy to worker, no session data
    return this.masterListener.getSessionData(skey, key);
}
  
  
/**
 * Store data in session
 *
 * @method setSessionData
 * @param {Object} skey session id
 * @param {Object} key data key
 * @param {Object} data data
 */
MasterServer.prototype.setSessionData = function (skey, key, data) {
    // serviceListener is a proxy to worker, no session data
    this.masterListener.setSessionData(skey, key, data);
}

/**
 * Go to work
 *
 * @method startLoooop
 */
MasterServer.prototype.startLoooop = function () {
    // is master

    let self = this;

    this.masterService = require('./service/'+conf['filename']);

    this.masterService.server = this;

    let workerConfs = this.conf["worker"];

    // intialize workers
    for(let widx=0;widx<workerConfs.length; ++widx){
        
        let wary = new _Workers();

        let wConf = workerConfs[widx];
        let wCount = wConf['count'];
        let wName = wConf['name'];

        console.log(`master server initialize worker name:${wName}`);

        // create worker
        for(const i=0;i<wCount;i++){
            console.log(`master server fork:${i}/${wCount}`);

            // create worker & send new service command
            let worker = cluster.fork();
            worker.send({__mcmd:"__new_service", __name:wName});

            wary.addWorker(worker);
        }

        self.workers[wName] = wary;
    }

    // handle worker event
    cluster.on('exit',(worker, code, signal)=>{
        console.log('worker id:%d pid:%d exit with:(%s)', worker.id, worker.process.pid, signal || code);

        // find worker & remove
        let found = false;
        for(const wName in self.workers){
            let wary = self.workers[wName];
            let idx = wary.findWorkerByID(worker.id);
            if(idx >= 0){
                if(self.masterService.finalizing){
                    // is finalizing, just remove
                    wary.removeWorker(idx);
                }
                else{
                    let pendingMsg = wary.getWorker(idx).pendingMsg;

                    // respawn a new worker
                    let newWorker = cluster.fork();
                    newWorker.send({__mcmd:"__new_service", __name:wName});
                    wary.replaceWorker(idx, newWorker);

                    console.log(`master server spawn worker:${newWorker.id}`);

                    if(pendingMsg){

                        console.log(`master server flush pending message length:${pendingMsg.length}`);

                        // flush pending messages
                        for(let msgidx=0; msgidx<pendingMsg.length ; ++msgidx){
                          // trans msg
                          newWorker.send(pendingMsg[msgidx]);
                        }
                    }
                }
                found = true;
                break;
            }
        }

        if(!found){
            // error, worker who exit not found
            console.error(`master server worker:${worker.id} not found in workers`);
        }
    });
    cluster.on('fork',(worker)=>{
        console.log(`fork：worker${worker.id}`)
    });
    cluster.on('disconnect',(worker)=>{
        console.log(`worker${worker.id} is disconnected.`)
    });
    cluster.on('listening',(worker,addr)=>{
        console.log(`worker${worker.id} listening on ${addr.address}:${addr.port}`)
    });
    cluster.on('online',(worker)=>{
        console.log(`worker${worker.id} is online now`)
    });
    cluster.on('message',(worker,par)=>{
        //console.log(`got the worker${worker.id}'s msg：${msg}`);
      if(par.__retmsgid){
        //fetch msg
        let msg = self.wokerMsgMap[par.__retmsgid];
        delete self.wokerMsgMap[par.__retmsgid];

        // return msg
        msg.return(par.par);
      }
      else if(par.__wcmd){
        // worker control command
        if(par.__wcmd=="__transserv"){
            // trans service
            self.transferService(par.__skey, par.__sname, par.__par);
        }
        else if(par.__wcmd=="__transserv_fin"){
            // trans service finish

            // move node to new worker
            let s2wNode = self.sessionWorkerMap[par.__skey];
            s2wNode.name = par.__sname;
            s2wNode.index = self.workers[s2wNode.name].findLowestLoadWorker();
            let wNode = self.workers[s2wNode.name][s2wNode.index]; 
            
            // clear session pending msg
            let pendingMsg = self.sessioinPendingMsg[par.__skey];
            delete self.sessioinPendingMsg[par.__skey];

            // flush pending messages
            for(const i = 0; i< pendingMsg.length; ++i){
                // trans msg
                wNode.worker.send(pendingMsg[i]);
            }
        }
        else if(par.__wcmd == "__wclosing"){

            // find worker & closing
            let found = false;
            for(const wName in self.workers){
                let wary = self.workers[wName];
                let idx = wary.findWorkerByID(worker.id);

                if(idx >= 0){

                    let wNode = wary.getWorker(idx);
                    
                    // mark closing
                    wNode.closing = true;
                    wNode.worker.send({__mcmd:"__ack_wclosing"});

                    found = true;
                    break;
                }
            }

            if(!found){
                // error, worker who exit not found
                console.error(`onmessage master server worker:${worker.id} not found in workers`);
            }
        }
        else if(par.__wcmd=="__ack_new_service"){
            // worker service initialize finish

            // find worker & mark start
            let found = false;
            for(const wName in self.workers){

                let wary = self.workers[wName];
                let idx = wary.findWorkerByID(worker.id);

                if(idx >= 0){
                    console.error(`onmessage master server worker:${worker.id} initialize finish`);

                    let wNode = wary.getWorker(idx);
                    wNode.initializing = false; // initialize finish

                    if(wNode.pendingMsg){
                        console.log(`master server flush pending message length:${wNode.pendingMsg.length}`);

                        // flush pending messages
                        for(let msgidx=0; msgidx<wNode.pendingMsg.length ; ++msgidx){
                            // trans msg
                            wNode.worker.send(wNode.pendingMsg[msgidx]);
                        }
                    }
                    found = true;
                    break;
                }
            }

            if(!found){
                // error, worker who exit not found
                console.error(`onmessage master server worker:${worker.id} not found in workers`);
            }
        }
      }
      else{
        // worker message
        self.masterService.onWorkerMsg(worker, msg);
      }
    });

    // create master server Ticker
    let masterTimeout = setInterval(()=>{
      self.masterService.onTick();
    }, this.conf["tick"]);

    // create service API listener
    this.serviceListener = new Listener.Listener(conf["serviceListener"]["type"]);
    let serviceEvtHandler = {
      onSessionOpen : (skey)=>{
        // new session

        // get login service
        lwary = self.workers['login']; // always name 'login'

        // find lowest load worker
        let wNodeIndex = lwary.findLowestLoadWorker()
        let wNode = lwary.getWorker(wNodeIndex);

        self.sessionWorkerMap[skey] = {name:'login', index:wNodeIndex};
        ++wNode.sessionCount;
      },
      onSessionClose : (skey)=>{
        let par = {
          sid : skey,
          cls : "__sclose",
        }

        // find worker index clear session worker map
        let s2wNode = self.sessionWorkerMap[skey];
        delete self.sessionWorkerMap[skey];

        // notify session close
        let wNode = self.workers[s2wNode.name][s2wNode.index]; 
        --wNode.sessionCount;
        wNode.worker.send(par);
      },
      onMsg : (msg)=>{
        // dispatch message to worker

        // make msg id & map id to msg
        let msgid = ++ self.wokerMsgIDSeed;
        self.wokerMsgMap[msgid] = msg;

        let par ={
          cls : msg.cls,
          cmd : msg.cmd,
          addr : msg.addr,
          par : msg.par,
          sid : msg.sid,
          __retmsgid : msgid
        }

        let s2wNode = self.sessionWorkerMap[skey];
        if(!s2wNode){
            console.error(`unknown session with skey:${skey} can't found in sessionWorkerMap`);
            return;
        }

        let wNode = self.workers[s2wNode.name][s2wNode.index]; 
        if(!wNode){
            console.error(`session with skey:${skey} in worker${s2wNode.name} index[${s2wNode.index}] can't found in workers`);
            return;
        }

        if(wNode.closing || wNode.initializing){
            // worker initializing or closing, store pending msg
            wNode.pendingMsg.push(par);
        }
        else if(self.sessioinPendingMsg[msg.sid]){
            // session in transfer, store pending msg
            self.sessioinPendingMsg[msg.sid].push(par);
        }
        else{
            // trans msg
            wNode.worker.send(par);
        }
      }
    }

    this.serviceListener.init(conf["serviceListener"], serviceEvtHandler);
    this.serviceListener.listen();

    // create master listener, allways http
    this.masterListener = new Listener.Listener(Listener.ListenerType.HTTP);
    let masterEvtHandler = {
      onSessionOpen : (skey)=>{

      },
      onSessionClose : (skey)=>{

      },
      onMsg : (msg)=>{
        self.masterService.onMsg(msg);
      }
    }

    this.masterListener.init(conf["masterListener"], masterEvtHandler);
    this.masterListener.listen();
    
    // intialize master service
    this.masterService.init();

    // handle process event
    process.on('exit', (code) => {
      console.log('master server exit with:'+code);

      // TO DO : clear tasks & save data
      
      self.masterService.fin();
      self.serviceListener.fin();
      self.masterListener.fin();

      self.masterService.finalizing = true;

      // kill all worker
      for(const wName in self.workers){
        let wary = self.workers[wName];
        for(const i =0; i< wary.getWorkerCount(); ++i){
            let wNode = wary.getWorker(i);
            
            wNode.closing = true;
            wNode.worker.send({__mcmd:"__ack_wclosing"});
        }
      }

      clearInterval(masterTimeout);
    });
}

module.exports = MasterServer;