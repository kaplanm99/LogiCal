function cancelTaskForm (form) {
    form.what.value = "";
    form.due_date.value = "";
    form.due_hour.value = "";
    form.due_minute.value = "";
    form.due_am_or_pm.value = "";    
    form.estimated_effort.value = "";
    form.task_distribution = "";
    $("#slider" ).slider("value",50);
    
    document.getElementById('formPopup').style.display='none';
}

function popupTaskForm () {
    document.getElementById('formPopup').style.display='block';
}

function changeTaskDistribution(event, ui) {
    $("#task_distribution").attr('value',ui.value);
}
/*
function editTask(t_what, t_due_date, t_due_hour, t_due_minute, t_due_am_or_pm, t_estimated_effort, t_task_distribution, t_slider) {
    form.what.value = t_what;
    form.due_date.value = t_due_date;
    form.due_hour.value = t_due_hour;
    form.due_minute.value = t_due_minute;
    form.due_am_or_pm.value = t_due_am_or_pm;    
    form.estimated_effort.value = t_estimated_effort;
    form.task_distribution = t_task_distribution;
    $("#slider" ).slider("value",t_slider);

    popupTaskForm();
}
*/
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