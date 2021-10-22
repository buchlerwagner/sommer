function getLables(){
    var _post = $('#frm-dictionary :not(textarea)').serialize();
    $.ajax({
        url:  "/ajax/dictionary/loadcontent/?" + _post
    }).done(function (data) {
        if(typeof data !== 'object'){
            data = JSON.parse(data);
        }
        for (var selector in data) {
            $(selector).html(data[selector]);

            if(selector == 'progressbars'){
                updateProgressBars('progress-orig', data.progressbars.orig.value, data.progressbars.orig.text);
                //updateProgressBars('progress-custom', tmp.progressbars.custom.value, tmp.progressbars.custom.text);
            }
            if(selector == 'totalpages'){
                resetInfiniteScroll('scroll', data.totalpages);
            }

            autosize($('textarea'));
        }
    });
}

function checkDirty(){
    var dirty = false;
    $('#frm-dictionary').each(function(){
        if($(this).find('textarea').hasClass('dirty')){
            dirty = true;
        }
    });

    if(dirty) {
        var _confirmtxt = $('#frm-dictionary').attr('data-confirmtxt');
        return confirm(_confirmtxt);
    }else{
        return true;
    }
}

function updateProgressBars(obj, value, text){
    var $this = $('#' + obj + ' .progress-bar');
    $this.css("width", value + '%');
    $this.attr('aria-valuenow', value);
    $this.html(text);
}

$(function() {
    if(typeof autosize == 'function') {
        autosize($('textarea'));
    }

    $(document).on('change', '#context', function(){
        if(checkDirty()) getLables();
    });

    $(document).on('change', 'textarea', function(){
        var $this = $(this);
        $this.addClass('dirty');
        $this.closest('.card').addClass('dict-mark-changed');
    });

    $(document).on('keyup', 'textarea', function(){
        var $this = $(this);
        $this.closest('.card').addClass('dict-mark-changed');
    });

    $(document).on('focus', 'textarea', function(){
        var $this = $(this);
        var $tools = $this.closest('.card').find('.dict-tools');
        $tools.removeClass('d-none').show();
    });

    $(document).on('blur', 'textarea', function(){
        var $this = $(this);
        if(!$this.hasClass('dirty')){
            var $tools = $this.closest('.card').find('.dict-tools');
            $tools.hide();
        }
    });

    $(document).on('click', '#btn-search', function(){
        if(checkDirty()) getLables();
    });

    $(document).on('click', '.dict-btn-save', function(){
        var $this = $(this);
        var $container = $this.closest('.card');
        if($container.find('textarea').hasClass('dirty')) {

            var _label = $container.attr('data-key');
            var _newvalue = $container.find('textarea').val();
            var _context = $('#context').val();
            var _langfrom = $('#langfrom').val();
            var _langto = $('#langto').val();
            var _updatemode = ($('#set-original').is(':checked') ? 1 : 0);

            _label = _label.trim();
            _newvalue = _newvalue.trim();

            $.ajax({
                type: 'POST',
                url: "/ajax/dictionary/savecontent/",
                data: {
                    label: _label,
                    langfrom: _langfrom,
                    langto: _langto,
                    context: _context,
                    value: encodeURIComponent(_newvalue),
                    updatemode: _updatemode
                }
            }).done(function (data) {
                if(typeof data !== 'object'){
                    data = JSON.parse(data);
                }
                $container.find('.dict-date').html(data.date);
                updateProgressBars('progress-orig', data.progress.orig.value, data.progress.orig.text);
                //updateProgressBars('progress-custom', tmp.progress.custom.value, tmp.progress.custom.text);

                $('#label-info-orig').html(data.progress.orig.info);
                //$('#label-info-custom').html(tmp.progress.custom.info);
            });

            $container.find('textarea').removeClass('dirty');
            $container.removeClass('dict-mark-changed');
            $container.find('.lang-unchanged').html( _newvalue );
        }
        $container.find('.dict-tools').hide();
    });

    $(document).on('click', '.dict-btn-cancel', function(){
        var $this = $(this);
        var $container = $this.closest('.card');
        if($container.find('textarea').hasClass('dirty')){
            var _confirmtxt = $('#frm-dictionary').attr('data-confirmtxt');
            var msg = confirm(_confirmtxt);
            if(msg) {
                $container.find('textarea').val( $container.find('.lang-unchanged').html() );
                $container.find('textarea').removeClass('dirty');
                $container.removeClass('dict-mark-changed');
            }else{
                return false;
            }
        }
        $container.find('.dict-tools').hide();
    });

    $(document).on('click', '.dict-btn-delete', function(e){
        e.stopPropagation();

        var $this = $(this);
        var $container = $this.closest('.card');
        var msg = confirm('Are you sure you want to permanently remove this label?');
        if(msg){
            var _key = $container.attr('data-key');
            var _context = $('#context').val();
            var _langfrom = $('#langfrom').val();
            var _langto = $('#langto').val();

            $.ajax({
                url: "/ajax/dictionary/delete-key/",
                data: {
                    key: _key,
                    langfrom: _langfrom,
                    langto: _langto,
                    context: _context
                }
            }).done(function (data) {
                if(typeof data !== 'object'){
                    data = JSON.parse(data);
                }
                if(data.success == 1) {
                    $container.remove();

                    updateProgressBars('progress-orig', data.progress.orig.value, data.progress.orig.text);
                    //updateProgressBars('progress-custom', tmp.progress.custom.value, tmp.progress.custom.text);

                    $('#label-info-orig').html(data.progress.orig.info);
                    //$('#label-info-custom').html(tmp.progress.custom.info);
                }
            });
        }
    });

    $(document).on('click', '.dict-switch', function(){
        var $this = $(this);
        var _caption_old = $this.html();
        var _caption_new = $this.attr('data-text');

        var $orig = $this.closest('.card').find('.dict-original');
        //var $custom = $this.closest('.card').find('.dict-custom');

        $this.html(_caption_new);
        $this.attr('data-text', _caption_old);

        if($orig.hasClass('d-none')){
            $orig.removeClass('d-none').show();
            //$custom.hide();
        }else{
            $orig.addClass('d-none').hide();
            //$custom.show();
        }
    });

    $(document).on('click', '.btn-select', function(){
        var $this = $(this);
        var _val = $this.attr('data-value');
        var $inp = $this.closest('.btn-group').find('input');

        if($inp.val() != _val && checkDirty()) {
            $inp.val(_val);
            $this.closest('.btn-group').find('.dropdown-toggle').html($this.html());
            getLables();
        }
    });

    $(".inp-search").on('keydown', function(e){
        if(e.which == 13) {
            if(checkDirty()) {
                getLables();
            }
            return false;
        }
    });

    var syncinprogress = false;
    $(document).on('click', '#btn-startsync', function(){
        if(!syncinprogress) {
            var $this = $(this);
            var msg = confirm('Are you sure you want to start the sync process?');

            if (msg) {
                syncinprogress = true;
                $this.attr('disabled', 'disabled').addClass('disabled');
                $('#sync-progress').removeClass('d-none').show();

                $.ajax({
                    url: "/ajax/dictionary/do-sync/?lang=" + $('#langto').val()
                }).done(function (data) {
                    if(typeof data !== 'object'){
                        data = JSON.parse(data);
                    }
                    for (var selector in data) {
                        $(selector).html(data[selector]);
                    }

                    $this.removeAttr('disabled').removeClass('disabled');
                    $('#sync-progress').hide();
                    syncinprogress = false;
                });
            }
        }
    });
});