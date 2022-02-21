var tables = {
    inProgress: false,

    setProgress: function(on){
        tables.inProgress = on;
    },

    sendRequest: function(table, keyValues, action, params){
        if(keyValues === '' || !keyValues) keyValues = 0;
        var alias = false;
        var options = false;
        var $table = $('#table_' + table);

        tables.setProgress(true);

        if($table.length > 0){
            alias = $table.data('alias');
            table = $table.data('table');
            options = $table.data('options');
        }

        var url = table + '/' + keyValues + '/';
        if (arguments.length > 4) {
            for(var i = 4; i < arguments.length; i++) {
                url += arguments[i] + '/';
            }
        }

        $.ajax({
            method: "POST",
            url: '/ajax/tables/' + url,
            data: {
                alias: alias,
                action: action,
                params: params,
                options: options
            }
        }).done(function (data) {
            if(typeof data !== 'object'){
                data = JSON.parse(data);
            }

            for (var selector in data) {
                if(selector.includes('tbody')) {
                    $table.find('tbody').remove();
                    $table.append(data[selector]);
                }else if(selector === 'fields') {
                    processJSONResponse(data[selector]);
                }else {
                    $(selector).replaceWith(data[selector]);
                }
            }

            app.reInit();
            tables.reInit();

            tables.setProgress(false);
        });
    },

    checkBox: function(table, keyValues, field, value, method){
        if(method !== 'mark'){
            method = 'check';
        }
        tables.sendRequest(table, keyValues, method, {'field': field, 'value': value});
    },

    page: function(table, keyValues, page){
        tables.sendRequest(table, keyValues, 'page', {'page': page});
    },

    delete: function(table, keyValues){
        $('#confirm-delete').modal('hide');
        tables.sendRequest(table, keyValues, 'delete');
    },

    unDelete: function(table, keyValues){
        $('#confirm-delete').modal('hide');
        tables.sendRequest(table, keyValues, 'undelete');
    },

    copy: function(table, keyValues){
        $('#confirm-delete').modal('hide');
        tables.sendRequest(table, keyValues, 'copy');
    },

    action: function(table, keyValues, action, params){
        $('#confirm-delete').modal('hide');
        tables.sendRequest(table, keyValues, action, params);
    },

    reloadTable: function(params){
        tables.reload(params[0], params[1], params[2]);
    },

    selectRow: function(table, keyValues){
        var ids = {};
        var checked = 0;
        var id = 0;

        $('#table_' + table).find('.table-row-selector').each(function(index, obj){
            id = parseInt($(obj).val());
            if($(obj).is(':checked')){
                checked = 1;
            }else{
                checked = 0;
            }

            ids[id] = checked;
        });

        tables.sendRequest(table, keyValues, 'select-row', {ids: ids});
    },

    reload: function(table, keyValues, closeModal){
        closeModal = typeof closeModal !== 'undefined' ? closeModal : true;

        if(closeModal) {
            $('#ajax-modal').modal('hide');
        }

        tables.sendRequest(table, keyValues, 'reload');
    },

    initControls: function(){
        $(document).on('click', '.tr-clickable', function(e){
            if($(this).data('modal')){
                var $modal = $('#ajax-modal');
                $modal.find('.modal-dialog').addClass('modal-' + $(this).data('size'));
                $modal.find('.modal-content').load($(this).data('href'));
                $modal.modal('show');
            }else if($(this).data('url')){
                if($(this).data('target') === 'self'){
                    document.location = $(this).data('url');
                }else {
                    window.open($(this).data('url'));
                }
            }else{
                var $modal = $("a[data-toggle='modal'] i");
                if (!$(e.target).is($modal)) {
                    window.location.href += $(this).data('edit');
                }
            }
        });

        $('.td-clickable').on('click', function (e) {
            var $this = $(this).parent('tr');
            var page = $this.data('url');
            document.location = page;
        });

        $(document).on('click', '.btn-table-pager', function(e){
            var $this = $(this);
            var table = $this.parents('.pagination').data('table');
            var keyValues = $this.parents('.pagination').data('keyvalues');
            var page = $this.data('page');

            if(!$this.hasClass('disabled') && !$this.hasClass('active')){
                tables.page(table, keyValues, page);
            }
        });

        $(document).on('click', '.table-options, .table-check', function(e){
            e.stopImmediatePropagation();
        });

        $(document).on('click', '.table-check input[type=checkbox]', function(e){
            var $this = $(this);
            e.stopImmediatePropagation();

            var checked = ($this.is(':checked')) ? 1 : 0;
            tables.checkBox($this.data('table'), $this.data('keyvalue'), $this.data('field'), checked, $this.data('method'));
        });

        $(document).on('click', '.table-row-selector', function () {
            var $this = $(this);
            var name = $this.parents('table').data('table');
            var keyValues = $this.parents('table').data('foreignkey');
            var checked = $this.is(':checked');

            if(checked) {
                $this.parents('tr').addClass('tr-selected');
            }else{
                $this.parents('tr').removeClass('tr-selected');
            }

            tables.selectRow(name, keyValues);
        });

        $(document).on('click', '.table-row-selector-all', function (e) {
            var $this = $(this);
            var name = $this.parents('table').data('table');
            var keyValues = $this.parents('table').data('foreignkey');
            var checked = $this.is(':checked');

            if(checked) {
                $this.parents('table').find('.table-row-selector').prop('checked', checked).parents('tr').addClass('tr-selected');
            }else{
                $this.parents('table').find('.table-row-selector').prop('checked', checked).parents('tr').removeClass('tr-selected');
            }

            tables.selectRow(name, keyValues);
        });

        $(document).on('click', '.table-row-selector-menu-all', function (e) {
            $('.table-row-selector-all').trigger('click');
        });

        $(document).on('click', '.table-row-selector-menu-none', function (e) {
            var $this = $(this);
            var name = $this.parents('table').data('table');
            var keyValues = $this.parents('table').data('foreignkey');
            $this.parents('table').find('.table-row-selector').prop('checked', false).parents('tr').removeClass('tr-selected');
            $('.table-row-selector-all').prop('checked', false);

            tables.sendRequest(name, keyValues, 'unselect-row');
        });

    },

    reInit: function(){
        $('.table-sort').sortable({
            items : 'tr:not(.no-sort)',
            placeholder: 'table-sort-placeholder',
            stop: function(e, ui){
                var table = $(ui.item).data('table');
                var keyValue = $(ui.item).data('keyvalue');
                var groupId = $(ui.item).data('groupid') || 0;
                var itemOrder = $(e.target).sortable("toArray");

                tables.sendRequest(table, keyValue, 'sort', {
                    groupId: groupId,
                    order: itemOrder
                });
            }
        }).disableSelection();

        if ($('.table-row-selector:checked').length === $('.table-row-selector').length) {
            $('.table-row-selector-all').prop('checked', true);
        }else{
            $('.table-row-selector-all').prop('checked', false);
        }
    },

    init: function(){
        this.initControls();
        this.reInit();
    }
};

$(function() {
    tables.init();

    $(document).on('click', '#btnAddInterval', function(e){
        var $this = $(this);

        var params = {
            smid: parseInt($this.data('smid')),
            start: $('#si_time_start').val(),
            end: $('#si_time_end').val(),
        };

        if(params.smid && params.start && params.end) {
            addInterval(params);
        }
    });
});

function addInterval(params) {
    $.ajax(
        '/ajax/intervals/add/',
        {
            data: params
        }
    ).done(function (res) {
        if(res) {
            tables.reload('shippingIntervals', params.smid, false);
        }
        $('#si_time_start').val('');
        $('#si_time_end').val('');
    });
}

function removeInterval(id, smid) {
    $.ajax({
        method: "GET",
        url: "/ajax/intervals/remove/",
        data: {
            smid: smid,
            id: id
        }
    }).done(function (res) {
        $('#confirm-delete').modal('hide');
        tables.reload('shippingIntervals', smid, false);
    });
}
