<?php
session_start();

if(isset($_SESSION['email']) && isset($_POST["what"]) && isset($_POST["due_date"]) && isset($_POST["due_hour"]) && isset($_POST["due_minute"]) && isset($_POST["due_am_or_pm"]) && isset($_POST["estimated_effort"]) && isset($_POST['task_distribution'])) {    
    
    $what = strip_tags($_POST["what"]);
    $what = trim($what);
    $what = filter_var($what, FILTER_SANITIZE_STRING);
    
    $due_date = strip_tags($_POST["due_date"]);
    $due_date = trim($due_date);
    $due_date = filter_var($due_date, FILTER_SANITIZE_STRING);
    
    $due_hour = strip_tags($_POST["due_hour"]);
    $due_hour = trim($due_hour);
    $due_hour = intval($due_hour);
    
    if($due_hour > 12 || $due_hour < 1) {
        $due_hour = 12;
    }
    
    $due_minute = strip_tags($_POST["due_minute"]);
    $due_minute = trim($due_minute);
    $due_minute = intval($due_minute);
    
    if($due_minute > 59 || $due_minute < 0) {
        $due_minute = 0;
    }
    
    $due_am_or_pm = strip_tags($_POST["due_am_or_pm"]);
    $due_am_or_pm = trim($due_am_or_pm);
    $due_am_or_pm = filter_var($due_am_or_pm, FILTER_SANITIZE_STRING);
    
    if($due_am_or_pm == "AM") {
        $due_is_am = 0;
    }
    else {
        $due_is_am = 1;
    }
    
    $estimated_effort = strip_tags($_POST["estimated_effort"]);
    $estimated_effort = trim($estimated_effort);
    $estimated_effort = floatval($estimated_effort);
    
    $task_distribution = strip_tags($_POST["task_distribution"]);
    $task_distribution = trim($task_distribution);
    $task_distribution = intval($task_distribution);
    
    require('db/config.php');
    $mysqli = new mysqli($host, $username, $password, $db);                    
    if ($stmt = $mysqli->prepare("INSERT INTO Tasks (email, what, due_date, due_hour, due_minute, due_is_am, estimated_effort, task_distribution) VALUES (?,?,?,?,?,?,?,?);")) {
        $stmt->bind_param('sssiiidi', $_SESSION['email'], $what, $due_date, $due_hour, $due_minute, $due_is_am, $estimated_effort, $task_distribution);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
    }
       
    $mysqli->close();
    
    /////////////////////////////////////////////////////////////////
    
    $explodedDate = explode( '/', $due_date);
    
    $year = "";
    $month = "";
    $day = "";
    if(count($explodedDate) > 2) {
        $month = $explodedDate[0];
        $day = $explodedDate[1];
        $year = $explodedDate[2];
    }
    
    $hour = "";
    $minute = "";
    if($due_am_or_pm == "AM"){
        if(intval($due_hour) == 12) {
            $hour = "00";
        } elseif(intval($due_hour) >= 10) {
            $hour = intval($due_hour);
        } else {
            $hour = "0" . intval($due_hour);
        }
    } else {
        if(intval($due_hour) == 12) {
            $hour = 12;
        } else {
            $hour = intval($due_hour)+12;
        }
    }
    
    if($due_minute < 10) {
        $minute = "0" . $due_minute;
    } else {
        $minute = $due_minute;
    }
        
    
    $endDateTime = $year . "-" . $month . "-" . $day . "T" . $hour . ":" . $minute . ":00";
    $dtInput = $year . "-" . $month . "-" . $day . " " . $hour . ":" . $minute . ":00.00";
    
    $startDateTime = "";
    
    // use endTime - 1 hour for start time. but if due_hour is 12 and due_am_or_pm is am then make start dat the day before (need to use datetime library because of uneven month lengths) and start hour 23.
    
    $format = 'Y-m-d H:i:s';
    $tempDT = new DateTime($dtInput);
    $tempDT->modify('-1 hour'); 
    $startDateTime =  str_replace(" ", "T", $tempDT->format($format));
    
    //print ("start: $startDateTime , end: $endDateTime <br/>");
    
    $event = new Google_Event();
    $event->setSummary($what . " is due now");
    $event->setDescription($id); // TODO: remove this line
    
    $start = new Google_EventDateTime();
    $start->setDateTime($startDateTime);
    $start->setTimeZone($timeZone);
    $event->setStart($start);
    
    $end = new Google_EventDateTime();
    $end->setDateTime($endDateTime);
    $end->setTimeZone($timeZone);
    $event->setEnd($end);
    
    $createdEvent = $cal->events->insert($ltCal->getId(), $event);
    
    //print_r ($createdEvent);
    /*
    require('db/config.php');
    $mysqli = new mysqli($host, $username, $password, $db);                    
    if ($stmt = $mysqli->prepare("INSERT INTO TaskEvents (task_id, email, start_time, end_time, event_id) VALUES (?,?,?,?,?);")) {
        $stmt->bind_param('issss', $id, $_SESSION['email'], $startDateTime, $endDateTime, $createdEvent['id']);
        $stmt->execute();
        $stmt->close();
    }
    
    $mysqli->close();
    */
    /////////////////////////////////////////////////////////////////
    
    $eventsToBeScheduled = eventsToBeScheduled($cal, $calList, $task_distribution, $estimated_effort, $endDateTime, $timeZone);
    /*
    foreach($eventsToBeScheduled as $evt) {
      print($evt."<br/>");
    }
    print("------------------------------------<br/>");
    */
    require('db/config.php');
    $mysqli = new mysqli($host, $username, $password, $db);                    
    if ($stmt = $mysqli->prepare("INSERT INTO TaskEvents (task_id, email, start_time, end_time, event_id) VALUES (?,?,?,?,?);")) {
        $stmt->bind_param('issss', $id, $_SESSION['email'], $startDateTime, $endDateTime, $createdEvent['id']);
        $stmt->execute();
        
        foreach($eventsToBeScheduled as $evt) {
            //print($evt."<br/>");
            
            $evtDate = substr($evt, 0,11); 
          
            $startDT = $evtDate . substr($evt, 11,5) . ":00"; 
          
            $endDT = $evtDate . substr($evt, 17,5) . ":00"; 
          
            $event = new Google_Event();
            $event->setSummary($what);
            $event->setDescription("");
            
            $start = new Google_EventDateTime();
            $start->setDateTime($startDT);
            $start->setTimeZone($timeZone);
            $event->setStart($start);
            
            $end = new Google_EventDateTime();
            $end->setDateTime($endDT);
            $end->setTimeZone($timeZone);
            $event->setEnd($end);
            
            unset($createdEvent);
            
            $createdEvent = $cal->events->insert($ltCal->getId(), $event);
          
          
            $stmt->bind_param('issss', $id, $_SESSION['email'], $startDT, $endDT, $createdEvent['id']);
            $stmt->execute();
        }
    
        $stmt->close();
    }
       
    $mysqli->close();
    
}
?>