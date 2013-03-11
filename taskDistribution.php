<?php 

function taskDistribution($slider_value_input, $effort_hours, $available_days) {
    //$slider_value = floatval(intval($_GET['slider_value'])-50)/25;
    //$effort_hours = intval($_GET['estimated_effort']);
    //$available_days = intval($_GET['available_days']);
    
    $slider_value = floatval(intval($slider_value_input)-50)/25;

    if($effort_hours/$available_days > 4) {
        $task_distribution =  pow(abs($slider_value),1.2);
    } else {
        $task_distribution =  pow(abs($slider_value),4);
    }


    $normalized_day =  1/floatval($available_days);

    $events_per_day = array();

    if($task_distribution == 0) {
        for ($i=0; $i<$available_days; $i++) {
            $events_per_day[] = $effort_hours/$available_days;
        }
    } else {
        for ($i=0; $i<$available_days; $i++) {
        
            $day = $i/$available_days;
            
            $events_per_day[] = ( ($task_distribution*$day + 1) + ($task_distribution*($day+$normalized_day) + 1) )*0.5*$normalized_day*$effort_hours;
            
            rsort($events_per_day);
        }
    }

    //print_r ($events_per_day);

    $day_event_counts = array();

    //to determine the actual events counts per each day, you need to convert the decimal point values above to integers by multiplying by available_days and then keep and accumulator that rolls over the event count into the next day if it is less than one. Stop rolling it over if there is only that amount left in total (so need to keep a total_event count and subtract from it).

    $total_event_count = $effort_hours;
    $accumulator = 0;

    for ($i=0; $i<$available_days; $i++) {

        if($total_event_count >= 0) {
        
            if($total_event_count - $events_per_day[$i] >= 0) {
                if($events_per_day[$i] > 12) {
                    $accumulator = $accumulator + 12;
                } else {
                    $accumulator = $accumulator + $events_per_day[$i];
                }
            } else {
                $accumulator = $accumulator + $total_event_count;
            }
            if($accumulator >= 1){
                
                $temp_event_count = intval($accumulator);
                $total_event_count = $total_event_count - intval($accumulator);
                $accumulator = $accumulator - intval($accumulator);  
                            
            } else {
                $temp_event_count = 0;
            }
            
            if ($i == $available_days-1) {
                $day_event_counts[] = $temp_event_count + $total_event_count;
                
            } else {
                $day_event_counts[] = $temp_event_count;
            }
        }
    }

    if($slider_value > 0) {
        sort($day_event_counts);   
    } elseif($slider_value < 0) {
        rsort($day_event_counts);
    }

    //print_r ($day_event_counts);

    return $day_event_counts;
}

// if $task_distribution negative then just reverse the $day_event_counts array from the $task_distribution positive $day_event_counts .

/*


For the middle value of the task distribution slider (50), the days the events should be scheduled every int(available_days/number_of_events) starting with the current_day.

to determine the actual events counts per each day, you need to convert the decimal point values above to integers by multiplying by available_days and then keep and accumulator that rolls over the event count into the next day if it is less than one. Stop rolling it over if there is only that amount left in total (so need to keep a total_event count and subtract from it).
*/
?>