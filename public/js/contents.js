/**
 * Created by Матюхин_МП on 22.12.2015.
 */

$(function () {

    $('.menu-item').click(function(e) {
        e.preventDefault();

        $('.menu > li').removeClass('active');
        $(this).closest('li').addClass('active');
        var ttype = $(this).data('type');

        $('#ticket-list').html('<tr class="warning strong"><td colspan="6">Загрузка...</td></tr>');
        $.post('/contents/list/', {type: ttype},
        function (data) {
            $('#ticket-list').html(data);
            $('.ticket').click(function () {
                console.info($(this));
            });
        });
        //showPopup($(this).prop('href'), 'alert-info');
        //askCount();
    }).filter(':first').trigger('click');

    function askCount() {
        $.post('/contents/count/', null,
            function (data) {
                $.each(data, function (idx, row) {
                    row.cnt = row.cnt <= 0 ? '' : row.cnt;
                    //var item = $('#menu-' + row.id).find('.item-cnt');
                    var item = $('.menu-item[data-type="' + row.id + '"] > .item-cnt');
                    if (item.text() != row.cnt) {
                        item.closest('li').stop().effect('highlight', {color:"#dff0d8"}, 4000);
                        item.text(row.cnt);
                    }
                });
            }, 'json');
    }
    //setInterval(askCount, 10000);
});