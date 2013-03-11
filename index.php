<?php
session_start();

require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_Oauth2Service.php';
require_once 'google-api-php-client/src/contrib/Google_CalendarService.php';

require('eventsToBeScheduled.php');

require('db/config.php');        
  
$client = new Google_Client();
$client->setApplicationName($googleApplicationName);
$client->setClientId($googleClientId);
$client->setClientSecret($googleClientSecret);
$client->setRedirectUri($googleRedirectUri);
$client->setDeveloperKey($googleDeveloperKey);
$cal = new Google_CalendarService($client);
$oauth2 = new Google_Oauth2Service($client);

if (isset($_GET['code'])) {
  $client->authenticate($_GET['code']);
  $_SESSION['token'] = $client->getAccessToken();
  $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
  return;
}

if (isset($_SESSION['token'])) {
 $client->setAccessToken($_SESSION['token']);
}

if (isset($_REQUEST['logout'])) {
  unset($_SESSION['token']);
  unset($_SESSION['email']);
  $client->revokeToken();
}

if ($client->getAccessToken()) {
  $user = $oauth2->userinfo->get();

  $email = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
  $_SESSION['email'] = $email;

  ////////////////////////////////////////////
  
  $calList = $cal->calendarList->listCalendarList();
  //$calListMarkup = "<h1>Calendar List</h1><pre>" . print_r($calList["items"], true) . "</pre>";
  
  $logiCalId = "";
  $logiCalTimeZone = "";
  foreach ($calList["items"] as $tempCal) {
    if($tempCal["summary"] == "LogiCal Tasks") {
        $logiCalId = $tempCal["id"];
    }
    
    if($tempCal["id"] == $_SESSION['email']){
        $logiCalTimeZone = $tempCal["timeZone"];
    }
  }
  
  //print ("$logiCalTimeZone");
  
  if($logiCalId == "") {
    $calendar = new Google_Calendar();
    $calendar->setSummary('LogiCal Tasks');
    $calendar->setTimeZone($logiCalTimeZone);

    $createdCalendar = $cal->calendars->insert($calendar);
    $logiCalId = $createdCalendar->getId();
  }
  
  
  if(isset($_GET["addATaskEvent"])) {
    // a GET request with this parameter equal to 1 should be made
    // with a jQuery GET AJAX call every 1 second (with setTimeout)
    // until that request has a response of "done".
    require ("addATaskEvent.php");  
  } else {
  
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">

<style type="text/css">
    #formPopup {
        display:none;
        position:absolute;
        background-color:#ffffff;
        height:500px;
        width: 160px;
        text-align:center;
    }
    #slider {
        width:85%;
        margin-left:auto;
        margin-right:auto;
    }
</style>

<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.0/themes/base/jquery-ui.css" />
<script src="http://code.jquery.com/jquery-1.8.3.js"></script>
<script src="http://code.jquery.com/ui/1.10.0/jquery-ui.js"></script>

<script src="logical.js"></script>

</head>
<body>
<h1>LogiCal</h1>

<?php
    print "<a class='logout' href='?logout'>Logout</a>";

      ////////////////////////////////////////////////////////
      
      require ("addTask.php");
      
      require ("getTasks.php");
            
      $tasks = getTasks();
      
      $taskListMarkup = "";
      
      foreach ($tasks as $task) {
        $taskListMarkup = $taskListMarkup . "<div style=\"border:1px solid black;\" >Id: " . $task["id"] . "<br/>What: " . $task["what"] . "<br/>Due date: " . $task["due_date"] . "<br/>Due Time: " . $task["due_hour"] . ":" . $task["due_minute"] . " " . $task["due_am_or_pm"] . "<br/>Estimated effort: " . $task["estimated_effort"] . "<br/>Task distribution: " . $task["task_distribution"] . "</div>";
      }
      
      ////////////////////////////////////////////
      
      //$eventList = $cal->events->listEvents($logiCalId);
      /*
      foreach ($eventList["items"] as $tempEvt) {
        $calListMarkup = $calListMarkup . "description" . $tempEvt["description"] . "<br/>";
      }
      */
      //$calListMarkup = $calListMarkup . print_r($eventList["items"], true);
  
      if(isset($_SESSION['email'] )){ 
        print $_SESSION['email']; 
      }
      /*
      if(isset($calListMarkup)){ 
        print $calListMarkup; 
      }
      */    
?>

<div style="width:160px;height:500px;border:1px solid black;position:relative;">
  <div id="formPopup">
      <h3>New Task</h3>
      <form name="myform" action="index.php" method="POST">
          <p style="margin: 0px;">  
            What<textarea rows="4" cols="17" name="what"></textarea>
            <br><br>
            Due Date<input type="text" name="due_date" id="datepicker" size="18">
            <br>
            Due Time<br><input type="text" name="due_hour" size="1">:<input type="text" name="due_minute" size="1">
            <select name="due_am_or_pm">
                <option value="AM">AM</option>
                <option value="PM" selected="">PM</option>
            </select>
            <br><br>
            Estimated Effort (in hours)<input type="text" name="estimated_effort" value="" size="18">
            <br><br>Task Distribution
            <input type="hidden" id="task_distribution" name="task_distribution" value="50">
          </p>
            <div id="slider"></div>
          <p style="margin: 0px;">
            <img src="taskDistribution.png" style="margin-bottom: 15px;"></img>
            <input type="submit" name="save" value="Save">
            <input type="button" name="cancel" value="Cancel" onclick="cancelTaskForm(this.form);">
        </p>
      </form>      
  </div>
  <form NAME="add" ACTION="" METHOD="GET">
      <input TYPE="button" NAME="createTask" Value="Create Task" onClick="popupTaskForm();">
  </form>
  
  <div style="height:400px;overflow-y:scroll;border:1px solid red;">
      <div id="content_div">
      <?
        if(isset($taskListMarkup)){ 
            print $taskListMarkup; 
        }
      ?>
      </div>
  </div>
</div>

<?php
/*  
  foreach ($tasks as $task) {
    $event = new Google_Event();
    $event->setSummary($task["what"] . " is due now");
    $event->setDescription($task["id"]);
    $start = new Google_EventDateTime();
    
    $explodedDate = explode( '/', $task["due_date"]);
    
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
    if($task["due_am_or_pm"] == "AM"){
        if(intval($task["due_hour"]) == 12) {
            $hour = "00";
        } else {
            $hour = "0" . intval($task["due_hour"]);
        }
        
        $minute = $task["due_minute"];
    } else {
        if(intval($task["due_hour"]) == 12) {
            $hour = 12;
        } else {
            $hour = intval($task["due_hour"])+12;
        }
        $minute = $task["due_minute"];
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
    
    // need to check if the event is already on the calendar by checking all of the  Google calendar event descriptions of events in the calendar with id = $logiCalId for an event with a description that matches $task["id"]. in the future, upon task creation, events should be created for that task and put in an Events table. The event ids should correspond to the Google calendar event descriptions.
    
    $evtAlreadyExists = false;
    
    foreach ($eventList["items"] as $tempEvt) {
        if($tempEvt["description"] == $task["id"]) {
            $evtAlreadyExists = true;
        }
    }
    
    if($evtAlreadyExists == false) {
        $start->setDateTime($startDateTime);
        $start->setTimeZone($logiCalTimeZone);
        $event->setStart($start);
        $end = new Google_EventDateTime();
        $end->setDateTime($endDateTime);
        $end->setTimeZone($logiCalTimeZone);
        $event->setEnd($end);
        $createdEvent = $cal->events->insert($logiCalId, $event);
    }
    
    /////////////////////////////////////////////////////////////
    
    
  }
*/  
  
  // Create an event
  /*
  $event = new Google_Event();
  $event->setSummary('Sample LogiCal Task');
  $start = new Google_EventDateTime();
  $start->setDateTime('2013-02-12T10:00:00');
  $start->setTimeZone($logiCalTimeZone);
  $event->setStart($start);
  $end = new Google_EventDateTime();
  $end->setDateTime('2013-02-12T20:30:00');
  $end->setTimeZone($logiCalTimeZone);
  $event->setEnd($end);
  $createdEvent = $cal->events->insert($logiCalId, $event); 
  */
  ////////////////////////////////////////////
  
  // The access token may have been updated lazily.
  $_SESSION['token'] = $client->getAccessToken();
?>

</body></html>  
  
<?php  
  }
} else {
  $authUrl = $client->createAuthUrl();
  print "<a class='login' href='$authUrl'>Connect Me!</a>";
}

?>