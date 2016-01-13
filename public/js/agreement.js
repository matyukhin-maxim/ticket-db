/**
 * Created by Матюхин_МП on 12.01.2016.
 */

$(function () {

    $('.choice').click(function (e) {
        e.preventDefault();
        var self = $(this);
        $('.choice').not(self).removeClass('active');

        self.toggleClass('active').blur();
        $('#result').val(self.filter('.active').data('agree'));
        $('.reason').prop('disabled', $('#result').val() !== "0").focus();
    });

    $('#save-btn').click(function (e) {
        e.preventDefault();
        $('#status-footer').hide();
        $.post('/ticket/agreement/', $('#agreement').serialize(),
        function(data) {
            data.length ? showPopup(data) : window.location = '/';
        });
    });

    $('.reason').prop('disabled',  $('#result').val() !== "0").focus();
});