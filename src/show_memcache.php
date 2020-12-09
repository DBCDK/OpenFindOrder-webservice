<?php

//@ $remote = gethostbyaddr($_SERVER["REMOTE_ADDR"]);
//$homie = (strpos($remote, ".dbc.dk") + 7 == strlen($remote));
//if ($homie)
  //$homie = (gethostbyname($remote) == $_SERVER["REMOTE_ADDR"]); // paranoia tjek
//
//if (!$homie) { header("HTTP/1.0 404 Not Found"); die(); }

$host = $_GET['host'] ?: 'localhost';
$port = $_GET['port'] ?: '11211';
if (class_exists('Memcache')) {
  $memcache_obj = new Memcache();
  $memcache_obj->addServer($host, $port);
  $cache_stat = ($memcache_obj->getStats());
} elseif (class_exists('Memcached')) {
  $memcache_obj = new Memcached();
  $memcache_obj->addServer($host, $port);
  $cache_stat = ($memcache_obj->getStats()[$host . ':' . $port]);
} else {
  die('Neither Memcache or Memcached is installed');
}

cache_info($cache_stat);

/********************************************************************/

function style() {
return '
  <style>
    table {background-color: #EEEEEE}
    td {padding: 2px 10px 2px 10px}
    tr:nth-child(odd) {background: #FFF}
  </style>';
}
function mega($num) {
  return round((real) $num/(1024*1024), 4);
}
function tr_me($txt, $val) {
  printf('<tr><td>%s</td><td>%s</td></tr>', $txt, $val);
}
function cache_info($status){
  $percCacheHit = sprintf(' (%s%%)', round(((real) $status['get_hits'] / (real) $status['cmd_get'] * 100), 2));
  //$percCacheHit = ' (' . round(((real) $status['get_hits'] / (real) $status['cmd_get'] * 100), 2) . '%)';
  $percCacheMiss = sprintf(' (%s%%)', round(((real) $status['get_misses'] / (real) $status['cmd_get'] * 100), 2));

  printf('<html><head>%s</head><body><table>', style());
  tr_me('Memcache Server version: ', $status ['version']);
  tr_me('Process id of this server process ', $status ['pid']);
  tr_me('Number of seconds this server has been running ', $status['uptime']);
  tr_me('Accumulated user time for this process ', round($status['rusage_user'], 4) . ' seconds');
  tr_me('Accumulated system time for this process ', round($status['rusage_system'], 4).' seconds');
  tr_me('Total number of items stored by this server ever since it started ', $status['total_items']);
  tr_me('Number of open connections ', $status['curr_connections']);
  tr_me('Total number of connections opened since the server started running ', $status['total_connections']);
  tr_me('Number of connection structures allocated by the server ', $status['connection_structures']);
  tr_me('Cumulative number of retrieval requests ', $status['cmd_get']);
  tr_me(' Cumulative number of storage requests ', $status['cmd_set']);
  tr_me('Number of keys that have been requested and found present ', $status['get_hits'] . $percCacheHit);
  tr_me('Number of items that have been requested and not found ', $status['get_misses'] . $percCacheMiss);
  tr_me('Total number of bytes read by this server from network ', mega($status['bytes_read']) . ' Mega Bytes');
  tr_me('Total number of bytes sent by this server to network ', mega($status['bytes_written']) . ' Mega Bytes');
  tr_me('Number of bytes this server is allowed to use for storage.', mega($status['limit_maxbytes']) . ' Mega Bytes');
  tr_me('Number of bytes currently in storage.',mega($status['bytes']) . ' Mega Bytes');
  tr_me('Number of valid items removed from cache to free memory for new items.', $status['evictions']);

 printf('</table></body></html>');
}


