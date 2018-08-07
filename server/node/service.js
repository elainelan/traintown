
//-----------------------------------------------------------------------------
// master service
var masterservice = {};

masterservice.init = function (){
    console.log("master service init");
}

masterservice.fin = function (code){
    console.log(`master service fin code${code}`);
}

masterservice.onTick = function (){
    console.log("master service onTick");
}

masterservice.onWorkerMsg = function (msg){
    console.log(`master service onWorkerMsg ${msg}`);
    
}

masterservice.onMsg = function (msg){
    console.log(`master service onMsg ${msg}`);
    
}

masterservice.onErr = function (err){
    console.error(`master service onErr ${err}`);
    
}

masterservice.finalizing = false;


// ----------------------------------------------------------------------------
var MsgTask = function(msg) {
    let msgClsAry = null;
    if(MsgTask.classCache[msg.cls]){
        msgClsAry = MsgTask.classCache[msg.cls];
    }
    else{
        msgClsAry = require("./game/msgs/"+msg.cls);
        MsgTask.classCache[msg.cls] = msgClsAry;
    }

    this.msg = msg;
    this.msgCls = msgClsAry[msg.cmd];
}
MsgTask.pool = new ObjectPool("msgTaskPool", 1024, function(){
    return new MsgTask(arguments);
});
MsgTask.classCache = {};

MsgTask.prototype.exec = function(onFin) {
    this.msgCls.exec(this.msg, onFin);
}
MsgTask.prototype.getName = function() {
    return "msg";
}
MsgTask.prototype.free = function() {
    this.msg = null;
    this.msgCls = null;
    MsgTask.pool.free(this);
}

//-----------------------------------------------------------------------------
// worker service
var workerservice = {};

workerservice.init = function (){
    console.log("worker service init");

}

workerservice.fin = function (code){
    console.log(`worker service fin code${code}`);
    
}

workerservice.onTick = function (){
    console.log("worker service onTick");
    
}

workerservice.onMasterMsg = function (msg){
    console.log(`worker service onMasterMsg msg${msg}`);
    
}

workerservice.onSessionOpen = function (skey){
    console.log(`worker service onSessionOpen skey${skey}`);

}

workerservice.onSessionClose = function (skey){
    console.log(`worker service onSessionClose skey${skey}`);
    
}

workerservice.createMsgTask = function (msg){
    console.log(`worker service createMsgTask msg${msg}`);
    return MsgTask.pool.alloc(msg);
}

workerservice.onErr = function (err){
    console.error(`worker service onErr err${err}`);
    
}
