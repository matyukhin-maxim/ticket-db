var tmr = 0;
function showPopup(text, tclass) {

    clearTimeout(tmr);
    var text = text || $.cookie('status');
    var tclass = tclass || $.cookie('class') || 'alert-danger';

    $.removeCookie('status', {path: '/'});
    $.removeCookie('class' , {path: '/'});

    var box = $('#status-box');
    if (text) {
        box.stop().slideUp('fast');
        box.find('#status-text').html(text);
        tmr = setTimeout(function() {
            box.addClass(tclass);
            box.slideDown('slow').delay(5000).slideUp(function () {
                box.removeClass(tclass);
            });
        }, 1500);
    }
};

$(function() {

    moment.locale('ru');

    $('.input-group.date > input').prop('readonly', true);
    $('.input-group.date').datetimepicker({
        format: 'DD.MM.YYYY HH:mm',
        ignoreReadonly: true,
        sideBySide: true,
        stepping: 10,
        //useCurrent: false,
        minDate: moment().format('YYYY-MM-DD')
    });

    $('#asu-info').click(function() {
        $('#info-block').slideToggle('slow');
    });

    $('#status-footer:empty').hide();

    $('.selectpicker').selectpicker({
        dropupAuto : false
    });

    showPopup();
});
