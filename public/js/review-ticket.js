 /**
 * Created by Матюхин_МП on 12.01.2016.
 */

$(function () {

    $('.choice').click(function (e) {
        e.preventDefault();
        var self = $(this);
        $('.choice').not(self).removeClass('active');

        self.toggleClass('active').blur();
        $('#result').val(self.filter('.active').data('agree') || 0);
        $('.reason').prop('disabled',  $('#result').val() <= 1).focus();
        $('#save-btn').prop('disabled', $('.choice.active').length === 0);
    });

    $('#save-btn').click(function (e) {
        e.preventDefault();
        $('#status-footer').hide();
        $.post('/ticket/confirmation/', $('#review').serialize(),
        function(data) {
            data.length ? showPopup(data) : window.location = '/';
        });
    });

    $('.reason').prop('disabled',  $('#result').val() <= 1);
    $('#save-btn').prop('disabled', 1);
});