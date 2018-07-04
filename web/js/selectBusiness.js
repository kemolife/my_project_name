document.addEventListener("DOMContentLoaded", function(event) {
    var selectBusiness = document.getElementById('business');

    if (selectBusiness) {
        selectBusiness.onchange = function (ev) {
            var business = ev.target.value;
            location.replace('?business=' + business);
        }
    }
});