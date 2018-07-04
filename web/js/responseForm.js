document.addEventListener("DOMContentLoaded", function(event) {
    var responseForm = document.getElementById('response-form');

    if(responseForm) {
        responseForm.onsubmit = function (ev) {
            ev.preventDefault();

            var response = document.getElementById('response');
            var selectBusiness = document.getElementById('business');
            var review = document.getElementById('review');

            var http = new XMLHttpRequest();
            var url = "/api/response";
            var params = "response="+response.value+"&business="+selectBusiness.value+"&review="+review.value;
            http.open("POST", url, true);

            http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

            http.onreadystatechange = function() {//Call a function when the state changes.
                if(http.readyState == 4 && http.status == 200) {
                    var reviewResponse = document.getElementsByClassName('review-response-'+review.value);
                    console.log(reviewResponse);
                    reviewResponse[0].innerHTML = '<div class="well"><p>'+response.value+'</p></div>'
                }
            };

            http.send(params);
        }
    }
});