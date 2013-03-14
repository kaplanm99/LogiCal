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
  
  require('LogiCalTasksCalendar.php');
  $ltCal = new LogiCalTasksCalendar($_SESSION['email'] );
  
  if($ltCal->getId() == "") {
    $calendar = new Google_Calendar();
    $calendar->setSummary('LogiCal Tasks');
    $calendar->setTimeZone("America/New_York");

    $createdCalendar = $cal->calendars->insert($calendar);
    
    $ltCal->setId($createdCalendar['id']);
    
  } else {
    try {
        $googleLogiCalTasksCal = $cal->calendarList->get($ltCal->getId());
        //if(isset($googleLogiCalTasksCal)){
        //    print ("HI");
        //} else {
        //    print ("BYE");
        //}
        
    } catch (Exception $e) {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
        // need to remake calendar using code above
    }  
  }
  
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
    #email {
        font-size: 12px;
    }
    .deleteButton {
        float:right;
    }
    .task {
        border-top:1px solid black;
        border-bottom: 1px solid black;
        font-size: 13px;
        margin-bottom: -1px;
    }
    .taskList {
        height:427px;overflow-y:auto;
    }
    
    .logout {
        float: right;
        margin-right: 5px;
    }
    
</style>

<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.0/themes/base/jquery-ui.css" />
<script src="http://code.jquery.com/jquery-1.8.3.js"></script>
<script src="http://code.jquery.com/ui/1.10.0/jquery-ui.js"></script>

<script src="logical.js"></script>

</head>
<body>

<?php
      require ("addTask.php");
      
      require ("getTasks.php");
            
      $tasks = getTasks();
      
      $taskListMarkup = "";
      
      foreach ($tasks as $task) {
        $taskListMarkup = $taskListMarkup . "<div class=\"task\" ><form action=\"index.php\" method=\"POST\"><input type=\"hidden\" name=\"deleteId\" value=\"" . $task["id"] . "\" ><input type=\"image\" src=\"deleteButton.png\" class=\"deleteButton\" ></form>" . $task["what"] . "<br/>Due: " . $task["due_date"] . " " . $task["due_hour"] . ":" . $task["due_minute"] . $task["due_am_or_pm"] . "<br/>Hours Remaining: " . $task["estimated_effort"] . "</div>";
      }
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
            Hours of Work<input type="text" name="estimated_effort" value="" size="18">
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
  <img src="logo.png" style="float: left;width: 100px;"/>
  
  <?php
    print "<a class='logout' href='?logout'>Logout</a>";
  
    if(isset($_SESSION['email'] )){ 
        print "<span id=\"email\">";
        print $_SESSION['email']; 
        print "</span>";
    }
  ?>
  <form NAME="add" ACTION="" METHOD="GET">
      <input TYPE="button" NAME="createTask" Value="Create Task" onClick="popupTaskForm();">
  </form>
  
  <div class="taskList" >
      <div id="content_div">
      <?
        if(isset($taskListMarkup)){ 
            print $taskListMarkup; 
        }
      ?>
      </div>
  </div>
</div>

</body></html>  

<?php
  // The access token may have been updated lazily.
  $_SESSION['token'] = $client->getAccessToken(); 
  
} else {
  $authUrl = $client->createAuthUrl();
  print "<a class='login' href='$authUrl'>Connect Me!</a>";
}

?>