/**
 * Created by Матюхин_МП on 23.12.2015.
 */

$(function () {

    function updateDevs() {
        $('#dev-cnt').text(0);
        $('.btn-check').click(function(e) {
            e.preventDefault();
            var btn = $(this);
            var box = btn.parent().find('.dev-check');
            var chk = !btn.data('checked');

            btn.data('checked', chk).blur();
            box.prop('checked', chk);
            btn.find('i').toggleClass('glyphicon-ok', chk).html(chk ? '' : '&nbsp;');

            $('#dev-cnt').text($('.dev-check:checked').length);
        });
        $('.btn-check').each(function() {
            var btn = $(this);
            var box = btn.parent().find('.dev-check');
            var chk = btn.data('checked');

            box.prop('checked', chk);
            btn.find('i').toggleClass('glyphicon-ok', !!chk).html(chk ? '' : '&nbsp;');
        });
        $('#dev-cnt').text($('.dev-check:checked').length);
    }

    if ($('.date').length) {
        $(document).click(function (e) {
            var ctrl = $(e.target).closest('.date');
            if (!ctrl.length) {
                $('.input-group.date').each(function () {
                    $(this).data('DateTimePicker').hide();
                });
            }
        });
    }

    $('button.btn-save').click(function(e) {
        e.preventDefault();
        var btn = $(e.target);

        // validation
        $('#ticket-message').closest('.form-group').toggleClass('has-error', $('#ticket-message').val() === '');
        $('.tpicker').each(function() {
            var item = $(this);
            item.parent().toggleClass('has-error', item.find('input').val() === '');
        });

        $('#t_node').closest('.form-group').toggleClass('has-error', $('#t_node').val() === '');

        // если количесво групп с ошибкой > 0, то покажем сообщение, и форму отправлять не будем
        if ($('.has-error').length > 0) {
            showPopup('Заполните все необходимые поля', 'alert-warning');
            return;
        }

        // В скрытое поле запоминаем какая из кнопок была нажата (сохранение / отправка)
        $('#confirm').val(btn.data('confirm'));

        // И отправляем форму на проверку-сохранение
        $.post('/ticket/save/', $('#ticket').serialize(),
        function(data) {
            location.href = '/contents/';
            //$('#response').html(data);
        });
    });

    $('#t_node').change(function() {
        $(this).parent().removeClass('has-error');
        $.post('/ticket/devices/', {node: $(this).val()},
        function(data) {
            $('#devices').html(data);
            updateDevs();
        });
    });


    $('.input-group.date').each(function() {
        $(this).data("DateTimePicker").minDate(moment($('#dcurrent').val(), 'DD.MM.YYYY'));
    });


    updateDevs(true);
});