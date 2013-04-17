function cancelTaskForm (form) {
    document.getElementById('myform').what.value = "";
    document.getElementById('myform').due_date.value = "";
    document.getElementById('myform').due_hour.value = "";
    document.getElementById('myform').due_minute.value = "";
    document.getElementById('myform').due_am_or_pm.value = "";    
    document.getElementById('myform').estimated_effort.value = "";
    document.getElementById('myform').task_distribution = "";
    $("#slider" ).slider("value",50);
    
    document.getElementById('formPopup').style.display='none';
    document.getElementById('interactWithTask').style.display='none';
    document.getElementById('homeScreen').style.display='block';
}

function popupTaskForm () {
    document.getElementById('homeScreen').style.display='none';
    document.getElementById('interactWithTask').style.display='none';
    document.getElementById('formPopup').style.display='block';    
}

function popupInteractWithTask () {
    document.getElementById('homeScreen').style.display='none';
    document.getElementById('formPopup').style.display='none';
    document.getElementById('interactWithTask').style.display='block';   
}

function changeTaskDistribution(event, ui) {
    $("#task_distribution").attr('value',ui.value);
}

function changeHoursCompleted() {

}

function interactWithTask(t_id, t_what, t_due_date, t_due_hour, t_due_minute, t_due_am_or_pm, t_estimated_effort, t_task_distribution) {
    // need a hidden editTaskId form to be set to taskId and reset to -1 if cancelled.
    
    document.getElementById('taskId').value = t_id;
    
    
    document.getElementById('myform').what.value = t_what;
    document.getElementById('myform').due_date.value = t_due_date;
    document.getElementById('myform').due_hour.value = t_due_hour;
    document.getElementById('myform').due_minute.value = t_due_minute;
    document.getElementById('myform').due_am_or_pm.value = t_due_am_or_pm;    
    document.getElementById('myform').estimated_effort.value = t_estimated_effort;
    document.getElementById('myform').task_distribution = t_task_distribution;
    $("#slider" ).slider("value",t_task_distribution);

    popupInteractWithTask();
}

$(document).ready(function(){
    $( "#slider" ).slider({
      slide: changeTaskDistribution
    });
    $("#slider" ).slider("value",50);
    
    $( "#hoursCompletedSlider" ).slider({
      slide: changeHoursCompleted
    });
    $("#hoursCompletedSlider" ).slider("value",50);
    
    $( "#datepicker" ).datepicker({
      changeMonth: true,
      changeYear: true
    });
    $( "#ui-datepicker-div" ).css("font-size", "50%");
    $( ".ui-slider-handle" ).css("z-index", "1");
    
    $( ".task" ).mouseover(function(){
      $(this).children(".taskArrow").css("display","block");
      $(this).css("background-color","rgb(255, 255, 192)");
    });          
    
    $( ".task" ).mouseout(function(){
      $(this).children(".taskArrow").css("display","none");
      $(this).css("background-color","rgb(255, 255, 255)");
    }); 
});