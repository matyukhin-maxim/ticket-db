/**
 * Created by Матюхин_МП on 23.12.2015.
 */

$(function () {

    $('button.bt-send').click(function(e) {
        e.preventDefault();

        var btn = $(this);
        // validation
        $('#ticket-message').closest('.form-group').toggleClass('has-error', $('#ticket-message').val() === '');
        $('.tpicker').each(function() {
            var item = $(this);
            item.parent().toggleClass('has-error', item.find('input').val() === '');
        });

        $('#t_node').parent().toggleClass('has-error', $('#t_node').val() <= 0);

        // если количесво групп с ошибкой > 0, то покажем сообщение, и форму отправлять не будем
        if ($('.has-error').length > 0) {
            showPopup('Ошибка при заполнении полей формы', 'alert-warning');
            return;
        }

        $.post('/ticket/save/', $('#ticket').serialize(),
        function(data) {
            location.href = btn.data('edit') ? data : '/';
        }, 'json');
    });

    $('#t_node').change(function() {
        $(this).parent().removeClass('has-error');
        $.post('/ticket/devices/', {node: $(this).val()},
        function(data) {
            $('#devices').html(data);
            $('#dev-cnt').text(0);
            $('.btn-check').click(function(e) {
                e.preventDefault();
                var btn = $(this);
                var box = btn.parent().find('.dev-check');
                var chk = !btn.data('checked');

                btn.data('checked', chk).blur();
                box.prop('checked', chk);
                box.parent().find('i').toggleClass('glyphicon-ok', chk).html(chk ? '' : '&nbsp;');

                $('#dev-cnt').text($('.dev-check:checked').length);
            });
        });
    });
});