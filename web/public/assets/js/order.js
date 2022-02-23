var orders = {
    inProgress: false,

    sendRequest: function(action, params, callback){
        orders.setProgress(true);

        if (arguments.length > 2) {
            for(var i = 2; i < arguments.length; i++) {
                action += '/' + arguments[i];
            }
        }

        $.ajax({
            method: "GET",
            url: '/ajax/orders/' + action + '/',
            data: params
        }).done(function (data) {
            if(typeof data !== 'object'){
                data = JSON.parse(data);
            }

            processJSONResponse(data);

            if(callback){
                var fn = window[callback];
                if(typeof fn === 'function' ) {
                    fn();
                }
            }

            orders.setProgress(false);
            orders.reInit();
        });
    },

    setProgress: function (on) {
        orders.inProgress = on;
    },

    showVariants: function (params){
        $.ajax({
            url: '/ajax/forms/showVariants/' + parseInt(params.productId) + '|' + parseInt(params.cartId) + '/' + params.tableName + '/',
            processData: false,
            contentType: false,
            success: function (data) {
                $('#ajax-modal .modal-content').html(data);
                $('#ajax-modal').modal('show');
            }
        });
    },

    refreshTable: function (params){
        tables.reload(params.tableName, params.cartId);
    },

    removeProduct: function (id){
        $('#confirm-delete').modal('hide');

        orders.sendRequest('removeProduct', {
            id: id,
            cartId: parseInt($('#cartId').val()),
            cartKey: $('#cartKey').val()
        });
    },

    loadShippingMode: function(){
        var id = parseInt($('input[name="shippingMode"]:checked').val());
        if(id){
            orders.sendRequest('getShippingDetails', {
                id: id,
                cartKey: $('#cartKey').val()
            }, 'setupShippingDetailsForm');
        }
    },

    saveShippingMode: function (){
        var id = parseInt($('input[name="shippingMode"]:checked').val());
        if(id) {
            let intervalId = parseInt($('#intervalId').val());
            if($('#customInterval').is(':checked')){
                intervalId = -1;
            }

            orders.sendRequest('setShippingMode', {
                id: id,
                date: $('#shippingDate').val(),
                intervalId: intervalId,
                customInterval: $('#custom-interval-text').val(),
                cartId: parseInt($('#cartId').val()),
                cartKey: $('#cartKey').val()
            });
        }
    },

    initControls: function () {
        $(document).on('click', '[data-toggle="clear"], .clear-inputs', function (){
            $('#finishOrder-form input').val('');
            $('#finishOrder-form select').val('HU');
        });

        $(document).on('click', '.quantity-select', function () {
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
                orders.sendRequest('changeProduct', {
                    id: $this.parents('.input-group').data('id'),
                    quantity: quantity,
                    cartId: parseInt($('#cartId').val()),
                    cartKey: $('#cartKey').val()
                });
            }
        });

        $(document).on('blur', '.quantity-input', function (){
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
                orders.sendRequest('changeProduct', {
                    id: $this.parents('.input-group').data('id'),
                    quantity: quantity,
                    cartId: parseInt($('#cartId').val()),
                    cartKey: $('#cartKey').val()
                });
            }
        });

        $(document).on('click', '.set-shipping-mode', function () {
            orders.loadShippingMode();
        });

        $(document).on('click', '.set-order-type', function () {
            var isLocalConsumption = 0;
            var ids = [];
            var orderType = parseInt($('.set-order-type:checked').val());

            $('.item-local-consumption').each(function(){
                ids.push($(this).data('id'));
            });

            $('.item-local-consumption').removeAttr('disabled');

            if(orderType === 1){
                $('#shipping-modes').hide();
                $('.item-local-consumption').prop('checked', true);
                isLocalConsumption = 1;
            }else if(orderType === 2) {
                $('#shipping-modes').hide();
                $('.item-local-consumption').prop('checked', false);
            }else{
                $('#shipping-modes').removeClass('d-none').show();
                $('.item-local-consumption').prop('checked', false).attr('disabled', 'disabled');
            }

            orders.sendRequest('setLocalConsumption', {
                localConsumption: isLocalConsumption,
                itemId: ids,
                cartId: parseInt($('#cartId').val()),
                cartKey: $('#cartKey').val()
            });

            orders.sendRequest('setOrderType', {
                type: orderType,
                cartId: parseInt($('#cartId').val()),
                cartKey: $('#cartKey').val()
            });
        });

        $(document).on('click', '.set-payment-mode', function () {
            var id = parseInt($(this).data('id'));
            if(id) {
                orders.sendRequest('setPaymentMode', {
                    id: id,
                    cartId: parseInt($('#cartId').val()),
                    cartKey: $('#cartKey').val()
                });
            }
        });

        $(document).on('click', '.item-local-consumption', function () {
            var id = parseInt($(this).data('id'));

            orders.sendRequest('setLocalConsumption', {
                localConsumption: ($(this).is(':checked') ? 1 : 0),
                itemId: id,
                cartId: parseInt($('#cartId').val()),
                cartKey: $('#cartKey').val()
            });
        });

        $(document).on('change', '.save-shipping-mode', function () {
            orders.saveShippingMode();
        });

        $(document).on('click', '.set-custom-interval', function () {
            var $this = $(this);
            var $parent = $this.parents('#shipping-details');
            var $dateControl = $parent.find('.date-picker');

            var minDate = false;
            var offDates = false;

            if($this.is(':checked')){
                $parent.find('.custom-interval').removeClass('d-none').show();
                $parent.find('.interval-select').hide();

                minDate = 'today';
                offDates = [];
            }else{
                $parent.find('.custom-interval').hide();
                $parent.find('.interval-select').removeClass('d-none').show();
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

            orders.saveShippingMode();
        });

        $(document).on('click', '.copy-invoice-address', function () {
            $('#us_country').val( $('#us_invoice_country').val() );
            $('#us_zip').val( $('#us_invoice_zip').val() );
            $('#us_city').val( $('#us_invoice_city').val() );
            $('#us_address').val( $('#us_invoice_address').val() );
        });

        $(document).on('click', '.copy-shipping-address', function () {
            $('#us_invoice_name').val( $('#us_lastname').val() + ' ' + $('#us_firstname').val() );
            $('#us_invoice_country').val( $('#us_country').val() );
            $('#us_invoice_zip').val( $('#us_zip').val() );
            $('#us_invoice_city').val( $('#us_city').val() );
            $('#us_invoice_address').val( $('#us_address').val() );
        });

        this.reInit();
    },

    reInit: function(){
        if ($.fn.flatpickr) {
            $('.date-picker:not(.inited)').each(function () {
                $(this).addClass('inited');
                var $isIndividual = $(this).parents('.shipping-intervals').find('.set-shipping-interval');
                var minDate = $(this).data('min-date');
                var offDates = $(this).data('off-dates') || [];
                var dayLimits = $(this).data('dow') || [];

                if ($isIndividual.is(':checked')) {
                    minDate = 'today';
                    offDates = [];
                }

                $(this).flatpickr({
                    dateFormat: "Y-m-d",
                    minDate: minDate,
                    disable: offDates,
                    enable: [
                        function (date) {
                            if (dayLimits.length > 0) {
                                if (dayLimits.includes(date.getDay())) {
                                    return true;
                                }
                            } else {
                                return true;
                            }

                            return false;
                        }
                    ],
                    locale: 'hu'
                });
            });
        }
    },

    init: function(){
        this.initControls();
        this.loadShippingMode();
    }
};

$(function() {
    $.fn.modal.Constructor.prototype._enforceFocus = function () { };
    orders.init();


});

function loadUserData(params){
    orders.sendRequest('loadUserData', params);
}

function addProduct(params){
    orders.sendRequest('addProduct', params);
}

function setupShippingDetailsForm(){
    orders.saveShippingMode();
}