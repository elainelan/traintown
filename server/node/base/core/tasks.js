"use strict";

const ObjectPool = require('./core/objectpool.js');

// ----------------------------------------------------------------------------
var OnceTask = function(exec, info) {
    this.execFunc = exec;
    this.info = info;
}
OnceTask.pool = new ObjectPool("onceTaskPool", 256, function(){
    return new OnceTask(arguments);
});

OnceTask.prototype.exec = function(onFin) {
    this.execFunc(); // exec
    onFin(); // fin
}
OnceTask.prototype.getName = function() {
    return "once";
}
OnceTask.prototype.free = function() {
    OnceTask.pool.free(this);
}

// ----------------------------------------------------------------------------
var TaskQueue = function() {
    this.taskArray = new Array();
    this.curTask = null;
}

TaskQueue.prototype.addTask = function(task){
    this.taskArray.push(task);
}

TaskQueue.prototype.run = function(tick){

    if(this.curTask){
        // current task is running, wait
        let execTime = process.uptime() - this.curTask.startTime
        if(execTime > this.curTask.timeOut)
        {
            // time out
            console.error(`task ${this.curTask.getName()} ${this.curTask.info} exec time out, move to next task!`);
            this.curTask.free();
            this.curTask = null; // ignore
        }
        else {
            // task executing, wait
        }
    }
    else if(this.taskArray.length > 0){
        let self = this;
        this.curTask = this.taskArray.shift(); // pop task
        this.curTask.exec(()=>{
            self.curTask.free();
            self.curTask = null;
        });
    }
    else{
        // no tasks, wait
    }

    process.nextTick(()=>{
        this.run(tick);
    });
}

module.exports = {
    OnceTask : OnceTask,
    TaskQueue : TaskQueue,
};