<?php
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");

    include_once '../config/connection.php';
    include_once '../class/nodemcu_log.php';

    $database = new Database();
    $db = $database->getConnection();

    $item = new id21764929_bmical($db);
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		// The request is using the POST method
		$data = json_decode(file_get_contents("php://input"));
		$item->tinggi = $data->tinggi;
		$item->berat = $data->berat;
        $item->hasil = $data->hasil;
        $item->hasil_char = $data->hasil_char;
	} 
    elseif ($_SERVER['REQUEST_METHOD'] === 'GET'){
		// The request is using the GET method
		$item->tinggi = isset($_GET['tinggi']) ? $_GET['tinggi'] : die('wrong structure!');
		$item->berat = isset($_GET['berat']) ? $_GET['berat'] : die('wrong structure!');
        $item->hasil = isset($_GET['hasil']) ? $_GET['hasil'] : die('wrong structure!');
        $item->hasil_char = isset($_GET['hasil_char']) ? $_GET['hasil_char'] : die('wrong structure!');
	}else {
		die('wrong request method');
	}
	
    if($item->createLogData()){
        echo 'Data created successfully.';
    } else{
        echo 'Data could not be created.';
    }
?>