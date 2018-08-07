

// ----------------------------------------------------------------------------
var ObjectPool = function(name, count, allocFunc) {
    this.freeList = new FreeList(name, count, allocFunc);
}

ObjectPool.prototype.alloc = function() {
    return this.freeList.alloc(arguments);
}

ObjectPool.prototype.free = function(task) {
    return this.freeList.free(task);
}

module.exports = ObjectPool;