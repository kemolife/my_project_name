var smsReceiversInput = document.getElementById('sms_receiver');
if (smsReceiversInput) {
    smsReceiversInput.type = 'tags';

}

var emailReceiversInput = document.getElementById('email_receiver');
if (emailReceiversInput) {
    emailReceiversInput.type = 'tags';
    [].forEach.call(document.querySelectorAll('input[type="tags"]'), tagsInput);
}

$('.action-schedule').on('click', function (e) {
    $('#modal-schedule').modal();
});

function enablePopovers() {
    $(document).ready(function () {

        $('[rel=popover]').popover({
            html: true,
            placement: function (e, t) {
                var n = $(t).offset(),
                    i = $(window).width();
                return i / n.left > 2 ? "right" : "left"
            },
            content: function () {
                var targetId = $(this).attr('data-target');
                return $(targetId).html();
            }
        });

    });
}

var reviewToggle = function (e) {
    var toggleLink = $(e.target).parent().parent()[0].childNodes[5].getAttribute('href').replace('remove', 'toggle');
    toggleLink = toggleLink.replace('/myreviews/', '/api/reviews/');
    if (toggleLink.indexOf('api') != -1) {
        $.get(toggleLink, function (data) {
            if ($(e.currentTarget).hasClass('action-hide')) {
                $(e.currentTarget).attr('class', 'action-show');
                $(e.target).attr('class', 'halficon-show');
            }
            else {
                $(e.currentTarget).attr('class', 'action-hide');
                $(e.target).attr('class', 'halficon-hide');
            }
        });
    }
};

$('.action-hide').on('click', reviewToggle);
$('.action-show').on('click', reviewToggle);

$('.action-edit').on('click', function (e) {
    var toggleLink = $(e.target).parent().parent()[0].childNodes[5].getAttribute('href').replace('remove', 'edit');
    toggleLink = toggleLink.replace('/myreviews/', 'api/reviews/');

    var reviewId = $(e.target).parent().parent().parent().parent()[0].getAttribute('data-review');
    var reviewContent = $('#review-content-' + reviewId).children().children()[0].textContent;
    var fieldSnippetTextArea = $('#field-snippet')[0].childNodes[3];
    fieldSnippetTextArea.textContent = reviewContent;

    var formInModal = $('#field-snippet').parent().parent().attr('action', toggleLink);

    $('#modal-snippet').modal();
});

$(document).ready(function () {

    var selectTheme = $('#available_themes');

    if (selectTheme) {
        selectTheme.attr('class', 'form-control');
    }

    var tabLinks = $('#tab-pane-links');

    if (tabLinks) {
        tabLinks.addClass('active');
    }

    $('.dropdown-submenu a.test').on("click", function (e) {
        $(this).next('ul').toggle();
        e.stopPropagation();
        e.preventDefault();
    });
});

$('#available_themes').on('change', function (el) {
    $('.signature-option').addClass('hidden');
    $('#signature-' + $(this).val()).removeClass('hidden');
});

$('.collapse-group .toggle').on('click', function (e) {
    e.preventDefault();
    var text = $(this).data('toggle-text');
    if (text) {
        $(this).data('toggle-text', $(this).text());
        $(this).html(text);
    }
    $(this).closest('.collapse-group').find('.collapse').collapse('toggle');
});

$('.collapse-group .expand').on('click', function (e) {
    e.preventDefault();
    var $this = $(this);
    var $collapse = $this.closest('.collapse-group').find('.collapse');
    $collapse.collapse('toggle');
    $this.remove();
});

$(".loc_time").timepicker({
    showDuration: !0,
    timeFormat: "H:i"
});

function n(e, t) {
    var n = e.closest(".day"),
        i = $(n).find("#hours_" + t).siblings(".hours");
    i.addClass("hide").end().removeClass("hide"), i.find("input").attr("disabled", "disabled"), $(n).find("#hours_" + t + " input").removeAttr("disabled")
}

// function i() {
//     var e = $(".Monday").find("#biz-hour-type")[0].value;
//     $(".day").each(function () {
//         var t = $(this),
//             i = t.find("#biz-hour-type")[0],
//             r = t.find("#hours_" + e).find(".loc_time");
//         if ("closed" !== i.value) {
//             i.value = e, n(t, e);
//             for (var o = 0; o < r.length; o++) {
//                 var a = $(".Monday").find("#hours_" + e).find(".loc_time"),
//                     s = t.find("#hours_" + e).find(".loc_time");
//                 s[o].value = a[o].value
//             }
//         }
//     })
// }

function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

$(document).on("change", "#biz-hour-type", function () {
    n(this, this.value)
})

// Statuses
var scanDone = '<i class="fas fa-check-circle synce-done"></i> <span>Found</span>';
var scanFail = '<i class="fas fa-check-times synce-done"></i> <span>Not found</span>';
var scanProgress = '<i class="fas fa-circle-notch fa-spin sync-in-progress"></i> <span>Search in progress...</span>';

$(document).ready(function () {
    $('#singappbundle_businessinfo_additionalCategories')
        .select2({width: '100%'});
    $('#singappbundle_businessinfo_category').select2({width: '100%'});

    var error = getParameterByName('error');
    if (error) {
        $.notify({
            title: "<strong>Error!</strong> ",
            message: error
        }, {
            type: 'danger'
        });
    }

    // Start scan by services
    $(".item-scan").each(function(index, value) {

        // Set params
        var business = $(this).attr("data-business");
        var service = $(this).attr("data-service");
        var status = $(this).find(".btn-status");

        // Set prime status
        status.html(scanProgress);
        
        // Send request
        $.ajax({
            type: "POST",
            url: "/scan/go",
            data: {
                business:   business,
                service:    service
            },
            cache: false,
            responseType: "json",
            success: function(html) {
                
                // Parse JSON format
                var data = $.parseJSON(html);
                
                // Check result
                if(data.status == '1') {
                    
                    status.html(scanDone);
                    
                } else if(data.status == '0') {

                    status.html(scanFail);
                    
                }
                
            }
        });

    });

});
