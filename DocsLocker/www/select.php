<?php

include 'database.php';

$search_for = "%" . $_GET["search_for"] . "%";

$data = [];

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_password);

    $stmt = $db->prepare("select * from files where (name like ? or hash like ?) and id > ? order by id asc");
	$stmt->execute([$search_for, $search_for, $_GET["from_id"]]);
	
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
	{
		$data[] = $row;
	}

	if ($_GET["search_for"]) {
		file_put_contents("events.log", "\e[92m[DocsLocker]\e[0m Searching in database for: \e[93m" . $_GET["search_for"] . "\e[0m...\n", FILE_APPEND);
	}
    

} catch (\PDOException $e) {
    echo $e->getMessage();
}

echo json_encode($data);