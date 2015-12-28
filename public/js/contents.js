/**
 * Created by Матюхин_МП on 22.12.2015.
 */

$(function () {

    $('.menu-item').click(function(e) {
        e.preventDefault();

        $('.menu > li').removeClass('active');
        $(this).closest('li').addClass('active');

        $('#ticket-list').html('<tr class="warning"><td colspan="6">Загрузка...</td></tr>');
        $.post('/contents/list/', {type: 0},
        function (data) {
            $('#ticket-list').html(data);
        });
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