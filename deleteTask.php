<?php
session_start();

function deleteTask($client,$cal,$ltCal) {
    if(isset($_SESSION['email']) && isset($_POST["delete"]) && isset($_POST["taskId"])) {  
        $deleteId = strip_tags($_POST["taskId"]);
        $deleteId = trim($deleteId);
        $deleteId = intval($deleteId);

        require('db/config.php');
        $mysqli = new mysqli($host, $username, $password, $db);                    
        if ($stmt = $mysqli->prepare("SELECT event_id FROM TaskEvents WHERE email = ? AND task_id = ?;")) {
            $stmt->bind_param('si', $_SESSION['email'], $deleteId);
            $stmt->execute();
            
            $stmt->bind_result($delete_event_id);
            
            try 
            {
                $client->setUseBatch(true);
                $batch = new Google_BatchRequest();
                
                while($stmt->fetch()) {
                    $batch->add($cal->events->delete($ltCal->getId(), $delete_event_id));        
                }
                $result = $batch->execute();
                $client->setUseBatch(false);
            
            } catch (Exception $e) {
                //echo 'Caught exception: ',  $e->getMessage(), "\n";
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
    }
}
?>