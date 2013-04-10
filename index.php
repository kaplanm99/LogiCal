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
  //$redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  $redirect = 'https://www.google.com/calendar/render?tab=cc';
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
  
  $settings = $cal->settings->listSettings();

  $timeZone = "America/New_York";
  
  foreach($settings['items'] as $setting) {
      if($setting['id'] == "timezone") {
          $timeZone = $setting['value'];
      }
  }
  
  ////////////////////////////////////////////
  
  $calList = $cal->calendarList->listCalendarList();
  //$calListMarkup = "<h1>Calendar List</h1><pre>" . print_r($calList["items"], true) . "</pre>";
  
  require('LogiCalTasksCalendar.php');
  $ltCal = new LogiCalTasksCalendar($_SESSION['email'] );
  
  if($ltCal->getId() == "") {
    $calendar = new Google_Calendar();
    $calendar->setSummary('LogiCal Tasks');
    $calendar->setTimeZone($timeZone);

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
        height:461px;
        overflow-y:auto;
        clear: both;
    }
    
    .headerButton {
        float: left;
        margin: 2px;
        text-decoration: none;
        background: #f5f5f5;
        border: 1px solid #dcdcdc;
        color: #444;
        cursor: pointer;
        font-size: 11px;
        font-weight: bold;
        padding: 2px;
        text-align: center;
        border-radius: 2px;
    }
    
</style>

<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.0/themes/base/jquery-ui.css" />
<script src="http://code.jquery.com/jquery-1.8.3.js"></script>
<script src="http://code.jquery.com/ui/1.10.0/jquery-ui.js"></script>

<script src="logical.js"></script>

</head>
<body>

<?php
      require ("deleteTask.php");
      
      require ("addTask.php");
      
      require ("getTasks.php");
            
      $tasks = getTasks();
      
      $taskListMarkup = "";
      
      foreach ($tasks as $task) {
        $taskListMarkup = $taskListMarkup . "
        <div class=\"task\" >
            <form action=\"index.php\" method=\"POST\">
                <input type=\"hidden\" name=\"deleteId\" value=\"" . $task["id"] . "\" >
                <input type=\"image\" src=\"deleteButton.png\" class=\"deleteButton\" >
            </form>
            <span onclick=\"editTask('".$task["what"]."', '".$task["due_date"]."', '".$task["due_hour"]."', '".$task["due_minute"]."', '".$task["due_am_or_pm"]."', '".$task["estimated_effort"]."', '".$task["task_distribution"]."');\" >" . 
            $task["what"] . 
            "<br/>Due: " . $task["due_date"] . " " . $task["due_hour"] . ":" . $task["due_minute"] . $task["due_am_or_pm"] . 
            "<br/>Hours Remaining: " . $task["estimated_effort"] . 
        "</span></div>";
      }
?>

<div style="width:160px;height:500px;position:relative;font: 12px Arial,sans-serif;">
  <div id="formPopup">
      <h3>New Task</h3>
      <form id="myform" name="myform" action="index.php" method="POST">
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
  <?php
    if(isset($_SESSION['email'] )){ 
        print "<span id=\"email\">";
        print $_SESSION['email']; 
        print "</span>";
    }  
  ?>
  <span onclick="popupTaskForm();" class="headerButton">Create Task</span>
  <a class="headerButton" href="?logout">Logout</a>
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
  print "<a class='login' target='_blank' href='$authUrl'>Connect Me!</a>";
}

?>