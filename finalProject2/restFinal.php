<?php
require_once("model.php");
header('Content-Type: application/json');
$pathArray = explode("/", substr(@$_SERVER['PATH_INFO'], 1));

switch ($_SERVER['REQUEST_METHOD']) {
case 'GET':
    if($pathArray[1] === "items") {
        if(count($pathArray) >=3) {
            $result = getItemsByUserConsumed($pathArray[2]);
            if(empty($result)) {
                print generateResponse(400, array("status" => "FAIL"));
            } else {
                print generateResponse(200, array("status" => "OK", "items" => $result));
            }

        } else {
            $result = getListOfItems();
            if(empty($result)) {
                print generateResponse(400, array("status" => "FAIL"));
            } else {
                print generateResponse(200, array("status" => "OK", "items" => $result));
            }
        }
    } else {
        $result = getSummaryOfItems($pathArray[2]);
        if(empty($result)) {
            print generateResponse(400, array("status" => "FAIL"));
        } else {
            print generateResponse(200, array("status" => "OK", "items" => $result));
        }
    }
    break;
case 'POST':
    $jsonData = getJsonData();
    if($pathArray[1] === "user") {
        $result = getToken($jsonData);
        if(empty($result)) {
            print generateResponse(400, array("status" => "FAIL"));
        } else {
            print generateResponse(200, array("status" => "OK", "token" => $result));
        }
    } else {
        $result = updateItemsConsumed($jsonData);
        if($result == "OK") {
            print generateResponse(200, array("status" => "OK"));
        } else if($result == "AUTH_FAIL") {
            print generateResponse(401, array("status" => "AUTH_FAIL"));
        } else {
            print generateResponse(400, array("status" => "FAIL"));
        }

    }
    break;
}

function generateResponse($code, $data) {
    $response = array();
    foreach($data as $key => $item) {
        $response[$key] = $item;
    }
    return json_encode($response);
}


function getJsonData() {
    $rawData = file_get_contents("php://input");
    $jsonBody = json_decode($rawData, true);
    return $jsonBody;
}
