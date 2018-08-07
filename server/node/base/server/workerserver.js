"use strict";

const cluster = require('cluster');

const Listener = require('./network/listener.js');
const Task = require('./core/tasks.js');

/**
 * Worker server manager by master
 *
 * @method WorkerServer
 * @param {Object} conf
 * @param {Object} service
 */
let WorkerServer = function (conf) {
  this.conf = conf;
}

/**
 * Get server config
 *
 * @method getConf
 * @return {Object} returns config of this master server
 */
WorkerServer.prototype.getConf = function () {
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
WorkerServer.prototype.transferService = function (skey, servicename, par) {
    // notify master server to start transfer service
    process.send({__wcmd:"__transserv", __skey:skey, __sname:servicename, __par:par});
}

/**
 * Get session data store in session
 *
 * @method getSessionData
 * @param {Object} skey session id
 * @param {Object} key data key
 * @return {Object} returns session data index by key
 */
WorkerServer.prototype.getSessionData = function (skey, key) {
  return this.listener.getSessionData(skey, key);
}


/**
* Store data in session
*
* @method setSessionData
* @param {Object} skey session id
* @param {Object} key data key
* @param {Object} data data
*/
WorkerServer.prototype.setSessionData = function (skey, key, data) {
  this.listener.setSessionData(skey, key, data);
}

/**
 * Go to work
 *
 * @method startLoooop
 */
WorkerServer.prototype.startLoooop = function () {
    // is worker

    console.log("worker:"+cluster.worker.id+" started");

    let self = this;

    // create task queue
    this.taskQueue = new Task.TaskQueue();

    // create worker listener, allways ListenerType.WORKER
    this.listener = new Listener.Listener(Listener.ListenerType.WORKER);
    let workerEvtHandler = {
      onSessionOpen : (skey)=>{
        // add task
        self.taskQueue.push(OnceTask.pool.alloc(()=>{
          self.workerService.onSessionOpen(skey);
        }));
      },
      onSessionClose : (skey)=>{
        // add task
        self.taskQueue.push(OnceTask.pool.alloc(()=>{
          self.workerService.onSessionClose(skey);
        }));
      },
      onMsg : (msg)=>{
        if(par.__retmsgid){
          let msgTask = self.workerService.createMsgTask(msg);
          self.taskQueue.push(msgTask);
        }
        else if(par.__mcmd){
            if(par.__mcmd == "__ack_transserv"){
                // master already block pending msg on session

                // add transfer task to nail of task queue, and wait all session msg been excuted
                self.taskQueue.push(OnceTask.pool.alloc(()=>{

                    // remove session
                    self.workerService.onSessionRemove(par.__skey);

                    self.listener.clearSession(sid);

                    // all session task cleared, transfer session to new worker
                    process.send({__wcmd:"__transserv_fin", __skey:par.__skey, __sname:par.__sname, __par:par.__par});
                }));
            }
            else if(par.__mcmd == "__ack_wclosing"){
                // ack closing
                console.log(`worker id:${cluster.worker.id} receive ack closing msg, schedule shutdown`);

                // service fin
                self.listener.fin();
                self.workerService.fin();
                
                // add closing task
                self.taskQueue.push(OnceTask.pool.alloc(()=>{
                    // max process message reached, exit
                    process.exit();
                }));
            }
            else if(par.__mcmd == "__new_service"){
                // create service
                
                conf = conf[par.__name]; // get worker conf by name
                self.workerService = require('./service/'+conf['filename']);
                
                // intialize worker service
                self.workerService.init();

                if(!self.workerService){
                    console.log(`worker id:${cluster.worker.id} serivce initialize with name${par.__name} file${conf['filename']} failed`);
                }
                else{
                    console.log(`worker id:${cluster.worker.id} serivce initialize with name${par.__name} file${conf['filename']} success`);
                    self.workerService.server = this;
                }

                // notify master initialize finish
                process.send({__wcmd:"__ack_new_service"});
            }
        }
        else{
          self.workerService.onMasterMsg(msg);
        }
      }
    }

    let maxWorkerProcMsg = this.conf["maxWorkerProcMsg"] || 0;
    if(maxWorkerProcMsg > 0){
      // need respawn worker after maxWorkerProcMsg messages been processed
      let msgProced = 0;
      workerEvtHandler.onMsg = (msg)=>{
        if(par.__retmsgid){

          // TO DO : add service message task
          let msgTask = self.workerService.createMsgTask(msg);
          self.taskQueue.push(msgTask);

          // count msg processed
          ++msgProced;
          if(msgProced >= maxWorkerProcMsg){
            // notify master closing
            process.send({__wcmd:"__wclosing"});
            
            console.log(`worker id:${cluster.worker.id} max message proc reached (${msgProced}), send closing msg`);
          }
        }
        else if(par.__mcmd){
          if(par.__mcmd == "__ack_wclosing"){
            // ack closing
            console.log(`worker id:${cluster.worker.id} receive closing ack msg, schedule shutdown`);

            // service fin
            self.listener.fin();
            self.workerService.fin();

            // add closing task
            self.taskQueue.push(OnceTask.pool.alloc(()=>{
              // max process message reached, exit
              process.exit();
            }));
          }
        }
        else{
          self.workerService.onMasterMsg(msg);
        }
      }
    }

    this.listener.init(conf["workerListener"], workerEvtHandler);
    this.listener.listen();
    
    // create worker server Ticker
    let workerTimeout = setInterval(()=>{
      // add task
      self.taskQueue.push(OnceTask.pool.alloc(()=>{
        self.workerService.onTick();
      }));
    }, this.conf["tick"]);
    
    // handle process event
    process.on('exit', (code) => {
      console.log(`worker id:${cluster.worker.id} exit with:`+code);

      // TO DO : clear tasks & save data
      clearInterval(workerTimeout);
    });

    // run task queue
    const taskTick = this.conf["taskTick"] || 1;
    this.taskQueue.run(taskTick);
}

module.exports = WorkerServer;