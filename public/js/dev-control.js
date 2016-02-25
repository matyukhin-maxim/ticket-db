/**
 * Created by Матюхин_МП on 24.02.2016.
 */
$(function () {
    var nodeid = -1;

    // установка обработчика удаления
    function bindDelete() {
        $('.list-group-item > .close').unbind('click').click(function (e) {
            e.preventDefault();
            var btn = $(this);
            var devid = btn.data('id');
            $.ajax({
                type: 'POST',
                url : 'deleteDevice/',
                data: {device_id: devid},
                success: function(data) {
                    if (data === 'OK') {
                        btn.closest('.list-group-item').remove();
                        $('#dev-cnt').text($('.list-group-item').length);
                    }
                },
                complete: function () { showPopup(); }
            });
        });
    }

    $('#s-node').change(function () {
        nodeid = $('#s-node').val();
        $('#del-node').prop('disabled', nodeid <= 0);
        if (nodeid <= 0) {
            $('#dev-list').text('');
            return;
        }

        $.ajax({
            type: 'POST',
            url : 'showList/',
            data: {node: nodeid},
            success: function (data) {
                $('#dev-list').html(data);
                $('#dev-cnt').text($('.list-group-item').length);

                bindDelete();
                showPopup();
            }
        });
    });

    $('#newGear').click(function (e) {
        e.preventDefault();
        var dev = $('#device');

        if (nodeid <= 0) {
            showPopup('Необходимо выбрать редактируемый узел.', 'alert-warning');
            return false;
        }
        if ($.trim(dev.val()).length === 0) {
            showPopup('Название устройства не может быть пустым');
            return false;
        }

        $.ajax({
            type: 'POST',
            url : 'newDevice/',
            data: {node: nodeid, name: dev.val()},
            success: function(data) {
                if (data.length) {
                    // Добавляем div с механизмом. Скроллим вниз, и удаляем alert (об отсутствии механизмов) если он есть
                    $('#dev-list').append(data).animate({scrollTop: $('#dev-list')[0].scrollHeight}, 1000).find('.alert').remove();
                    dev.val('').focus();
                    bindDelete();
                }
                showPopup();
            }
        });
    });

    $('#newNode').click(function (e) {
        e.preventDefault();
        var node = $('#node');

        $.ajax({
            type: 'POST',
            url : 'newNode/',
            data: {node: node.val()},
            success: function(data) {
                if (data) {
                    var opt = $(data);
                    $('#s-node').append(data).selectpicker('refresh').selectpicker('val', opt.val()).trigger('change');
                    node.val(''); // Очищаем поле
                }
                showPopup();
            }
        });
    });

    $('#del-node').click(function (e) {
        e.preventDefault();

        console.info(nodeid);

        $.ajax({
            type: 'POST',
            url : 'deleteNode/',
            data: {node: nodeid},
            success: function(data) {
                showPopup();
                if (data) location.reload();
            }
        });
    });
});