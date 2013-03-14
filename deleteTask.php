<?php
session_start();

if(isset($_SESSION['email']) && isset($_POST["deleteId"])) {    
    
    $deleteId = strip_tags($_POST["deleteId"]);
    $deleteId = trim($deleteId);
    $deleteId = intval($deleteId);
    
    require('db/config.php');
    $mysqli = new mysqli($host, $username, $password, $db);                    
    if ($stmt = $mysqli->prepare("SELECT event_id FROM TaskEvents WHERE email = ? AND task_id = ?;")) {
        $stmt->bind_param('si', $_SESSION['email'], $deleteId);
        $stmt->execute();
        
        $stmt->bind_result($delete_event_id);
                    
        while($stmt->fetch()) {
            $cal->events->delete($ltCal->getId(), $delete_event_id);
        }    
        $stmt->close();
    }
       
    $mysqli->close();
    
    require('db/config.php');
    $mysqli = new mysqli($host, $username, $password, $db);                    
    if ($stmt = $mysqli->prepare("DELETE FROM TaskEvents WHERE email = ? AND task_id = ?;")) {
        $stmt->bind_param('si', $_SESSION['email'], $deleteId);
        $stmt->execute();
        $stmt->close();
    }
    $mysqli->close();
    
    require('db/config.php');
    $mysqli = new mysqli($host, $username, $password, $db);                    
    if ($stmt = $mysqli->prepare("DELETE FROM Tasks WHERE email = ? AND id = ?;")) {
        $stmt->bind_param('si', $_SESSION['email'], $deleteId);
        $stmt->execute();
        $stmt->close();
    }
    $mysqli->close();
    
    //TODO: delete all TaskEvents with deleteId and delete the Task with deleteId
}    
?>