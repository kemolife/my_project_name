$(document).ready(function () {


    var getUrlParameter = function getUrlParameter(sParam) {
        var sPageURL = decodeURIComponent(window.location.search.substring(1)),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : sParameterName[1];
            }
        }
    };

    $('.loader').on('click', function() {
        var $this = $(this);
        $this.button('loading');

        var toggle = $('.'+$this.attr('data-toggle'));
        var url = $this.attr('data-url');
        var business = getUrlParameter('business');
        var page = toggle.attr('data-page');
        var params = {'business': business, 'page': page};


        $.get( url, params, function( data ) {
            if (data.items.length > 0) {
                var htmlData = '';
                $.each( data.items, function( key, value ) {
                    htmlData += '<tr> ' +
                        '<td>'+value.icon+'</td> ' +
                        '<td>'+value.date+'</td> ' +
                        '<td class="text-center"> <img class="round" width="40" height="40"  avatar="'+value.author+'"> ' +
                        '<br> <span>'+value.author+'</span> ' +
                        '</td> ' +
                        '<td class="text-center"> <div class="read-rating" data-rating="'+value.rating+'"></div>' +
                        '</td> ' +
                        '<td> <p>'+value.content+'</p>  </td> ' +
                        '<td align="center">'+value.status+'</td>'+
                        '</tr>'
                });
                toggle.append(htmlData);

                $(".read-rating").starRating({
                    starSize: 25,
                    activeColor: "#ffdc59",
                    hoverColor: "#5c5e5c",
                    useGradient: false,
                    readOnly: true,
                    strokeWidth: 0

                });

                LetterAvatar.transform();
                toggle.attr('data-page', parseInt(page) + 1);

                enablePopovers();
            }

            if (data.items.length > 9 || data.items.length === 0){
                $this.css('display', 'none');
            }

            $this.button('reset');
        });
    });
});