/**
 * Created by Матюхин_МП on 22.12.2015.
 */

$(function () {

    var last = $.cookie('last-item')  || 0;

    $('.menu-item').click(function(e) {
        var self = $(this);
        var idx = $('.menu-item').index((self));
        $.cookie('last-item', idx, {path: '/', expires: 1});

        e.preventDefault();

        //$('.menu > li').removeClass('active');
        //self.closest('li').addClass('active');
        var type = self.data('type');
        //$('#ticket-list').html($('<tr/>').addClass('warning text-center strong').html('<td colspan="6">Загрузка...</td>'));
        //$('#ticket-list').html('');

        $.post('/contents/list/', {type: type},
            function (data) {
                $('.menu > li').removeClass('active');
                self.closest('li').addClass('active');

                $('#ticket-list').html(data);
                $('[data-toggle="popover"]').popover({html:true});
            });
        askCount();
    });

    function askCount() {
        $.post('/contents/count/', null,
            function (data) {
                $.each(data, function (idx, row) {
                    row.cnt = row.cnt <= 0 ? '' : row.cnt;
                    var item = $('.menu-item[data-type="' + row.id + '"] > .item-cnt');
                    if (item.text() != row.cnt) {
                        item.closest('li').stop().effect('highlight', {color:"#dff0d8"}, 4000);
                        item.text(row.cnt);
                    }
                });

                $('.badge').each(function () {
                    var b = $(this);
                    b.toggleClass('hidden', b.text().length === 0);
                });
            }, 'json');
    }

    setInterval(function() {
        $('.active .menu-item').trigger('click');
    }, 3 * 60 * 1000);
    $('.menu-item').eq(last % $('.menu-item').length).trigger('click');
});