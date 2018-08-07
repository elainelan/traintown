"use strict";

const crypto = require('crypto');

const ObjectPool = require('../core/objectpool.js');
const Session = require('./sessions.js');

var ListenerType = {
    HTTP  : 1,
    TCP : 2,
    UDP : 3,
    IPC : 4,
    WEBSOCK : 5,

    WORKER : 99, // only in worker
}

// ----------------------------------------------------------------------------
var Msg = function(){
}
Msg.pool = new ObjectPool("HttpMessagePool", 100, ()=>{
    return new Msg();
});

Msg.prototype.init = function(listener, res, cls, cmd, par, sid, addr){
    this.listener = listener;
    this.res = res;
    this.cls = cls;
    this.cmd = cmd;
    this.par = par;
    this.sid = sid;
    this.addr = addr;
}

Msg.prototype.getSessionData = function(skey){
    if(this.listener.sessions[this.sid])
    {
        return this.listener.sessions[this.sid].getData(skey);
    }

    return null;
}

Msg.prototype.setSessionData = function(skey, data){
    if(this.listener.sessions[this.sid])
    {
        this.listener.sessions[this.sid].setData(skey, data);
        return true;
    }

    return false;
}

Msg.prototype.return = function(par) {

    // send back message
    this.listener.msgReturn(this, par);

    this.listener = null;
    this.res = null;
    Msg.pool.free(this);
}

// TO DO : add tcp\udp\ipc binaray msg

// ----------------------------------------------------------------------------
var _createSession = function(addr, sessionCount){
    var tm = Date.now();
    var ptm = process.uptime();

    // calc session key
    const hash = crypto.createHash('sha256');
    hash.update(`wahaha${tm}${ptm}${sessionCount}`);
    var sid = hash.digest('hex') + '-' + addr;

    return new Session(sid, tm);
}

var _createSessionCheckTimer = function(listener){
    return setTimeout(function(){
        var tmNow = Date.now();
        var expireTime = listener.conf["sexpiretm"];

        Object.keys(listener.sessions).forEach(function(skey) {
            if(tmNow - listener.sessions[skey].tm > expireTime){

                // notify session close
                listener.evthandler.onSessionClose(skey);

                // destroy session
                delete listener.sessions[skey];
                --listener.sessionCount;
            }
        });

    }, listener.conf["schecktm"]);
}

// ----------------------------------------------------------------------------
var HttpListener = function(type) {
    this.type = type;
    this.sessions = new Object();
    this.sessionCount = 0;
    this.scheckTmOut = 0;
}

HttpListener.prototype.init = function(conf, evthandler){
    this.conf = conf;
    this.evthandler = evthandler;

    this.scheckTmOut = _createSessionCheckTimer(this);
}

HttpListener.prototype.fin = function(){

    // remove listener
    this.httpServer.close();

    let self = this;

    // clear sessions
    this.sessions.forEach((skey)=>{
        // notify session close
        self.evthandler.onSessionClose(skey);
        --self.sessionCount;
    });
    
    // destroy session
    delete this.sessions;

    clearTimeout(this.scheckTmOut);
    delete this.evthandler;
}

HttpListener.prototype.msgReturn = function(msg, par){

    var r = {
        sid:msg.sid,
        par:par, 
    };

    // TO DO : bytebuffer & encrypt & zip
    msg.res.end(JSON.stringify(r));

    // clear response listener
    //msg.res.removeAllListeners("error");
}

HttpListener.prototype.clearSession = function(sid) {
    if(this.sessions[sid]){
        delete this.sessions[sid];
        --this.sessionCount;
    }
}

HttpListener.prototype.listen = function() {
    const http = require('http');

    let self = this;

    // create http server
    this.httpServer = http.createServer(function (request, response){
        let body = [];
        let res = {};
        
        request.on('error', (err) => {
            // on request error
            console.error(`http listener request:${request.url} on error:${err}`);

            // TO DO : handle error
        
        }).on('data', (chunk) => {
            // receive data
        
            body.push(chunk);
        
        }).on('end', () => {
            // req end

            // clear request listener
            //request.removeAllListeners("error");
            //request.removeAllListeners("data");
            //request.removeAllListeners("end");
            
            // handle response error
            response.on('error', (err) => {
                // on response error
                console.error(`http listener request:${request.url} response on error:${err}`);

                // TO DO : handle error
            });
        
            // for Debug...
            //console.log("web3cmd server on request end body:'"+body+"'");
        
            //let urlInfo = url.parse(request.url, true);
            let cmd = request.url.substr(1).splite('.'); // remove '/' and splite by '.'
            if(cmd.length < 2){
                // error
                cmd = cmd.concat(["", ""]);
            }
            body = Buffer.concat(body).toString();
        
            // TO DO : bytebuffer & decrypt & unzip
            let par = JSON.parse(body);
            par.par = par.par || {};
            par.sid = par.sid || "";
            par.addr = request.socket.address().address;

            if(par.sid.length <= 0 || !self.sessions.hasOwnProperty(par.sid))
            {
                // new session
                let s = _createSession(par.addr, self.sessionCount);
                self.sessions[s.sid] = s;
                ++self.sessionCount;
                par.sid = s.sid;
                
                self.evthandler.onSessionOpen(s.sid);
            }
            else if(cmd[0] == "__sclose"){
                // close session
    
                // notify session close
                self.evthandler.onSessionClose(par.sid);
    
                // destroy session
                delete self.sessions[par.sid];
                --self.sessionCount;
    
                return;
            }
            else {
                // update time
                self.sessions[par.sid].tm = Date.now();
            }

            // handle msg
            var msg = Msg.pool.alloc();
            Msg.init(self, response, cmd[0], cmd[1], par.par, par.sid, par.addr);

            self.evthandler.onMsg(Msg);
        });
    });
    
    this.httpServer.listen(conf["port"], conf["host"]);
}

// ----------------------------------------------------------------------------
var WorkerListener = function(type) {
    this.sessions = new Object();
    this.sessionCount = 0;
    this.scheckTmOut = 0;
    this.conf = {};
}

WorkerListener.prototype.init = function(conf, evthandler){
    this.conf = conf;
    this.evthandler = evthandler;

    this.scheckTmOut = _createSessionCheckTimer(this);
}

WorkerListener.prototype.fin = function(){

    // remove listener
    process.removeAllListeners("message");

    let self = this;

    // clear sessions
    this.sessions.forEach((skey)=>{
        // notify session close
        self.evthandler.onSessionClose(skey);
        --self.sessionCount;
    });
    
    // destroy session
    delete this.sessions;

    clearTimeout(this.scheckTmOut);
    delete this.evthandler;
}

WorkerListener.prototype.msgReturn = function(msg, par){

    var r = {
        __retmsgid:msg.res,
        sid:msg.sid,
        par:par, 
    };

    process.send(r);
}

WorkerListener.prototype.clearSession = function(sid) {
    if(this.sessions[sid]){
        delete this.sessions[sid];
        --this.sessionCount;
    }
}

WorkerListener.prototype.listen = function() {
    
    let self = this;

    process.on('message', (par)=>{
        par.cls = par.cls || "";
        par.cmd = par.cmd || "";
        par.addr = par.addr || "";
        par.par = par.par || {};
        par.sid = par.sid || ""; // sid allocated by master
        par.__retmsgid = par.__retmsgid || 0;

        if(par.sid.length <= 0){
            // error
            console.error(`worker on msg with wrong sid, par:${par}`);

            // TO DO : handle error
            return;
        }
        else if(par.cls == "__sclose"){
            // close session

            // notify session close
            self.evthandler.onSessionClose(par.sid);

            // destroy session
            delete self.sessions[par.sid];
            --self.sessionCount;

            return;
        }

        if(!self.sessions.hasOwnProperty(par.sid))
        {
            // new session
            self.sessions[sid] = new Session(sid, Date.now());
            ++self.sessionCount;
            
            self.evthandler.onSessionOpen(skey);
        }
        else {
            // update time
            self.sessions[sid].tm = Date.now();
        }

        // handle msg
        var msg = Msg.pool.alloc();
        Msg.init(self, par.__retmsgid, par.cls, par.cmd, par.par, par.sid);

        self.evthandler.onMsg(Msg);
    });
}

// ----------------------------------------------------------------------------
var Listener = function(type) {
    switch(type){
        case ListenerType.HTTP:{
            this.listener = new HttpListener(type);
        }
        break;
        case ListenerType.TCP:{
                // TO DO : implement tcp listener
            }
            break;
        case ListenerType.UDP:{
                // TO DO : implement udp listener
            }
            break;
        case ListenerType.IPC:{
                // TO DO : implement ipc listener
            }
            break;
        case ListenerType.WEBSOCK:{

            }
            break;
        case ListenerType.WORKER:{
            this.listener = new WorkerListener(type);
            break;
        }

    }
}

Listener.prototype.init = function(conf, evthandler){
    this.listener.init(conf, evthandler);
}

Listener.prototype.fin = function(){
    this.listener.fin();
}

Listener.prototype.clearSession = function(sid) {
    this.listener.clearSession(sid)
}

Listener.prototype.getSessionData = function (skey, key) {
    if(this.listener.sessions[skey]){
        return this.listener.sessions[skey].getData(key);
    }
    return null;
}

Listener.prototype.setSessionData = function (skey, key, data) {
    if(this.listener.sessions[skey]){
        this.listener.sessions[skey].setData(key, data);
    }
}

Listener.prototype.listen = function() {
    this.listener.listen()
}

module.exports = {
    Listener:Listener,
    ListenerType:ListenerType
};