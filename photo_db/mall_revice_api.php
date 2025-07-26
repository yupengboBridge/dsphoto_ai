<?php
date_default_timezone_set('Asia/Tokyo');

require_once('./config.php');
require_once('./lib.php');

function validateToken($timestamp, $token, $secretKey) {
    $timeWindow = 300;
    $currentTimestamp = time();

    if (abs($currentTimestamp - $timestamp) > $timeWindow) {
        return false;
    }

    $expectedToken = hash_hmac('sha256', $timestamp, $secretKey);

    return hash_equals($expectedToken, $token);
}

$secretKey = '1eoAeF7dGxxBvZZMX3qezmV9UULNcx';
$receivedTimestamp = $_GET['timestamp'];
$receivedToken = $_GET['token'];

if (validateToken($receivedTimestamp, $receivedToken, $secretKey)) {
    $db_link = db_connect();

    $ret_data = array(
        "status" => "200",
        "message" => "MALLと連携できました。"
    );

    $sql = "INSERT INTO mall_task (task_recive_datetime) VALUES (?)";
    $stmt = $db_link->prepare($sql);

    $timestamp = time();
    $currentDateTime = date("Y-m-d H:i:s", $timestamp);

    $stmt->bindParam(1,$currentDateTime);
    $result = $stmt->execute();
    if ($result == true){
        exec("nohup /usr/bin/php /home/xhankyu/public_html/photo_db/malltools/mall_task_start.php > /dev/null 2>&1 &");
        header('Content-Type: application/json');
        echo json_encode($ret_data);
    }else{
        $ret_data["status"] = "500";
        $ret_data["message"] = "MALLと連携失敗できました。後ほど試してみてください。";

        header('Content-Type: application/json');
        echo json_encode($ret_data);
    }
} else {
    $ret_data["status"] = "502";
    $ret_data["message"] = "有効なTokenではありません。";

    header('Content-Type: application/json');
    echo json_encode($ret_data);
}

?>