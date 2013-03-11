<?php
session_start();

function getTasks() {
    $tasks = array();
    
    if(isset($_SESSION['email'])) {    
        
        require('db/config.php');
        $mysqli = new mysqli($host, $username, $password, $db);                    
        
        if ($stmt = $mysqli->prepare("SELECT id, what, due_date, due_hour, due_minute, due_is_am, estimated_effort, task_distribution FROM Tasks WHERE email = ?;")) {
            $stmt->bind_param('s', $_SESSION['email']);
            $stmt->execute();
            $stmt->bind_result($id, $what, $due_date, $due_hour, $due_minute, $due_is_am, $estimated_effort, $task_distribution);
            
            while($stmt->fetch()) {
                if($due_is_am == 0) {
                    $due_am_or_pm = "AM";
                }
                else {
                    $due_am_or_pm = "PM";
                }
                
                if($due_hour == 0) {
                    $due_hour = "12";
                }
                
                if($due_minute == 0) {
                    $due_minute = "00";
                }
            
                $task = array('id' => $id, 'what' => $what, 'due_date' => $due_date, 'due_hour' => $due_hour, 'due_minute' => $due_minute, 'due_am_or_pm' => $due_am_or_pm, 'estimated_effort' => $estimated_effort, 'task_distribution' => $task_distribution);
                
                $tasks[] = $task;
            }
            
            $stmt->close();        
        }
           
        $mysqli->close();
    }
    
    return $tasks;
}
?>