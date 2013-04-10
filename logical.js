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
}

function popupTaskForm () {
    document.getElementById('formPopup').style.display='block';
}

function changeTaskDistribution(event, ui) {
    $("#task_distribution").attr('value',ui.value);
}

function editTask(t_what, t_due_date, t_due_hour, t_due_minute, t_due_am_or_pm, t_estimated_effort, t_task_distribution) {
    // need a hidden editTaskId form to be set to taskId and reset to -1 if cancelled.
    
    document.getElementById('myform').what.value = t_what;
    document.getElementById('myform').due_date.value = t_due_date;
    document.getElementById('myform').due_hour.value = t_due_hour;
    document.getElementById('myform').due_minute.value = t_due_minute;
    document.getElementById('myform').due_am_or_pm.value = t_due_am_or_pm;    
    document.getElementById('myform').estimated_effort.value = t_estimated_effort;
    document.getElementById('myform').task_distribution = t_task_distribution;
    $("#slider" ).slider("value",t_task_distribution);

    popupTaskForm();
}

$(document).ready(function(){
    $( "#slider" ).slider({
      slide: changeTaskDistribution
    });
    
    $("#slider" ).slider("value",50);
    
    $( "#datepicker" ).datepicker({
      changeMonth: true,
      changeYear: true
    });
    $( "#ui-datepicker-div" ).css("font-size", "50%");
    $( ".ui-slider-handle" ).css("z-index", "1");
    
    $( ".task" ).click()          
});