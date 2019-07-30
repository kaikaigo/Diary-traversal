<?php

$mysqli = mysqli_connect("localhost", "cse383" , "HoABBHrBfXgVwMSz" , "cse383");

function gererateRandomString($n) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
    return $randomString;
}

function getToken($jsonData){
    global $mysqli;
    $password = $jsonData['password'];
    $uniqId = $jsonData['user'];
    $token = "";
    $password_database = "";
    if($query = $mysqli->prepare("select password from users where user = ?")) {
        $query->bind_param("s", $uniqId);
        $query->execute();
        $query->store_result();
        $query->bind_result($password_database);
        $query->fetch();
        $query->free_result();
        if(!empty($password_database)) {
            if(password_verify($password, $password_database)) {
                $token = gererateRandomString(20);
                if($query = $mysqli->prepare("insert into tokens (user, token) values (?,?)")) {
                    $query->bind_param("ss", $uniqId, $token);
                    $query->execute();
                }
            }
        }
    }
    return $token;
}

function getListOfItems() {
    $items = array();
    global $mysqli;
    $qury = mysqli_query($mysqli, "select pk,item from diaryItems");
    while($record = mysqli_fetch_assoc($qury)) {
        array_push($items, array('pk' => $record['pk'],'item' => $record['item']));
    }
    return $items;
}

function getItemsByUserConsumed($token) {
    global $mysqli;
    $userFK = getUserFKByToken($token);
    $items = array();
    if(!empty($userFK)) {
        if($query = $mysqli->prepare("select diaryItems.item,diary.timestamp from diaryItems
                       left join diary on diaryItems.pk=diary.itemFK
                        where userFK=? order by diary.timestamp desc")) {
            $query->bind_param("s", $userFK);
            $query->execute();
            $item = "";
            $timestamp = "";
            $query->bind_result($item, $timestamp);
            while($query->fetch()) {
                array_push($items, array("item" => $item, "timestamp" => $timestamp));
            }
        }
    }
    return $items;

}

function getSummaryOfItems($token) {
    global $mysqli;
    $userFK = getUserFKByToken($token);
    $summaryOfItems = array();
    if(!empty($userFK)) {

        if($query = $mysqli->prepare("select  di.item, count(timestamp) as count from diaryItems as di
           left join diary as d on di.pk= d.itemFK where d.userFK = ? group by di.item")) {
            $query->bind_param("s", $userFK);
            $query->execute();
            $item = "";
            $count = 0;
            $query->bind_result($item, $count);
            while($query->fetch()) {
                array_push($summaryOfItems, array("item" => $item, "count" => $count));
            }
        }
    }
    return $summaryOfItems;

}

function updateItemsConsumed($jsonData) {
    $token = $jsonData['token'];
    $itemFK = $jsonData['itemFK'];
    global $mysqli;
    $userFK = getUserFKByToken($token);
    if(empty($userFK)) {
        return "AUTH_FAIL";
    } else if (!isItemFKValid($itemFK)) {
        return "FAIL";
    } else {
        if($query = $mysqli->prepare("insert into diary (userFK, itemFK) values (?,?)")) {
            $query->bind_param("ss", $userFK, $itemFK);
            $query->execute();
            return "OK";
        }
    }

}


function getUserFKByToken($token) {
    global $mysqli;
    $userToken = '';

    if($query = $mysqli->prepare("select users.pk from users inner join tokens on users.user = tokens.user
                                   where tokens.token = ?")) {
        $query->bind_param("s", $token);
        $query->execute();
        $query->bind_result($userToken);
        $query->fetch();
        $query->free_result();
    }
    return $userToken;
}

function isItemFKValid($itemFK) {
    global $mysqli;
    $itemPK = '';

    if($query = $mysqli->prepare("select pk from diaryItems where pk=?")) {
        $query->bind_param("s", $itemFK);
        $query->execute();
        $query->bind_result($itemPK);
        $query->fetch();
        $query->free_result();
    }

    return !empty($itemPK);
}
