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

    var selectTheme =  $('#available_themes');

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

$('#available_themes').on('change', function(el) {
    $('.signature-option').addClass('hidden');
    $('#signature-'+$(this).val()).removeClass('hidden');
});

$('.collapse-group .toggle').on('click', function(e) {
    e.preventDefault();
    var text = $(this).data('toggle-text');
    if (text) {
        $(this).data('toggle-text', $(this).text());
        $(this).html(text);
    }
    $(this).closest('.collapse-group').find('.collapse').collapse('toggle');
});

$('.collapse-group .expand').on('click', function(e) {
    e.preventDefault();
    var $this = $(this);
    var $collapse = $this.closest('.collapse-group').find('.collapse');
    $collapse.collapse('toggle');
    $this.remove();
});

$(".loc_time").timepicker({
    showDuration: !0,
    timeFormat: "h:ia"
});

function n(e, t) {
    var n = e.closest(".day"),
        i = $(n).find("#hours_" + t).siblings(".hours");
    i.addClass("hide").end().removeClass("hide"), i.find("input").attr("disabled", "disabled"), $(n).find("#hours_" + t + " input").removeAttr("disabled")
}

function i() {
    var e = $(".Monday").find("#biz-hour-type")[0].value;
    $(".day").each(function () {
        var t = $(this),
            i = t.find("#biz-hour-type")[0],
            r = t.find("#hours_" + e).find(".loc_time");
        if ("closed" !== i.value) {
            i.value = e, n(t, e);
            for (var o = 0; o < r.length; o++) {
                var a = $(".Monday").find("#hours_" + e).find(".loc_time"),
                    s = t.find("#hours_" + e).find(".loc_time");
                s[o].value = a[o].value
            }
        }
    })
}

$(document).on("change", "#biz-hour-type", function () {
    i(), n(this, this.value)
})

$(document).ready(function () {
    $('#singappbundle_businessinfo_additionalCategories')
        .select2({width: '100%'});
    $('#singappbundle_businessinfo_category').select2({width: '100%'});
});

function initAutocomplete() {
    geocoder = new google.maps.Geocoder();
    new google.maps.places.Autocomplete(
        (document.getElementById('singappbundle_businessinfo_address'))/*,
      {types: ['(cities)']}*/);
}
