const ClusterServer = require("./base/server/clusterserver.js");

// TO DO : load server config

let clserver = new ClusterServer(conf);
clserver.startLoooop();