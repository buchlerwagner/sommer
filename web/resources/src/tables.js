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
            method: "GET",
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
                $(selector).replaceWith(data[selector]);
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
    },

    reInit: function(){

    },

    init: function(){
        this.initControls();
        this.reInit();
    }
};

$(function() {
    tables.init();
});
