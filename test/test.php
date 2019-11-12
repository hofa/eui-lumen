<?php
$register_address = '127.0.0.1:1238';
$connectTimeout = 10;
$client = stream_socket_client('tcp://' . $register_address, $errno, $errmsg, $connectTimeout);
print_r($client);