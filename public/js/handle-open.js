/**
 * Created by Матюхин_МП on 18.01.2016.
 */

$(function () {

    $('#btn-close').click(function (e) {
        e.preventDefault();
        $.post('/ticket/complete/', {ticket: $('#tid').val()},
        function(data) {
            data.length ? showPopup(data) : window.location = '/';
        });
    });
});