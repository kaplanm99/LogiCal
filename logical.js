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
});