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
        tmr = setTimeout(function() {
            box.addClass(tclass);
            box.html(text);
            box.slideDown('slow').delay(5000).slideUp(function () {
                box.removeClass(tclass);
            });
        }, 1000);
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
        //minDate: current
    });

    // блок с информаций об отделе
    $('#asu-info').click(function() {
        $('#info-block').slideToggle('slow');
    });

    // mysql ошибки или другие
    $('#status-text:empty').closest('#status-footer').hide();

    $('.selectpicker').selectpicker({
        dropupAuto : false
    });

    // подсказки для кнопок, текст которых может свернуться на мелких экранах
    $('.btn-group-justified .btn').each(function () {
        var self = $(this);
        self.attr('title', $.trim(self.text()));
    });

    $('[data-toggle="popover"]').popover();

    showPopup();

});
