/* checkPass */
function checkPass() {
    var password = $("#pass").val();
    var confirmPassword = $("#vpass").val();
    if (password != confirmPassword) $("#lblcheckpass").html("Passwords do not match!");
    else $("#lblcheckpass").html("Passwords match.");
}
$(document).ready(function() {
    $("#vpass").keyup(checkPass);
});

/* Tablecloth */
$(document).ready(function() {
    $("table").tablecloth({
        sortable: true
    });
});

/* Link-to-tab */
var url = document.location.toString();
if (url.match('#')) {
    $('.nav-tabs a[href=#' + url.split('#')[1] + ']').tab('show');
}
// Change hash for page-reload
$('.nav-tabs a').on('shown', function(e) {
    window.location.hash = e.target.hash;
});