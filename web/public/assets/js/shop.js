var mzOptions = {
    upscale: false
};

var shoppingCart = {
    inProgress: false,

    sendData: function(action, data){
        if(!shoppingCart.inProgress) {
            shoppingCart.setProgress(true);

            //console.log(data);

            $.ajax({
                url: '/ajax/cart/' + action + '/',
                type: 'post',
                data: JSON.stringify(data),
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
                success: function (data) {
                    if (data) {
                        processJSONResponse(data);
                        shoppingCart.reinit();
                    }

                    shoppingCart.setProgress(false);
                }
            });
        }
    },
    setProgress: function(inProgress) {
        shoppingCart.inProgress = inProgress;
    },

    viewPopup: function(){
        var $modal = $('#ajax-modal');
        $('#ajax-modal .modal-dialog').addClass('modal-lg modal-dialog-centered');
        $modal.find('.modal-content').load('/ajax/cart/popup/', function () {
            $modal.modal('show');
        });
    },

    addNewHeaderItem: function(data){
        console.log(data);
        $('#header-cart .header-cart-items').append(
            '<figure class="itemside mb-2 border-bottom pb-2 item-' + data.id + '">' +
            '<div class="aside"><img src="/i.php?src=' + data.imgbase64 + '&w=80&h=80&m=1" class="img-sm border"></div>' +
            '<figcaption class="info align-self-center text-nowrap w-100">' +
            '<p class="title">' + data.name + '</p>' +
            '<a href="#" class="float-right action-remove-item" data-id="' + data.id + '"><i class="fa fa-trash"></i></a>' +
            '<div class="price item-price-' + data.id + '">' + data.price.formated + '</div>' +
            '</figcaption>' +
            '</figure>'
        );
    },

    addToCart: function(obj){
        var params = $(obj).serializeJSON();
        shoppingCart.sendData('add', params);
    },

    removeFromCart: function(id){
        shoppingCart.sendData('remove', {id: id});
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

    initControlls: function () {
        $('.action-add-item').on('click', function () {
            var $form = $(this).parents('form');
            shoppingCart.addToCart($form);
        });

        $(document).on('click', '.action-remove-item', function () {
            var id = $(this).data('id');
            shoppingCart.removeFromCart(id);
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
        });

        $('.select-variant').on('change', function () {
            var id = parseInt($(this).val());
            var $item = $('#item-price-' + id);

            $('.item-price').hide();
            $item.removeClass('d-none').show();
            $('#quantity').attr('data-min', $item.data('min')).attr('data-max', $item.data('max')).val($item.data('min'));
            $('#weight').html($item.data('weight'));

            if($item.data('img')){
                var img = document.getElementById("img-" + $item.data('img'));
                MagicZoom.switchTo('zoom', img);
            }else{
                MagicZoom.switchTo('zoom', 0);
            }
        });
    },

    reinit: function(){

    },

    init: function () {
        shoppingCart.initControlls();
    }
};

$(function() {
    if(typeof autosize == 'function') {
        autosize($('textarea'));
    }
    shoppingCart.init();
});
