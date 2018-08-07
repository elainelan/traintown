// redis 链接
var redis   = require('redis');
var client  = redis.createClient('6379', '127.0.0.1');

// redis 链接错误
client.on("error", function(error) {
    console.log(error);
});

// redis 验证 (reids.conf未开启验证，此项可不需要)
//client.auth("foobared");

var t1 = process.uptime();
var t = t1;

client.select('15', function(error){
    if(error) {
        console.log(error);
    } else {
        var t = process.uptime() - t1;
        console.log("select time:"+t);
    }
});

// set
client.set('str_key_0', '0', function(error, res) {
    if(error) {
        console.log(error);
    } else {
        console.log(res);
    }

    t = process.uptime() - t1;
    console.log("set time:"+t);
});

t1 = process.uptime();
// get
client.get('str_key_0', function(error, res){
    if(error) {
        console.log(error);
    } else {
        console.log(res);
    }

    t = process.uptime() - t1;
    console.log("get time:"+t);
});

t1 = process.uptime();
// hmset
var info = {};
info.baidu = 'www.baidu.com';
info.sina  = 'www.sina.com';
info.qq    = 'www.qq.com';
client.hmset('site', info, function(error, res){
    if(error) {
        console.log(error);
    } else {
        console.log(res);
    }
    
    t = process.uptime() - t1;
    console.log("hmset time:"+t);
});

t1 = process.uptime();
// hmget
client.hmget('site', 'baidu', function(error, res){
    if(error) {
        console.log(error);
    } else {
        console.log(res);
    }
    
    t = process.uptime() - t1;
    console.log("hmget time:"+t);
});

t1 = process.uptime();
// hgetall
client.hgetall('site', function(error, res){
    if(error) {
        console.log(error);
    } else {
        console.log(res);
    }
    
    t = process.uptime() - t1;
    console.log("hgetall time:"+t);
});

t1 = process.uptime();
// lpush
client.lpush('list', 'key_0', function(error, res){
    if(error) {
        console.log(error);
    } else {
        console.log(res);
    }
    
    t = process.uptime() - t1;
    console.log("lpush1 time:"+t);
});
t1 = process.uptime();
client.lpush('list', 'key_1', function(error, res){
    if(error) {
        console.log(error);
    } else {
        console.log(res);
    }
    
    t = process.uptime() - t1;
    console.log("lpush2 time:"+t);
});


t1 = process.uptime();
// lrange
client.lrange('list', '0', '-1', function(error, res){
    if(error) {
        console.log(error);
    } else {
        console.log(res);
    }

    t = process.uptime() - t1;
    console.log("lrange time:"+t);
});

// 都结束后，关闭连接
// 关闭链接
//client.quit();