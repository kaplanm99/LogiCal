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
        background-color:#ffffff;
        height:500px;
        width: 160px;
        text-align:center;
    }
    #interactWithTask {
        display:none;
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
        border-bottom: 1px solid grey;
        font-size: 13px;
        padding-top: 4px;
        padding-bottom: 4px;
        padding-left: 4px;
    }
    .taskList {
        height:440px;
        border-top:1px solid black;
        overflow-y:auto;
        clear: both;
    }
    
    .headerButton {
        margin: 0px;
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
    
    #myform {
        padding-top: 10px;
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
      
      deleteTask($client,$cal,$ltCal);
      
      require ("addTask.php");
      
      require ("getTasks.php");
            
      $tasks = getTasks();
      
      $taskListMarkup = "";
      
      foreach ($tasks as $task) {
        
        $jsWhat = $task["what"];
        $jsWhat = str_replace("\r\n", "\\n", $jsWhat);
        
        $jsSubtasks = $task["subtasks"];
        $jsSubtasks = str_replace("\r\n", "\\n", $jsSubtasks);
        
        $taskListWhat = $task["what"];
        $taskListWhat = str_replace("\r\n", "<br>", $taskListWhat);
        
        
        $taskListMarkup = $taskListMarkup . "
        <div class=\"task\" >
            <div class=\"taskArrow\" title=\"Interact with this task\" style=\"display:none;float: right;color: rgb(37, 73, 167);   font-weight: bold;font-size: 20px;position: relative;cursor: pointer;top: -4px;\" onclick=\"interactWithTask('" . $task["id"] . "', '" . $jsWhat ."', '".$task["due_date"]."', '".$task["due_hour"]."', '".$task["due_minute"]."', '".$task["due_am_or_pm"]."', '".$task["estimated_effort"]."', '".$task["task_distribution"]."', '".$jsSubtasks."');\" >&gt;</div><div>" . $taskListWhat . 
            "</div><div style=\"color: grey;font-size: 9px;padding-top: 2px;\">Due " . $task["due_date"] . 
            ", " . $task["estimated_effort"] . " Hrs Left</div></div>";
      }
?>

<div style="width:160px;height:500px;position:relative;font: 12px Arial,sans-serif;">
  <div id="interactWithTask" style="position: relative;">
      <span class="headerButton" onclick="cancelTaskForm(this.form);" style="position: absolute; left: 10px;">
        <img src="backIcon.png" style="position: relative;  top: 2px;">
      </span>
      <h3 style="margin:0px;">Task</h3>
      <form action="index.php" method="POST">
        <p style="height: 20px; padding: 10px; margin: 0px; text-align: center;     border-bottom: 1px solid gainsboro;">
            <input type="hidden" id="taskId" name="taskId" value="" >
            <input type="submit" name="completed" class="headerButton" style="margin-top: 2px; margin-bottom: 2px;" value="Completed">
            <span onclick="popupTaskForm();" class="headerButton" style="margin-top: 2px; margin-bottom: 2px;">Edit</span>
            <input type="submit" name="delete" class="headerButton" style="margin-top: 2px; margin-bottom: 2px;" value="Delete">
        </p>
      
      <div style="border-bottom: 1px solid gainsboro;">
        <h5 style="font-size: 9px; margin-bottom: 5px;">Hours Completed Since Last Entry</h5>
        <div id="hoursCompletedSlider"></div>
        <p>
            <span class="headerButton" style="margin-top: 10px;  margin-bottom: 2px;">Submit</span>
            <span class="headerButton" onclick="cancelTaskForm(this.form);">Cancel</span>
        </p>
      </div>
      
      <p style="margin: 0px;">
          Subtasks/Details
          <textarea rows="8" cols="17" id="subtasks" name="subtasks" style="margin: 2px; height: 300px; width: 153px;"></textarea>
          <br>            
      </p>
            
      <p style="height: 20px;margin: 0px;text-align: center;">
        <input type="submit" name="saveSubtasks" class="headerButton" style="margin-top: 2px; margin-bottom: 2px;" value="Save">
        <span class="headerButton" onclick="cancelTaskForm(this.form);">Cancel</span>
      </p>
      
    </form>
  </div>
  
  <div id="formPopup">
      <h3 style="margin:0px;">Task</h3>
      <form id="myform" name="myform" action="index.php" method="POST">
          <p style="margin: 0px;">  
            What<textarea rows="4" cols="17" name="what"></textarea>
            <br>
            Due Date<input type="text" name="due_date" id="datepicker" size="18">
            <br>
            Due Time<br><input type="text" name="due_hour" size="1">:<input type="text" name="due_minute" size="1">
            <select name="due_am_or_pm">
                <option value="AM">AM</option>
                <option value="PM" selected="">PM</option>
            </select>
            <br>
            Hours of Work<input type="text" name="estimated_effort" value="" size="18">
            <br>Task Distribution
            <input type="hidden" id="task_distribution" name="task_distribution" value="50">
          </p>
            <div id="slider"></div>
          <p style="margin: 0px;">
            <img src="taskDistribution.png" style="margin-bottom: 15px;"></img>
            <p style="height: 20px;margin: 0px;text-align: center;">
                <input type="submit" class="headerButton" style="margin-top: 2px;  margin-bottom: 2px;" value="Save">
                <span class="headerButton" onclick="cancelTaskForm(this.form);">Cancel</span>
            </p>
        </p>
      </form>      
  </div>
  <div id="homeScreen">
      <?php
        if(isset($_SESSION['email'] )){ 
            print "<span id=\"email\">";
            print $_SESSION['email']; 
            print "</span>";
        }  
      ?>
      <p style="margin: 4px 0px;"> 
        <span onclick="popupTaskForm();" class="headerButton">Create Task</span>
        <a class="headerButton" href="?logout">Logout</a>
        <span class="headerButton"><img src="settingsIcon.png" style="position:relative;top:2px;"></span>
      </p>
      <span style="clear: both; float: left; border: 1px solid black; border-bottom: 1px solid white; position: relative; top: 1px; padding: 2px;">Active</span>
      <span style="float: left; border: 1px solid black; position: relative;  top: 1px;
        left: -1px; background-color: rgb(247, 247, 247); padding: 2px;">Completed</span>
      <span style="float: left; border: 1px solid black; position: relative; top: 1px;     left: -2px; background-color: rgb(247, 247, 247); padding: 2px;">Groups</span>
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