<?php
    
    //print("Select TaskEvents from the db, belonging to the user with this email that have a value of 0 for added_to_g_cal. Should print a response of \"done\" if there are no TaskEvents meeting that criteria returned. If any TaskEvent is returned then add that to the google calendar using the example below with the new parameters. If the add is successful (look at Google request response for 200 or the fact that there is no exception (try/catch), then update the row in the db to have added_to_g_cal = 1");

    require('db/config.php');
    $mysqli = new mysqli($host, $username, $password, $db);                    
    if ($stmt = $mysqli->prepare("SELECT te.start_time, te.end_time, t.what FROM TaskEvents te, Tasks t WHERE te.email = ? AND ISNULL(te.event_id) AND te.task_id = t.id;")) {
        $stmt->bind_param('s', $_SESSION['email']);
        $stmt->execute();
        
        $stmt->bind_result($te_start_time, $te_end_time, $t_what);
                    
        if($stmt->fetch()) {
            //print($te_event_id . "," . $te_start_time . "," . $te_end_time . "," . $t_what);
            
            try {
                
                $event = new Google_Event();
                $event->setSummary($t_what);
                $event->setDescription("");
                
                $start = new Google_EventDateTime();
                $start->setDateTime($te_start_time);
                $start->setTimeZone("America/New_York");
                $event->setStart($start);
                
                $end = new Google_EventDateTime();
                $end->setDateTime($te_end_time);
                $end->setTimeZone("America/New_York");
                $event->setEnd($end);
                
                unset($createdEvent);
                
                $createdEvent = $cal->events->insert($ltCal->getId(), $event);
                
                /////
                
                $stmt->close();
                
                if(isset($createdEvent)) {
                // set event_id here from the returned event here
                // and keep in mind that email, start_time is the primary key
                    if ($stmt = $mysqli->prepare("UPDATE TaskEvents SET event_id = ? WHERE email = ? AND start_time = ?;")) {
                        $stmt->bind_param('sss', $createdEvent['id'], $_SESSION['email'], $te_start_time);
                        $stmt->execute();
                    }
                    
                    $stmt->close();
                }
                
            } catch (Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
        } else{
            print("done");
        }

        $stmt->close();
    }
       
    $mysqli->close();
    
    
?>