/**
 * Created by fellix on 16.01.16.
 */
$(function () {
    $('#btn-login').click(function (e) {
        e.preventDefault();
        var form = $('#login-form');
        $.post('/auth/login/', form.serialize(),
        function(data) {
            if (data.length) {
                form.find('input:first').focus();
                form.trigger('reset');
                showPopup();
            } else window.location = '/';
        });
    });
    $('input').keydown(function (e) {
        if (e.which == 13) $('#btn-login').trigger('click');
    })
});