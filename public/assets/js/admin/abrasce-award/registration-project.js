/**
 * Created by albertovieiradelima on 2/12/15.
 *
 * Registration JS
 *
 * @author Alberto Vieira de Lima <albertovieiradelima@gmail.com>
 */

// Set menu options
$(".sidebar-menu").find("li.active").removeClass("active");
$(".abrasce-award-menu").addClass("active");
$(".registrations-link").addClass("active");

function imprimirPDF(id){
    window.open("/admin/abrasce-award/registrations/project/"+id+"/1","_blank");
}