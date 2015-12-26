/**
 * Created by Матюхин_МП on 22.12.2015.
 */

$(function () {

    $('.menu-item').click(function(e) {
        e.preventDefault();

        $('.menu > li').removeClass('active');
        $(this).closest('li').addClass('active');

        //showPopup($(this).prop('href'), 'alert-info');
        //askCount();
    }).filter(':first').trigger('click');

    function askCount() {
        $.post('/contents/count/', null,
            function (data) {
                $.each(data, function (idx, nval) {
                    var item = $('.item-cnt').eq(idx);
                    nval = nval <= 0 ? '' : nval;
                    if (item.text() != nval) {
                        item.closest('li').stop().effect('highlight', {color:"#dff0d8"}, 4000); //color: "#33e9af"
                        item.text(nval);
                    }
                });
        }, 'json');
    }
    //setInterval(askCount, 10000);
});