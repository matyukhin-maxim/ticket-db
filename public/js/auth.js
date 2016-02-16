/**
 * Created by fellix on 28.01.16.
 */

$(function () {
    var ua = navigator.userAgent;
    var rx = new RegExp('MSIE [0-9]');
    if (!rx.test(ua)) {
        $('label').hide();
    }

    $('#user-field').autocomplete({
        delay: 500,
        minLength: 3,
        autoFocus: true,
        source: function (request, response) {
            $('#btn-login').prop('disabled', true);
            $.post('/auth/complete/', {q: request.term},
                function(data) {
                    response(data);
                }, 'json');
        },
        select: function (ev, ui) {
            $(this).val(ui.item.label);
            $('input[type="password"]').focus();
            $('#user-id').val(ui.item.value);
            $('#btn-login').removeProp('disabled');
            return false;
        },
        focus: function() {
            return false;
        },
        response: function (ev, ui) {
            $(this).parent().toggleClass('has-error', ui.content.length === 0);
            $('#user-id').val('');

            if (ui.content.length === 0) {
                showPopup('Пользователь не найден. Проверьте правильность ввода');
                $(this).val('');
            }
            // автозавершение ввода, если в списке "живого поиска" остался только один вариант
            if (ui.content.length === 1) {
                ui.item = ui.content[0];
                $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                $(this).autocomplete('close');
            }
        }
    }).autocomplete( "instance" )._renderItem = function( ul, item ) {
        return $( "<li>")
            .append("<a>" + item.label + "</a>")
            .append( $('<em/>').addClass('pull-right text-muted').html(item.value) )
            .appendTo( ul );
    };

    $('[type="password"]').keydown(function (e) {
        if (e.which == 13) $('#btn-login').trigger('click');
    });

    $('#btn-login').click(function (e) {
        e.preventDefault();
        if ($('#user-id').val().length === 0) {
            showPopup('Ползователь не указан.');
            $('#login-form').trigger('reset');
            return false;
        }

        $.ajax({
            url: '/auth/login/',
            type: 'post',
            data: $('#login-form').serialize(),
            success: function(data) {
                data.length ? showPopup() : window.location = '/contents/';
            }
        });
    });
});