<?php

function eventsToBeScheduled($cal, $calList, $sliderValue, $effortHours, $dueDateTime) {

  $fBRequest = new Google_FreeBusyRequest();
  
  $currentTime = new DateTime(NULL, new DateTimeZone('America/New_York'));
  
  $format = 'Y-m-d H:i:s';
  $timeMin =  str_replace(" ", "T", $currentTime->format($format)) . ".000-05:00";
  
  $fBRequest->setTimeMin($timeMin);
  //$fBRequest->setTimeMax("2013-03-12T12:57:00.000-05:00");
  
  //print($dueDateTime);
  
  $fBRequest->setTimeMax($dueDateTime);
  
  $fBRequest->setTimeZone("America/New_York");
  
  $fbRequestItems = array();
  
  foreach ($calList["items"] as $tempCal) {
    $tempItem = new Google_FreeBusyRequestItem();
    $tempItem->setId($tempCal["id"]);
    $fbRequestItems[] = $tempItem;
  }
  
  $fBRequest->setItems($fbRequestItems);
  
  try {
      $freeBusy = $cal->freebusy->query($fBRequest);
  } catch (Exception $e) {
      echo 'Caught exception: ',  $e->getMessage(), "\n";
  }
  
  $busyByDay = array();
  
  // populate busyByDay with an array at a key for every day until the last day in the range. need to add full days of events (within start, end range) if the day does not exist in free busy. use DateTime->modify("+1 day")
  
  $format = 'Y-m-d';
  
  $startDT = $currentTime->format($format);
  //$endDT = "2013-03-12";
  $endDT = substr($dueDateTime, 0,10);
  $tempDT = new DateTime($startDT);
  
  $busyByDay[($tempDT->format($format))] = array();
  // need to add an event from 10am to current date time to the day array above
  $busyByDay[($tempDT->format($format))][] = "10:00-" . $currentTime->format("H:i");      
  
  //print($tempDT->format($format). "<br/>");
  
  if($startDT != $endDT) {
  
      $tempDT->modify('+1 day');
      
      while($tempDT->format($format) != $endDT) {
        $busyByDay[($tempDT->format($format))] = array();
        //print($tempDT->format($format) . "<br/>");
        $tempDT->modify('+1 day');
      }
      
      $busyByDay[($tempDT->format($format))] = array();
      // TODO: need to add an event from end date time to 10pm to the day array above
      $busyByDay[($tempDT->format($format))][] = substr($dueDateTime, 11,5) . "-22:00";
      
      //print($tempDT->format($format) . "<br/>");
  }
  
  /*
  foreach($busyByDay as $key=>$tempBusy) {
    print($key . "<br/>");  
  }
  */
  
  // need to check that start and end DateTime are not the same day before
  // adding it to the array
  
  foreach ($freeBusy["calendars"] as $tempCal) {
    foreach ($tempCal["busy"] as $tempEvent) {
        //print (substr($tempEvent["start"], 0, 19) . ".00");
        
        //same day
        if(substr($tempEvent["start"], 0, 10) == substr($tempEvent["end"], 0, 10)) {
            /*
            $startHour = intval( substr($tempEvent["start"], 11,2) );
            $startMinute = intval( substr($tempEvent["start"], 14,2) );
            
            $endHour = intval( substr($tempEvent["end"], 11,2) );
            $endMinute = intval( substr($tempEvent["end"], 14,2) );
            
            $minutesDiff = ($endHour*60 + $endMinute) - ($startHour*60 + $startMinute)
            */
            
            // cluster into 2d array here
            // need to check if date key exists
            $date = substr($tempEvent["start"], 0, 10);
            
            // if it doesn't then create array and store it in that key
            /*
            if(! array_key_exists($date, $busyByDay)) {
                $busyByDay[$date] = array();
            }
            */
            
            // either way append the "startTime,endTime" (just need hours and minutes) to the array stored at that key in the larger array.
            $busyByDay[$date][] = substr($tempEvent["start"], 11,5) . "-" . substr($tempEvent["end"], 11,5);
            
            
        }
    }
  }
  
  //$calListMarkup = "<h1>Calendar List</h1><pre>" . print_r($busyByDay, true) . "</pre>";
  
  $freeHoursByDay = array();
  
  foreach ($busyByDay as $key=>$busyDay) {
    //print($key);
    
    sort($busyDay);
    
    $earliestTimeInMinutes = 10*60;
    $latestTimeInMinutes = 22*60;
    
    $busyPeriods = array();
    
    $busyPeriods[] = "10:00-10:00";
    
    foreach($busyDay as $busyPeriod) {
        $tempStartTimeInMinutes = (intval(substr($busyPeriod, 0,2)) * 60) + intval(substr($busyPeriod, 3,2));
        
        if($tempStartTimeInMinutes < $earliestTimeInMinutes) {
            $newBusyPeriod = "10:00-";
        } elseif($tempStartTimeInMinutes > $latestTimeInMinutes) {
            $newBusyPeriod = "22:00-";
        } else {
            $newBusyPeriod = substr($busyPeriod, 0,6);
        }
        
        $tempEndTimeInMinutes = (intval(substr($busyPeriod, 6,2)) * 60) + intval(substr($busyPeriod, 9,2));
        
        if($tempEndTimeInMinutes < $earliestTimeInMinutes) {
            $newBusyPeriod = $newBusyPeriod . "10:00";
        } elseif($tempEndTimeInMinutes > $latestTimeInMinutes) {
            $newBusyPeriod = $newBusyPeriod . "22:00";
        } else {
            $newBusyPeriod = $newBusyPeriod . substr($busyPeriod, 6,5);
        }
        
        $busyPeriods[] = $newBusyPeriod;
        
    }
    
    $busyPeriods[] = "22:00-22:00";
    
    /*
    foreach($busyPeriods as $busyPeriod) {
        print($busyPeriod . "<br/>");
    }
    */
    
    $freeHours = array(); 
    
    for($i = 0;$i < count($busyPeriods)-1;$i++) {
        $freePeriodStartHour = intval(substr($busyPeriods[$i], 6,2));
        
        $freePeriodStartMinute = substr($busyPeriods[$i], 9,2);
        $freePeriodStart = $freePeriodStartHour * 60 + intval($freePeriodStartMinute);
        
        $freePeriodEndHour = intval(substr($busyPeriods[$i+1], 0,2));
        $freePeriodEndMinute = substr($busyPeriods[$i+1], 3,2);
        $freePeriodEnd = $freePeriodEndHour * 60 + intval($freePeriodEndMinute);
        //
        if( ($freePeriodEnd-$freePeriodStart) >= 60 ) {
        
            $hoursDiff = intval(($freePeriodEnd - $freePeriodStart)/60);
            //print(substr($busyDay[0], 0,5) . " time is " . $hoursDiff . " hours from free 10am");
            
            for($j = 0;$j < $hoursDiff;$j++) {
                
                $freeHourStartHour = $freePeriodStartHour+$j;
                $freeHourEndHour = $freeHourStartHour+1;
                
                /*
                if($freeHourStartHour<10) {
                    $freeHourStartHour = "0" . $freeHourStartHour;
                }
                
                if($freeHourEndHour<10) {
                    $freeHourEndHour = "0" . $freeHourEndHour;
                }
                */
                
                $freeHours[] = $freeHourStartHour . ":" . $freePeriodStartMinute . "-" . $freeHourEndHour . ":" . $freePeriodStartMinute;
                
            }
        }
        //
    }
    
    $freeHoursByDay[$key] = $freeHours;
    
  }
  
  /*
  foreach($freeHoursByDay as $key=>$value){
    print("<br/><br/>" . $key);
    
    foreach($value as $val){
        print("<br/>" . $val);
    }
  }
  */
  require('taskDistribution.php');
  
  $potentialTaskDistribution = taskDistribution( $sliderValue, $effortHours, count($freeHoursByDay) );
  
  $eventsToBeScheduled = array();
  $eventsScheduledByDay = array();
  
  $eventOverflowCount = 0;
  $dayNum = 0;
  
  $format = 'Y-m-d';
  $startDT = $currentTime->format($format);
  
  for($dayNum = 0;$dayNum < count($freeHoursByDay);$dayNum++){
    $tempDT = new DateTime($startDT);
    $tempDT->modify('+' . $dayNum . ' day');
    
    $day = $tempDT->format($format);
  
    $eventsScheduledByDay[$dayNum] = 0;
    
    $eventOverflowCount += $potentialTaskDistribution[$dayNum];
    
    while( $eventOverflowCount > 0 && $eventsScheduledByDay[$dayNum] < count($freeHoursByDay[$day]) ) {
        $freeHour = $freeHoursByDay[$day][$eventsScheduledByDay[$dayNum]];
        $eventsScheduledByDay[$dayNum]++;
        $eventsToBeScheduled[] = $day . "T" . $freeHour;
        $eventOverflowCount--;     
    }
  
  }
  
  if($eventOverflowCount > 0) {
    // reverse the loop above
    for($dayNum = count($freeHoursByDay)-1;$dayNum >= 0;$dayNum--){
        $tempDT = new DateTime($startDT);
        $tempDT->modify('+' . $dayNum . ' day');
        
        $day = $tempDT->format($format);
      
        while( $eventOverflowCount > 0 && $eventsScheduledByDay[$dayNum] < count($freeHoursByDay[$day]) ) {
            $freeHour = $freeHoursByDay[$day][$eventsScheduledByDay[$dayNum]];
            $eventsScheduledByDay[$dayNum]++;
            $eventsToBeScheduled[] = $day . "T" . $freeHour;
            $eventOverflowCount--;     
        }
      
    }
  }
  /*
  foreach($eventsToBeScheduled as $evt) {
    print($evt."<br/>");
  }
  */
  return $eventsToBeScheduled;
  
  // TODO: give the user a warning note on that task in the task list(red !) ).
  
}
?>