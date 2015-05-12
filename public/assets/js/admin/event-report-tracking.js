/**
 * Created by albertovieira on 5/4/15.
 */

function getUrlRegisters() {
    return url_getRegiter = "/admin/event-report-tracking/" + _eventID + "/get-data";
}

$(".sidebar-menu").find("li.active").removeClass("active");
$(".courses_events-menu").addClass("active");
$(".events-link").addClass("active");
$(".courses-link").addClass("active");

// Set selection option
$("#select-event").val(_eventID);
$("#select-event").change(function (e) {
    _loader.show();
    window.location.href = "/admin/event-report-tracking/" + $(this).val();
});