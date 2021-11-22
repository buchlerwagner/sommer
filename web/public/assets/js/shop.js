var mzOptions = {
    upscale: false
};

var shoppingCart = {
    inProgress: false,

    sendData: function(action, data){
        if(!shoppingCart.inProgress) {
            //shoppingCart.setProgress(true);

            $.ajax({
                url: '/ajax/cart/' + action + '/',
                type: 'post',
                data: JSON.stringify(data),
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
                success: function (data) {
                    if (data) {
                        processJSONResponse(data);
                        shoppingCart.reInit();
                    }

                    shoppingCart.setProgress(false);
                },
            });
        }
    },

    setProgress: function(inProgress) {
        shoppingCart.inProgress = inProgress;
    },

    viewPopup: function(){
        var $modal = $('#modal');
        $('#modal .modal-dialog').addClass('modal-lg modal-dialog-centered');
        $modal.find('.modal-content').load('/ajax/cart/popup/', function () {
            $modal.modal('show');
        });
    },

    addToCart: function(id, variant, quantity){
        shoppingCart.sendData('add', {
            id: id,
            variant: variant,
            quantity: quantity
        });
    },

    removeFromCart: function(id){
        shoppingCart.sendData('remove', {
            id: id
        });
    },

    setQuantity: function(value, min, max){
        var quantity = parseInt($('#quantity').val());
        quantity += value;
        $('#quantity').val(quantity);

        if(quantity > min){
            $('#button-minus').removeAttr('disabled');
        }else{
            $('#button-minus').attr('disabled', 'disabled');
        }

        if(quantity <= max){
            $('#button-plus').removeAttr('disabled');
        }else{
            $('#button-plus').attr('disabled', 'disabled');
        }
    },

    filterProducts: function (){
        $('#product-filter').submit();
    },

    initControls: function () {
        $(document).on('click', '.add-to-cart', function () {
            var $this = $(this);
            var id = parseInt($this.data('id'));

            if(id) {
                var variant = parseInt($this.data('variant'));
                if(!variant){
                    variant = parseInt($('#variant').val());
                }

                var quantity = parseInt($this.data('quantity'));
                if(!quantity){
                    quantity = parseInt($('#quantity').val());
                }

                shoppingCart.addToCart(id, variant, quantity);
            }
        });

        $(document).on('click', '.remove-item', function () {
            var id = parseInt($(this).data('id'));
            if(id) {
                shoppingCart.removeFromCart(id);
            }
        });

        $(document).on('click', '.pager', function () {
            var page = parseInt($(this).data('page'));
            var currentPage = parseInt($('#page').val());
            if(page && page != currentPage) {
                $('#page').val(page);
                shoppingCart.filterProducts();
            }
        });

        $(document).on('keypress', '.filter-search', function (e) {
            if (e.which == 13) {
                shoppingCart.filterProducts();
                return false;
            }
        });

        $(document).on('change', '.sort-products', function () {
            shoppingCart.filterProducts();
        });

        $(document).on('click', '.do-filter', function () {
            $('#page').val(1);
            shoppingCart.filterProducts();
        });

        $(document).on('click', '.set-shipping-mode', function () {
            var id = parseInt($(this).data('id'));
            $('.item-shipping-fee').removeClass('d-none').show();

            if(id) {
                shoppingCart.sendData('setShippingMode', {
                    id: id,
                });
            }
        });

        $(document).on('change', '.set-shipping-mode', function () {
            $('.set-shipping-mode').each(function(i, obj) {
                var $parent = $(obj).parents('.options');

                if($(obj).is(':checked')){
                    $parent.find('select').removeAttr('disabled');
                    $parent.find('input[type=text]').removeAttr('disabled');
                }else{
                    $parent.find('select').attr('disabled', 'disabled');
                    $parent.find('input[type=text]').attr('disabled', 'disabled');
                }
            });
        });

        $(document).on('click', '.set-payment-mode', function () {
            var id = parseInt($(this).data('id'));
            if(id) {
                shoppingCart.sendData('setPaymentMode', {
                    id: id,
                });
            }
        });

        $(document).on('change', '.change-state', function () {
            var $this = $(this);
            var options = $this.data('stateOptions');
            var value, found = false;

            if(options) {
                if (this.type && this.type === 'checkbox') {
                    value = ($this.is(':checked') ? 1 : 0);
                } else if (this.type && this.type === 'radio') {
                    value = ($this.is(':checked') ? $this.val() : 0);
                } else {
                    value = $this.val();
                }

                /*
                if(typeof options !== 'object'){
                    options = JSON.parse(options);
                }
                */

                $.each(options, function (val, opt) {
                    if (val == value) {
                        found = true;
                        $.each(opt, function (action, elements) {
                            if (action === 'show') {
                                $(elements).removeClass('d-none').show();
                            } else if (action === 'hide') {
                                $(elements).hide();
                            } else if (action === 'disable') {
                                $(elements).attr('disabled', 'disabled');
                            } else if (action === 'enable') {
                                $(elements).removeAttr('disabled');
                            } else if (action === 'readonly') {
                                $(elements).attr('readonly', 'readonly');
                            } else if (action === 'editable') {
                                $(elements).removeAttr('readonly');
                            } else if (action === 'value') {
                                $(elements.el).val(elements.val).trigger('change');
                            }
                        });
                    }
                });

                if (!found) {
                    var def = $this.data('stateDefault');
                    if (def) {
                        $.each(def, function (action, elements) {
                            if (action === 'show') {
                                $(elements).removeClass('d-none').show();
                            } else if (action === 'hide') {
                                $(elements).hide();
                            } else if (action === 'disable') {
                                $(elements).attr('disabled', 'disabled');
                            } else if (action === 'enable') {
                                $(elements).removeAttr('disabled');
                            } else if (action === 'readonly') {
                                $(elements).attr('readonly', 'readonly');
                            } else if (action === 'editable') {
                                $(elements).removeAttr('readonly');
                            } else if (action === 'value') {
                                $(elements.el).val(elements.val);
                            }
                        });
                    }
                }
            }
        });

        $('.quantity-select').on('click', function () {
            var $this = $(this);
            var min = parseInt($this.parents('.input-group').find('input').attr('data-min') || 1);
            var max = parseInt($this.parents('.input-group').find('input').attr('data-max') || 99999);
            if(max === 0){
                max = 99999;
            }
            var value = parseInt($this.data('value'));

            var $control = $this.parents('.input-group').find('input');
            var quantity = parseInt($control.val());
            quantity += value;

            if(quantity > max){
                quantity = max;
            }
            if(quantity < min){
                quantity = min;
            }

            $control.val(quantity);

            if(quantity > min){
                $this.parents('.input-group').find('.btn-minus').removeAttr('disabled');
            }else{
                $this.parents('.input-group').find('.btn-minus').attr('disabled', 'disabled');
            }

            if(quantity < max){
                $this.parents('.input-group').find('.btn-plus').removeAttr('disabled');
            }else{
                $this.parents('.input-group').find('.btn-plus').attr('disabled', 'disabled');
            }

            if($this.parents('.input-group').data('id')){
                shoppingCart.sendData('change', {
                    id: $this.parents('.input-group').data('id'),
                    quantity: quantity,
                });
            }
        });

        $('.quantity-input').on('blur', function (){
            var $this = $(this);
            var quantity = parseInt($this.val());
            var min = parseInt($this.parents('.input-group').find('input').attr('data-min') || 1);
            var max = parseInt($this.parents('.input-group').find('input').attr('data-max') || 99999);
            if(max === 0){
                max = 99999;
            }

            if(quantity > max){
                quantity = max;
            }
            if(quantity < min){
                quantity = min;
            }

            $this.val(quantity);

            if($this.parents('.input-group').data('id')){
                shoppingCart.sendData('change', {
                    id: $this.parents('.input-group').data('id'),
                    quantity: quantity,
                });
            }
        });

        $('.select-variant').on('change', function () {
            var id = parseInt($(this).val());
            var $item = $('#item-price-' + id);

            $('.item-price').hide();
            $item.removeClass('d-none').show();
            $('#quantity').attr('data-min', $item.data('min')).attr('data-max', $item.data('max')).val($item.data('min'));
            $('#weight').html($item.data('weight'));
            $('#quantity-unit').html($item.data('unit'));

            if($item.data('img')){
                var img = document.getElementById("img-" + $item.data('img'));
                MagicZoom.switchTo('zoom', img);
            }else{
                MagicZoom.switchTo('zoom', 0);
            }
        });

        $('#set-invoice-address').on('change', function (){
            var $this = $(this);
            if($this.is(':checked')){
                $('#company-details').find('input')
                    .removeAttr('data-parsley-excluded')
                    .attr('data-parsley-required', '1')
                    .parsley();

                $('.set-invoice-type').trigger('change');
            }else{
                $('#company-details')
                    .find('input')
                    .attr('data-parsley-excluded', '1')
                    .removeAttr('data-parsley-required')
                    .parsley();

                $('#company-details')
                    .find('.form-group')
                    .removeClass('has-error has-success');
            }
        });

        $('.set-invoice-type').on('change', function (){
            var value = parseInt($('input.set-invoice-type:checked', '#frmOrder').val());
            if(value === 2){
                $('#o-invoice-vat')
                    .removeAttr('data-parsley-excluded')
                    .removeAttr('disabled')
                    .attr('data-parsley-required', '1')
                    .parsley();
            }else{
                $('#o-invoice-vat')
                    .attr('data-parsley-excluded', '1')
                    .attr('disabled', 'disabled')
                    .removeAttr('data-parsley-required')
                    .parsley();
            }
        });

        if($('.set-invoice-type').length){
            $('.set-invoice-type').trigger('change');
        }

        $('.set-shipping-interval').on('change', function (){
            var $this = $(this);
            var $parent = $this.parents('.shipping-intervals');
            var $dateControl = $this.parents('.shipping-intervals').find('.date-picker');

            var minDate = false;
            var offDates = false;

            if(parseInt($this.val()) === -1){
                $parent.find('.custom-interval').removeClass('d-none').show();

                minDate = 'today';
                offDates = [];
            }else{
                $parent.find('.custom-interval').hide();
            }

            if($dateControl.length > 0){
                $dateControl.flatpickr({
                    dateFormat: "Y-m-d",
                    minDate: (minDate ? minDate : $dateControl.data('min-date')),
                    disable: (offDates ? offDates : $dateControl.data('off-dates') || []),
                    locale: 'hu'
                });

                if(!minDate) {
                    $dateControl.val($dateControl.data('min-date'));
                }
            }
        });


        $(document).on('keyup', '.numbersonly', function(){
            var chars = $(this).data('chars');
            var replaceCharFrom = $(this).data('replace-from');
            var replaceCharTo = $(this).data('replace-to');

            if(replaceCharFrom && replaceCharTo){
                this.value = this.value.replace(replaceCharFrom, replaceCharTo);
            }

            if(chars === '' || typeof chars === 'undefined'){
                chars = '\\-.,';
            }
            var pattern = '[^0-9' + chars + ']';
            var re = new RegExp(pattern, 'ig');
            this.value = this.value.replace(re, "");
        });

        $(document).on('blur', '.numbersonly', function(){
            var chars = $(this).data('chars');
            if(chars === '' || typeof chars === 'undefined'){
                chars = '\\-.,';
            }

            var pattern = '[^0-9' + chars + ']';
            var re = new RegExp(pattern, 'ig');
            this.value = this.value.replace(re, "");
        });

        $('#modal').on('show.bs.modal', function (e) {
            var url = '';
            var modal = $(this);
            if(e.relatedTarget) {
                var button = $(e.relatedTarget);
                if (button.data('size')) {
                    $('#modal .modal-dialog').addClass('modal-' + button.data('size'));
                }

                if (button.attr('href') !== '#' && button.attr('href') !== '') {
                    url = button.attr('href');
                } else if (button.data('href')) {
                    url = button.data('href');
                }
            }

            if (url !== '') {
                modal.find('.modal-content').load(url);
            }
        }).on('hidden.bs.modal', function (e) {
            $(e.target).removeData('bs.modal');
            $('#ajax-modal .modal-dialog').removeClass('modal-sm modal-lg modal-xl');
            $('#ajax-modal .modal-content').html('');
        });

        $('.date-picker:not(.inited)').each (function () {
            $(this).addClass('inited');
            var minDate = $(this).data('min-date');
            var offDates = $(this).data('off-dates') || [];

            $(this).flatpickr({
                dateFormat: "Y-m-d",
                minDate: minDate,
                disable: offDates,
                locale: 'hu'
            });
        });

        if ($.fn.parsley) {
            $('.parsley-form:not(.inited)').each (function () {
                $(this).addClass('inited');
                $(this).parsley ({
                    trigger: 'change',
                    errorClass: 'is-invalid',
                    successClass: 'is-valid',
                    errorsWrapper: '<div></div>',
                    errorTemplate: '<label class="error"></label>',
                }).on('field:success', function (ParsleyField) {
                    var $container = ParsleyField.$element.parents('.form-group');
                    $container.removeClass('has-error').addClass('has-success');
                }).on('field:error', function (ParsleyField) {
                    var $container = ParsleyField.$element.parents('.form-group');
                    $container.removeClass('has-success').addClass('has-error');
                });
            });
        }
    },

    reInit: function(){

    },

    init: function () {
        shoppingCart.initControls();
    }
};

function processJSONResponse(data){
    if(typeof data !== 'object'){
        data = JSON.parse(data);
    }

    $.each(data, function(selector, action){
        $.each(action, function(method, value){

            if(typeof window[selector] === 'object') {
                if(typeof window[selector][method] === 'function') {
                    window[selector][method](value);
                }
            }else{
                if(method === 'show'){
                    if(value === true){
                        $(selector).hide().removeClass('d-none').show();
                    }else{
                        $(selector).hide();
                    }
                }else if(method === 'tagsinput'){
                    $.each(value, function(i, avalue) {
                        if(avalue) {
                            $(selector).tagsinput('add', avalue);
                        }
                    });
                }else if(method === 'summernote'){
                    $(selector).summernote('code', value);
                    crm.isdirty = false;
                }else if(method === 'addclass'){
                    $(selector).addClass(value);
                }else if(method === 'removeclass'){
                    $(selector).removeClass(value);
                }else if(method === 'html'){
                    $(selector).html(value);
                }else if(method === 'remove'){
                    $(selector).remove();
                }else if(method === 'append'){
                    $(selector).append(value);
                }else if(method === 'closeModal'){
                    $(selector).modal('hide');
                }else if(method === 'attr'){
                    $.each(value, function(attr, avalue) {
                        if(avalue) {
                            $(selector).attr(attr, avalue);
                        }else{
                            $(selector).removeAttr(attr, '');
                        }
                    });
                }else if(method === 'value'){
                    if($(selector).is(':checkbox') || $(selector).is(':radio')) {
                        $(selector).prop('checked', value);
                    }else {
                        $(selector).val(value);
                    }
                }else if(method === 'options') {
                    $(selector).find('option').remove();
                    $(selector).append(value.map(function (val) {
                        return '<option value="' + val.id + '">' + val.name + '</option>'
                    }));
                }else if(method === 'functions'){
                    if(value.callback) {
                        var fn = window[value.callback];
                        if(typeof fn === 'function' ) {
                            fn(value.arguments);
                        }
                    }
                }else{
                    if(typeof window[method] === 'object') {
                        if(typeof window[method][selector] === 'function') {
                            window[method][selector](value);
                        }
                    }
                }
            }
        });
    });

    return data;
}

$(function() {
    /*
    if(typeof autosize == 'function') {
        autosize($('textarea'));
    }
    */
    shoppingCart.init();
});
;var tables = {
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

    page: function(table, keyValues, page){
        tables.sendRequest(table, keyValues, 'page', {'page': page});
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
    },

    init: function(){
        this.initControls();
    }
};

$(function() {
    tables.init();
});
