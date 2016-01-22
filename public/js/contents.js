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

        $('.menu > li').removeClass('active');
        self.closest('li').addClass('active');
        var type = self.data('type');

        $.post('/contents/list/', {type: type},
            function (data) {
                $('#ticket-list').html(data);
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
            }, 'json');
    }

    setInterval(function() {
        $('.active .menu-item').trigger('click');
    }, 5 * 60 * 1000);
    $('.menu-item').eq(last % $('.menu-item').length).trigger('click');
});