/*
function updateTasks() {
    $.get("getTasksJSON.php", { },
        function(data) { 
            var tasksParsedJSON = $.parseJSON(data); 

            var output = "";
        
            for (i=0;i<tasksParsedJSON.length;i++) {
                // "need to keep id for mouse click events"
                // "don't need to keep task distribution for display here"
                output = output + "<div style=\"border:1px solid black;\" >Id: " + tasksParsedJSON[i].id + "<br/>What: " + tasksParsedJSON[i].what + "<br/>Due date: " + tasksParsedJSON[i].due_date + "<br/>Due Time: " + tasksParsedJSON[i].due_hour + ":" + tasksParsedJSON[i].due_minute + " " + tasksParsedJSON[i].due_am_or_pm + "<br/>Estimated effort: " + tasksParsedJSON[i].estimated_effort + "<br/>Task distribution: " + tasksParsedJSON[i].task_distribution + "</div>";
            }

            document.getElementById('content_div').innerHTML = output;
    });    
}    

function addToList (form) {
    var t_what = $.trim(form.t_what.value);
    var t_due_date = $.trim(form.t_due_date.value);
    var t_due_hour = $.trim(form.t_due_hour.value);
    var t_due_minute = $.trim(form.t_due_minute.value);
    var t_due_am_or_pm = $.trim(form.t_due_am_or_pm.value);    
    var t_estimated_effort = $.trim(form.t_estimated_effort.value);
    var t_task_distribution = $("#slider").slider("value");
    
    $.post("addTask.php", { what: t_what, due_date: t_due_date, due_hour: t_due_hour, due_minute: t_due_minute, due_am_or_pm: t_due_am_or_pm, estimated_effort: t_estimated_effort, task_distribution: t_task_distribution },
        function(data) {   
        updateTasks();
    });
    
    cancelTaskForm(form);
}
*/

var ajaxAddATaskEventInterval;

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

function ajaxAddATaskEvent() {
    $.get("index.php", { addATaskEvent: 1 },
        function(data) {
            //console.log(data);
            if(data == "done") {
                clearInterval(ajaxAddATaskEventInterval);
            }
    });
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
    
    var result = "";
    
    ajaxAddATaskEventInterval = setInterval(ajaxAddATaskEvent, 1000);        
    
});