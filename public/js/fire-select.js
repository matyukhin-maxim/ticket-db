/**
 * Created by Матюхин_МП on 08.02.2016.
 */
$(function () {

    $('.selectpicker').on('changed.bs.select', function() {

        // В кнопку открытия спрячем id диспетчера ПЧ
        $('#action-btn').data('fire', $(this).val());
    });

    $('#action-btn').click(function () {
        // а перед кликом по ней, дополним адрес ссылки
        var a = $(this);
        if (a.data('fire').length) {
            a.attr('href', this.href + a.data('fire') + '/');
        }
    })
});