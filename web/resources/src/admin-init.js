var app = {
    inProgress: false,

    setProgress: function(on){
        app.inProgress = on;
        if(!on) {
            $('.btn-progress').each(function() {
                var $this = $(this);
                $this.html($this.attr('data-orig-caption')).removeAttr('disabled').removeAttr('data-orig-caption');
            });
            $('#ajax-modal .modal-footer .btn').removeAttr('disabled').removeClass('disabled');
            $('.card-progress').hide();
        }else{
            $('.btn-progress').each(function() {
                var $this = $(this);
                if(!$this.attr('data-orig-caption')) {
                    $this.attr('data-orig-caption', $this.html());
                }

                $this.html('<i class="fas fa-circle-notch fa-spin"></i>');
            });

            $('.card-progress').removeClass('d-none').show();
            $('#ajax-modal .modal-footer .btn').attr('disabled', 'disabled').addClass('disabled');
        }
    },

    reloadTimeline: function (fid){
        var data = {};

        $.ajax({
            url: '/ajax/timeline/reload/?fid=' + fid,
            data: data,
            success : function(data) {
                processJSONResponse(data);
            }
        });
    },

    clearConnectedSelect: function(selector){
        $(selector).prop('disabled', true).find('optgroup, option').remove().html('');

        if ($(selector).data('connected-select') !== undefined) {
            var subclear = $( $(selector).data('connected-select') );
            app.clearConnectedSelect( subclear );
        }
    },

    updateConnectedSelects: function(selector, value){
        $( selector ).each(function(idx, select) {
            var $subselect = $(select);

            app.clearConnectedSelect($subselect);

            if (value !== '0') {
                var data = {};
                if($subselect.data('params')) {
                    data = $subselect.data('params');
                }
                data.id = value;

                var fields = $subselect.data('fields');
                if (fields !== undefined) {
                    $(fields).each(function(){
                        data[$(this).prop('name')] = $(this).val();
                    });
                }

                $.ajax({
                    url: '/ajax/lists/' + $subselect.data('list') + '/',
                    data: data,
                    success : function(data) {
                        if(typeof data !== 'object'){
                            data = JSON.parse(data);
                        }

                        app.fillConnectedSelect($subselect, data);
                    }
                });
            }
        });
    },

    fillConnectedSelect: function(select, data){
        var $select = $(select);
        var defaultValue = $select.data('default-value') || 0;
        var defaultSelectFirst = $select.data('default-select-first') || false;
        var group = false;
        var found = false;
        var selected = false;
        var html = '';
        var optionData = '';

        if ($select.data('empty-option')) {
            html += '<option value="0">' + $select.data('empty-option') + '</option>';
        }

        if(data) {
            $.each(data, function (idx, val) {
                selected = false;

                if (val.groupId !== group && val.groupId) {

                    if (group) {
                        html += '</optgroup>';
                        group = false;
                    }

                    html += '<optgroup label="' + val.groupName + '">';
                    group = val.groupId;
                }

                if(val.id) {
                    if(Array.isArray(defaultValue)){
                        if(defaultValue.indexOf(val.id) !== -1){
                            found = true;
                            selected = true;
                        }
                    }else {
                        if (val.id == defaultValue) {
                            found = true;
                            selected = true;
                        }
                    }

                    if(val.data){
                        optionData = '';
                        $.each(val.data, function (idx, val) {
                            optionData += ' data-' + idx + '="' + val + '"';
                        });
                    }

                    html += '<option value="' + val.id + '"' + (selected ? ' selected="selected"' : '') +  optionData + (val.style ? ' style="' + val.style + '"' : '') + (val.class ? ' class="' + val.class + '"' : '') + '>' + val.text + '</option>';
                }
            });
        }

        if (group) {
            html += '</optgroup>';
        }

        $select.append(html);
        $select.removeAttr('disabled');

        if(!found && defaultSelectFirst) {
            $select.find('option').first().prop('selected', true);
        }

        if($select.hasClass('select-picker')){
            $select.selectpicker('refresh');
        }

        $select.trigger('change');
    },

    initControls: function(){
        $(document).on('click', '.checkbox-highlight input', function () {
            var $this = $(this);
            var $container = $this.parents('.checkbox-highlight');
            var active = $container.data('active') || '';
            var inactive = $container.data('inactive') || '';

            if($this.is(':checked')){
                if(inactive != '') $container.removeClass(inactive);
                if(active != '') $container.addClass(active);
            }else{
                if(inactive != '') $container.addClass(inactive);
                if(active != '') $container.removeClass(active);
            }
        });

        /*
        $(document).on('click', '.btn-progress', function () {
            var $this = $(this);

            if(!$this.attr('data-orig-caption')) {
                $this.attr('data-orig-caption', $this.html());
            }

            $this.html('<i class="fas fa-circle-notch fa-spin"></i>');
        });
        */

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            $('input[name=' + $(e.target).parent().attr('id') + ']').val( $(e.target).attr('href').replace('#', '') );
        });

        $(document).on('click', '.btn-modal-submit', function(){
            var $this = $(this);
            var $modal = $this.parents('.modal-content');
            var $form = $modal.find('form');
            $form.submit();
        });

        $(document).on('keyup', '.alphanumeric-only', function(){
            this.value = this.value.replace(/[^a-z0-9]/ig, "");
        });

        $(document).on('blur', '.alphanumeric-only', function(){
            this.value = this.value.replace(/[^a-z0-9]/ig, "");
        });

        $(document).on('blur', '.time-format', function () {
            var $this = $(this);
            var time = $this.val();
            $this.val(moment(time.toString(), 'LT').format('HH:mm'));
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

        $('.btn-insert-text').on('click', function () {
            var $this = $(this);
            var $editor = $this.parents('fieldset').find('.htmleditor');

            $editor.summernote('editor.saveRange');
            $editor.summernote('editor.restoreRange');
            $editor.summernote('editor.focus');
            $editor.summernote('editor.insertText', $this.html());
        });

        $(document).on('change', '.change-currency-sign', function () {
            var $this = $(this);
            if($this.val() != '0') {
                var text = $this.find('option:selected').text();
                $('.has-currency-sign').parents('.input-group').find('.input-group-text').html(text);
            }
        });

        $(document).on('change', '.change-state-on-change', function () {
            var $this = $(this);
            var enabledFields = $this.data('enable-fields');
            var disableFields = $this.data('disable-fields');
            var enableValue = $this.data('enable-value');
            var disableValue = $this.data('disable-value');

            var readonlyFields = $this.data('readonly-fields');
            var readonlyValue = $this.data('readonly-value');

            var visibleIds = $this.data('visible-ids');
            var visibleValue = $this.data('visible-value');

            if(enabledFields) {
                $(enabledFields).each(function (idx, obj) {
                    if ($this.val() == enableValue) {
                        $(obj).removeAttr('disabled');
                    } else {
                        $(obj).attr('disabled', 'disabled');
                    }
                });
            }

            if(disableFields) {
                $(disableFields).each(function (idx, obj) {
                    if($this.val() == disableValue){
                        $(obj).attr('disabled', 'disabled');
                    }else{
                        $(obj).removeAttr('disabled');
                    }
                });
            }

            if(readonlyFields) {
                $(readonlyFields).each(function (idx, obj) {
                    if($this.val() == readonlyValue){
                        $(obj).removeAttr('readonly');
                    }else{
                        $(obj).attr('readonly', 'readonly');
                    }
                });
            }

            if(visibleIds) {
                $(visibleIds).each(function (idx, obj) {
                    if($this.val() == visibleValue){
                        $(obj).removeClass('d-none').show();
                    }else{
                        $(obj).hide();
                    }
                });
            }
        });

        $(document).on('change', '.change-state', function () {
            var $this = $(this);
            var options = $this.data('stateOptions');
            var value, found = false;

            if(options) {
                if (this.type && (this.type === 'checkbox' || this.type === 'radio')) {
                    value = ($this.is(':checked') ? 1 : 0);
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

        $('[data-toggle="tooltip"]').tooltip();

        $("table.user_level_rights input:checkbox").on("click",
            function(e){
                var $this = $(this);
                var _userGroup = $this.attr('data-usergroup');
                var _role = $this.attr('data-role');
                var _page = $this.attr('data-page');
                var _function = $this.attr('data-function');
                if(!_function){
                    _function = '';
                }

                $.ajax({
                    url: "/ajax/accessrights/",
                    data: "usergroup=" + _userGroup + "&role=" + _role + "&page=" + _page + "&function=" + _function + "&checked=" + (($this.is(":checked")) ? 1 : 0)
                });
            }
        );

        $("table.user_level_rights .btn-accesslevel").on("click",
            function(e){
                var $this = $(this);
                var $button = $this.closest('.btn-group').find('button');
                var _value = $this.attr('data-value');
                var _color = $this.attr('data-color');
                var _icon = $this.attr('data-ico');
                var _userGroup = $this.closest('.btn-group').attr('data-usergroup');
                var _role = $this.closest('.btn-group').attr('data-role');
                var _page = $this.closest('.btn-group').attr('data-page');
                var _current_icon = $button.attr('data-ico');
                var _current_color = $button.attr('data-color');

                $.ajax({
                    url: "/ajax/accessrights/",
                    data: "usergroup=" + _userGroup + "&role=" + _role + "&page=" + _page + "&value=" + _value,
                    success: function(data){
                        $button.toggleClass('btn-' + _color + ' btn-' + _current_color).attr('data-color', _color).attr('data-ico', _icon);
                        $button.find('i').toggleClass('fa-' + _icon + ' fa-' + _current_icon );
                    }
                });
            }
        );

        /*
        if ($.fn.parsley) {
            $('.parsley-form:not(.inited)').each (function () {
                $(this).addClass('inited');
                $(this).parsley ({
                    trigger: 'change',
                    errorClass: '',
                    successClass: '',
                    errorsWrapper: '<div></div>',
                    errorTemplate: '<label class="error"></label>',
                }).on('field:success', function (ParsleyField) {
                    var $container = ParsleyField.$element.parents('.form-group');
                    $container.removeClass('has-error');
                }).on('field:error', function (ParsleyField) {
                    var $container = ParsleyField.$element.parents('.form-group');
                    $container.removeClass('has-success').addClass('has-error');

                    $('.btn-progress').html($('.btn-progress').data('orig-caption'));
                    $('.btn-progress').parents('form').find('.card-progressbar').addClass('d-none');
                });
            });
        }
         */

        $('[data-toggle="accept"]').on('click', function (){
            var $this = $(this);
            if($this.is(':checked')){
                $this.parents('form').find('.btn-accept').removeAttr('disabled');
            }else{
                $this.parents('form').find('.btn-accept').attr('disabled', 'disabled');
            }
        });

        $(".upload-profile-img").on('click', function(e){
            e.preventDefault();
            $("#fileInput:hidden").trigger('click');
        });

        $(".delete-profile-img").on('click', function(e){
            e.preventDefault();
            $.ajax({
                url: "/ajax/fileupload/delete-profile-img/",
                success: function(data) {
                    processJSONResponse(data);
                }
            });
        });

        $('#fileInput').on('change', function(){
            var image = $(this).val();
            var img_ex = /(\.jpg|\.jpeg|\.png|\.gif)$/i;

            if(!img_ex.exec(image)){
                app.showMessage({type: 'warning', message: 'Hibás fájltípus! Engedélyezett fájltípusok: jpg, png, gif'});

                $('#fileInput').val('');
                return false;
            }else{
                $('#img-upload-form').submit();
            }
        });

        $('#img-upload-form').on('submit',(function(e) {
            var $this = $(this);
            e.preventDefault();
            $.ajax({
                url: $this.attr('action'),
                type: "POST",
                data:  new FormData(this),
                contentType: false,
                cache: false,
                processData:false,
                success: function(data){
                    processJSONResponse(data);
                },
                error: function(e){
                    app.showMessage({type: 'error', message: 'Invalid file.'});
                }
            });
        }));

        $('body').on('click', '.fileuploader-action-multiselect', function () {
            var $this = $(this);
            $this.parents('.fileuploader-item-inner').toggleClass('selected');
            $this.parents('.fileuploader-item-inner').find('.fileuploader-item-image img').toggleClass('selected-img');
            $this.find('i').toggleClass('fa-square fa-check-square');
        });

        $(".chk-notification").on("click",
            function(e){
                var $this = $(this);
                var id = parseInt($this.data('id'));
                var type = $this.data('type');
                var uid = parseInt($this.data('uid'));
                var value = ($this.is(':checked') ? 1 : 0);

                $.ajax({
                    url: "/ajax/subscribe-notification/",
                    data: "id=" + id + "&type=" + type + "&uid=" + uid + "&value=" + value,
                    success: function(data){
                    }
                });
            }
        );

        if(jQuery().select2) {
            $('.select2:not(.inited)').addClass('inited').select2();
        }

        if(jQuery().selectpicker) {
            $('.select-picker:not(.inited)').addClass('inited').selectpicker();
        }

        if($('.htmleditor').length > 0){
            $('.htmleditor').summernote({
                height: 300,
                toolbar: [
                    ['edit', ['undo', 'redo']],
                    //['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'fontsize', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['table', 'link', 'gallery', 'video']], // 'picture'
                    ['misc', ['fullscreen', 'codeview']],
                    ['cleaner',['cleaner']]
                ],
                cleaner:{
                    action: 'both',
                    newline: '<br>',
                    notStyle: 'position:hidden;top:0;left:0;right:0',
                    icon: '<i class=\"fa fa-eraser\"></i>',
                    keepHtml: false,
                    keepOnlyTags: ['<p>', '<br>', '<ul>', '<li>', '<b>', '<strong>','<i>', '<a>'],
                    keepClasses: false,
                    badTags: ['style', 'script', 'applet', 'embed', 'noframes', 'noscript', 'html'],
                    badAttributes: ['style', 'start', 'class'],
                    limitChars: false,
                    limitDisplay: 'none',
                    limitStop: false
                },
                callbacks :{
                    onInit: function() {
                        $(this).data('image_dialog_images_url', "/ajax/gallery-content/");
                        $(this).data('image_dialog_title', "");
                    }
                }
            });
        }

        $('.btn-notification').on('click', function (){
            var $this = $(this);
            var url = $this.data('url');
            var id = parseInt($this.data('id'));

            $this.removeClass('note-new');
            $this.parent().addClass('viewed');

            $.ajax({
                url: "/ajax/notifications/viewed/",
                data: "id=" + id,
                success: function(data){
                    processJSONResponse(data);
                    if(url){
                        window.location = url;
                    }
                }
            });
        });

        initInfiniteScroll();

        this.reInit();
    },

    reInit: function(){
        if(jQuery().select2) {
            $('.select2:not(.inited)').addClass('inited').select2();
        }

        if(jQuery().selectpicker) {
            $('.select-picker:not(.inited)').addClass('inited').selectpicker();
        }

        if ($.fn.parsley) {
            $('.parsley-form:not(.inited)').each (function () {
                $(this).addClass('inited');
                $(this).parsley ({
                    trigger: 'change',
                    errorClass: '',
                    successClass: '',
                    errorsWrapper: '<div></div>',
                    errorTemplate: '<label class="error"></label>',
                }).on('field:success', function (ParsleyField) {
                    var $container = ParsleyField.$element.parents('.form-group');
                    $container.removeClass('has-error');
                }).on('field:error', function (ParsleyField) {
                    var $container = ParsleyField.$element.parents('.form-group');
                    $container.removeClass('has-success').addClass('has-error');

                    app.setProgress(false);
                });
            });
        }

        $('input.tagsinput:not(.inited)').each(function(){
            $(this).addClass('inited');
            $(this).tagsinput({
                trimValue: true,
                freeText: true
            });
            $(this).tagsinput('input').attr('data-parsley-ui-enabled', 'false').on('blur', function(){
                if ($(this).val()) {
                    $(this).parent().parent().find('.tagsinput').tagsinput('add', $(this).val());
                    $(this).val('');
                }
            });
        });

        $('select.tagsinput:not(.inited)').each(function(){
            $(this).addClass('inited');
            $(this).tagsinput({
                itemValue: 'id',
                itemText: 'label'
            });
            $(this).find('option').each(function(){
                $(this).parent().tagsinput('add', { "id": $(this).attr('value'), "label": $(this).html() });
            });

            $(this).tagsinput('input').parent().addClass('bootstrap-tagsinput-fullwidth');
            $(this).tagsinput('input')
                .attr('data-list-url', $(this).attr('data-list-url'))
                .attr('data-scope', $(this).attr('data-scope'))
                .addClass('catselautocomplete');
        });

        $('.autocomplete:not(.inited)').each(function() {
            var $this = $(this);
            $this.addClass('inited');

            var _data = {};
            var _changed = false;
            var id = $this.attr('id');
            var addNew = parseInt($this.data('addnew')) || 0;
            var insertFields = $this.data('insertfields') || false;
            var searchFields = $this.data('searchfields') || false;
            var callBackFunction = $this.data('callback') || false;
            var clearOnSelect = $this.data('clearonselect') || false;
            var list = $this.data('list') || '';
            var url = '/ajax/lists/' + list + '/';

            $this.autoComplete({
                resolver: 'custom',
                minLength: 0,
                preventEnter: true,
                events: {
                    search: function (q, callback) {
                        _data = {};
                        _data.q = q;

                        if(searchFields){
                            $(searchFields).each(function(idx, inp){
                                _data[$(inp).attr('id')] = $(inp).val();
                            });
                        }

                        $.ajax(
                            url,
                            {
                                data: _data
                            }
                        ).done(function (res) {
                            if(res.results) {
                                callback(res.results);
                            }
                        });
                    }
                }
            });

            $this.on('focus', function () {
                $this.autoComplete('show');
            });

            $this.on('change', function () {
                _changed = true;
            });

            $this.on('keyup', function (e) {
                if(e.keyCode === 13) {
                    e.preventDefault();
                    _changed = true;
                    $this.trigger('autocomplete.freevalue', [ $this.val() ]);
                }
            });

            $this.on('autocomplete.select', function (evt, item) {
                $('#' + id + '-id').val(item.id);
                _changed = false;

                _data = {};
                _data.id = item.id;
                _data.action = 'count';

                $.ajax(
                    url,
                    {
                        data: _data
                    }
                );

                if(callBackFunction){
                    var fn = window[callBackFunction];
                    if(typeof fn === 'function'){
                        console.log('call function');
                        _data = {};
                        _data.id = item.id;

                        if(insertFields){
                            $(insertFields).each(function(idx, inp){
                                _data[$(inp).attr('id')] = $(inp).val();
                            });
                        }

                        fn.call(null, item);
                    }
                }

                if(clearOnSelect){
                    $this.autoComplete('clear');
                    $('#' + id + '-id').val(0);
                }
            });

            $this.on('autocomplete.freevalue', function (evt, value) {
                if(addNew){
                    if(_changed && value !== '') {
                        _changed = false;
                        _data = {};
                        _data.value = value;
                        _data.action = 'add';
                        if(insertFields){
                            $(insertFields).each(function(idx, inp){
                                _data[$(inp).attr('id')] = $(inp).val();
                            });
                        }

                        $.ajax(
                            url,
                            {
                                data: _data
                            }
                        ).done(function (res) {
                            if(res.id){
                                $('#' + id + '-id').val(res.id);

                                if(callBackFunction){
                                    var fn = window[callBackFunction];
                                    if(typeof fn === 'function'){
                                        _data = {};
                                        _data.id = res.id;

                                        if(insertFields){
                                            $(insertFields).each(function(idx, inp){
                                                _data[$(inp).attr('id')] = $(inp).val();
                                            });
                                        }

                                        fn.call(null, _data);
                                    }
                                }
                            }
                        });
                    }
                }else {
                    $('#' + id + '-id').val(0);
                }

                if(clearOnSelect){
                    $this.autoComplete('clear');
                    $('#' + id + '-id').val(0);
                }
            });
        });

        $('.catselautocomplete:not(.inited)').each(function() {
            var $this  = $(this);
            var _url  = $this.attr('data-list-url');
            var _scope = $this.attr('data-scope') || 0;
            $this.addClass('inited');

            $this.catselcomplete({
                minLength: 2,
                source: function(request, response) {
                    $.ajax({
                        url: _url,
                        dataType: "jsonp",
                        data: {
                            query: request.term,
                            scope: _scope,
                        },
                        success : function(data) {
                            var resp = {};
                            for (var i in data) {
                                var val = data[i];
                                if (val.categoryid !== undefined && resp[val.categoryid] === undefined) {
                                    resp[val.categoryid] = {
                                        value: val.categoryid,
                                        label: val.category,
                                        category: val.category,
                                        categorycode: val.categoryid,
                                        class: 'ui-autocomplete-category'
                                    };
                                }
                                if (val.id !== val.categoryid) {
                                    resp[val.id] = {
                                        value: val.id,
                                        label: val.label,
                                        category: val.category,
                                        categorycode: val.categoryid,
                                        class: 'ui-menu-item'
                                    };
                                }
                            }
                            response(resp);
                        }
                    });
                },
                focus: function( event, ui ) {
                    if ($(this).parent().hasClass('bootstrap-tagsinput')) {
                    } else {
                        $( this ).val( ui.item.label );
                        $('#' + this.id + '-id').val( ui.item.value );
                    }
                    return false;
                },
                select: function(event, ui) {
                    if ($(this).parent().hasClass('bootstrap-tagsinput')) {
                        $(this).parent().next().tagsinput('add', { "id": ui.item.value, "label": ui.item.label });
                        $(this).val('');
                    } else {
                        $(this).val( ui.item.label );
                        $('#' + this.id + '-id').val( ui.item.value );
                    }
                    return false;
                }
            }).focusout(function() {
                if ($(this).parent().hasClass('bootstrap-tagsinput')) {
                } else {
                    if ($(this).val() == '') {
                        $('#' + this.id + '-id').val('');
                    }
                }
            });
        });

        if ($.fn.datepicker) {
            $('.datepicker:not(.inited)').each(function() {

                var $this = $(this);
                $this.addClass('inited');
                var _language = $this.attr('data-language') || 'en';
                var _firstday = parseInt($this.attr('data-firstday') || 1);
                var _dateformat = $this.attr('data-dateformat') || 'yy-mm-dd';
                var _calendars = parseInt($this.attr('data-calendars') || 1);
                var _changeyear = $this.attr('data-change-year') || 'false';
                var _changemonth = $this.attr('data-change-month') || 'true';
                _changeyear = (_changeyear === 'true' ? true : false );
                _changemonth = (_changemonth === 'true' ? true : false );

                if(_changeyear){
                    var _yearrange = $this.attr('data-year-range') || 'c-10:c+10';
                }else{
                    var _yearrange = 'c-10:c+10';
                }

                var _mindate = $this.attr('data-min-date') || '';
                var _maxdate = $this.attr('data-max-date') || '';

                var _open = $this.attr('data-open') || false;
                var _datelimitmin = $this.attr('data-datelimit-min') || false;
                var _datelimitmax = $this.attr('data-datelimit-max') || false;

                var _rangefrom = $this.attr('data-range-from') || false;
                var _rengeto = $this.attr('data-range-to') || false;

                $this.datepicker($.extend({}, $.datepicker.regional[_language],{
                    firstDay: _firstday,
                    dateFormat: _dateformat,
                    numberOfMonths: _calendars,
                    changeMonth: _changemonth,
                    changeYear: _changeyear,
                    yearRange: _yearrange,
                    minDate: _mindate,
                    maxDate: _maxdate,
                    prevText: '',
                    nextText: '',
                    beforeShowDay: function( date ) {
                        if (_rengeto && $('#' + _rengeto)) {
                            var date1 = $.datepicker.parseDate(_dateformat, $(this).val());
                            var date2 = $.datepicker.parseDate(_dateformat, $('#' + _rengeto).val());
                        } else if (_rangefrom && $('#' + _rangefrom)) {
                            var date1 = $.datepicker.parseDate(_dateformat, $('#' + _rangefrom).val());
                            var date2 = $.datepicker.parseDate(_dateformat, $(this).val());
                        }
                        var extra_class = '';
                        if (date1 && date.getTime() === date1.getTime()) {
                            extra_class += ' dp-range-start';
                        }
                        if (date2 && date.getTime() === date2.getTime()) {
                            extra_class += ' dp-range-end';
                        }
                        return [true, date1 && date2 && (date >= date1 && date <= date2) ? "dp-highlight" + extra_class : extra_class];
                    },
                    onClose: function( selectedDate ) {
                        if(_datelimitmin && $('#' + _datelimitmin)) $('#' + _datelimitmin).datepicker( "option", "minDate", selectedDate );
                        if(_datelimitmax && $('#' + _datelimitmax)) $('#' + _datelimitmax).datepicker( "option", "maxDate", selectedDate );
                        if(_open && $('#' + _open) && selectedDate!='') $('#' + _open).datepicker('show');
                    },
                    onSelect: function(dateText, inst) {
                        $(this).trigger('onblur');
                        $(this).trigger('change');
                    }
                }));
            });

            if (!$('body').hasClass('datepicker-inited')) {
                $('body').addClass('datepicker-inited');
                $('body').on(
                    'mouseenter',
                    '#ui-datepicker-div .ui-datepicker-calendar td:not(.ui-datepicker-unselectable,.ui-state-disabled)',
                    function() {
                        if ($('.ui-datepicker-calendar .dp-range-start, .ui-datepicker-calendar .dp-range-end').length > 0) {
                            $(this).addClass('dp-state-hover');
                            var rangefrom = false;
                            var rangeto = false;
                            if ($('.ui-datepicker-calendar .dp-range-start.dp-range-end .ui-state-active').length > 0) {
                                rangeto = true;
                            } else if ($('.ui-datepicker-calendar .dp-range-start .ui-state-active').length > 0) {
                                rangefrom = true;
                            } else if ($('.ui-datepicker-calendar .dp-range-end .ui-state-active').length > 0) {
                                rangeto = true;
                            } else if (!rangefrom && $('.ui-datepicker-calendar .dp-range-start').length > 0) {
                                rangeto = true;
                            } else if (!rangeto && $('.ui-datepicker-calendar .dp-range-end').length > 0) {
                                rangefrom = true;
                            }

                            $('.ui-datepicker-calendar .dp-range-start').addClass('dp-range-start-h').removeClass('dp-range-start');
                            $('.ui-datepicker-calendar .dp-range-end').addClass('dp-range-end-h').removeClass('dp-range-end');
                            $('.ui-datepicker-calendar .dp-highlight').addClass('dp-highlight-h').removeClass('dp-highlight');
                            $('.ui-datepicker-calendar .ui-state-active').addClass('ui-state-active-h').removeClass('ui-state-active');
                            var hoverfound = false;
                            var startfound = false;
                            var endfound = false;
                            var started = false;
                            var ended = false;
                            $('#ui-datepicker-div .ui-datepicker-calendar td:not(.ui-datepicker-unselectable,.ui-state-disabled)').each(function(idx, td){
                                if ($(td).hasClass('dp-state-hover')) {
                                    hoverfound = true;
                                }
                                if ($(td).hasClass('dp-range-start-h')) {
                                    startfound = true;
                                }
                                if (!startfound && $(td).hasClass('dp-highlight-h')) {
                                    startfound = true;
                                    if (rangeto) {
                                        started = true;
                                    } else {
                                        rangefrom = true;
                                    }
                                }
                                if ($(td).hasClass('dp-range-end-h')) {
                                    endfound = true;
                                }
                                if (!started) {
                                    if (rangefrom && hoverfound && !endfound) {
                                        started = true;
                                        $(td).addClass('dp-hover-start');
                                    }
                                    if (rangeto && startfound && !hoverfound) {
                                        started = true;
                                        $(td).addClass('dp-hover-start');
                                    }
                                } else if (!ended) {
                                    if (rangefrom && !endfound) {
                                        $(td).addClass('dp-hover');
                                    } else if (rangeto && !hoverfound) {
                                        $(td).addClass('dp-hover');
                                    } else {
                                        ended = true;
                                        $(td).addClass('dp-hover-end');
                                    }
                                }
                            });
                        }
                    }
                );
                $('body').on(
                    'mouseleave',
                    '#ui-datepicker-div .ui-datepicker-calendar td:not(.ui-datepicker-unselectable,.ui-state-disabled)',
                    function() {
                        $('#ui-datepicker-div .ui-datepicker-calendar td').removeClass('dp-hover dp-state-hover dp-hover-start dp-hover-end');
                        $('.ui-datepicker-calendar .dp-range-start-h').addClass('dp-range-start').removeClass('dp-range-start-h');
                        $('.ui-datepicker-calendar .dp-range-end-h').addClass('dp-range-end').removeClass('dp-range-end-h');
                        $('.ui-datepicker-calendar .dp-highlight-h').addClass('dp-highlight').removeClass('dp-highlight-h');
                        $('.ui-datepicker-calendar .ui-state-active-h').addClass('ui-state-active').removeClass('ui-state-active-h');
                    }
                );
            }
        }

        $('.connected-select:not(.cs-inited)').each(function(){
            $(this).addClass('cs-inited').on('change', function() {
                var $this = $(this);
                app.updateConnectedSelects($this.data('connected-select'), $this.val());
            });

            if ($(this).val() !== '0' && $(this).val() !== null) {
                $(this).trigger('change');
            }
        });

        //$('.selectpicker').selectpicker();
        $('.change-state-on-change').trigger('change');
        $('.change-state').not('.skip-init').trigger('change');

        var $input = $('input.file[type=file]');
        if ($input.length) {
            $input.fileinput();
        }

        $('.img-zoom').zoom();

        if(typeof autosize == 'function') {
            autosize($('textarea.autosize'));
        }
    },

    initModals: function(){
        $('#ajax-modal').on('show.bs.modal', function (e) {
            var url = '';
            var modal = $(this);
            if(e.relatedTarget) {
                var button = $(e.relatedTarget);
                if (button.data('size')) {
                    $('#ajax-modal .modal-dialog').addClass('modal-' + button.data('size'));
                }

                if (button.attr('href') != '#' && button.attr('href') != '') {
                    url = button.attr('href');
                } else if (button.data('href')) {
                    url = button.data('href');
                }
            }

            if (url != '') {
                modal.find('.modal-content').load(url);
            }
        });

        $('#ajax-modal').on('hidden.bs.modal', function (e) {
            $(e.target).removeData('bs.modal');
            $('#ajax-modal .modal-dialog').removeClass('modal-sm modal-lg modal-xl');
            $('#ajax-modal .modal-content').html('');

            $.ajax({
                url: '/ajax/cleanup/'
            });
        });

        $('#confirm-delete').on('show.bs.modal', function(e) {
            e.stopPropagation();

            var $confirm_button = $(this).find('.danger');
            var color = $(e.relatedTarget).data('color');

            $confirm_button.removeClass('btn-primary btn-secondary btn-info btn-success btn-warning btn-danger');
            $('#confirm-delete').removeClass('modal-outline-warning modal-outline-success modal-outline-primary modal-outline-info modal-outline-danger modal-outline-secondary');
            $('#confirm-delete .modal-header').removeClass('bg-warning bg-success bg-primary bg-info bg-danger bg-secondary');
            $('#confirm-delete .modal-content').removeClass('brc-danger-m2 brc-warning-m2 brc-success-m2 brc-primary-m2 brc-info-m2 brc-secondary-m2');

            if(color){
                $confirm_button.addClass('btn-' + color);
                $('#confirm-delete').addClass('modal-outline-' + color);
                $('#confirm-delete .modal-header').addClass( 'bg-' + color );
                $('#confirm-delete .modal-content').addClass('brc-' + color + '-m2');
            }else{
                $confirm_button.addClass('btn-danger');
                $('#confirm-delete').addClass('modal-outline-danger');
                $('#confirm-delete .modal-header').addClass('bg-danger');
                $('#confirm-delete .modal-content').addClass('brc-danger-m2');
            }

            if ($(e.relatedTarget).data('href') != undefined) {
                $confirm_button.data('href', $(e.relatedTarget).data('href'));
                $confirm_button.on('click',  function(){
                    document.location = $(this).data('href');
                });
            } else if ($(e.relatedTarget).data('confirm-action') != undefined) {
                $confirm_button.attr('onclick', $(e.relatedTarget).data('confirm-action'));
            }
            if($(e.relatedTarget).data('confirm-button') != undefined) {
                $confirm_button.html($(e.relatedTarget).data('confirm-button'));
            } else {
                $confirm_button.html($confirm_button.data('default-caption'));
            }
            if($(e.relatedTarget).data('title') != undefined) {
                $('#confirm-delete .modal-title').html( $(e.relatedTarget).data('title') );
            }else{
                $('#confirm-delete .modal-title').html( $('#confirm-delete .modal-title').data('default-title') );
            }

            $('.confirm-question').html( $(e.relatedTarget).data('confirm-question') );
            $('.debug-data').html(  $(e.relatedTarget).data('confirm-data')  );
        });

        // Keep modal scrollable if a confirm modal opened
        $('#confirm-delete').on('hidden.bs.modal', function () {
            $('.modal-footer .btn:not(.btn-approve), .modal-header button').show();
            $('.modal-footer .modal-loader').remove();

            if($('#ajax-modal').hasClass('in')){
                $('body').addClass('modal-open')
            }

            /*
            $.ajax({
                url: '/ajax/cleanup/'
            });
            */
        });

        $(document).on('focus', '.input-group > input, .input-group > select', function(e){
            $(this).parents('.input-group').addClass("input-group-focus");
        }).on('blur', '.input-group > input, .input-group > select', function(e){
            $(this).parents('.input-group').removeClass("input-group-focus");
        });
    },

    showMessage: function(value){
        toastr.options.progressBar = true;
        toastr.options.closeButton = true;

        switch(value.type){
            case 'warning':
                toastr.warning(value.message);
                break;
            case 'error':
                toastr.error(value.message);
                break;
            case 'success':
                toastr.success(value.message);
                break;
            case 'info':
            default:
                toastr.info(value.message);
                break;
        }
    },

    showMessages: function(){
        if(typeof _messages !== 'undefined'){
            $.each( _messages, function(idx, value) {
                app.showMessage(value);
            });
        }
    },

    initToaster: function(){
        setTimeout(function(){ app.showMessages(); }, 500);
    },

    checkLoginStatus: function(response){

    },

    init: function(){
        this.initControls();
        this.initModals();
        this.initToaster();
    }
};

$(function() {
    $.fn.modal.Constructor.prototype._enforceFocus = function () { };

    $.fn.extend({
        insertAtCaret: function(myValue) {
            this.each(function() {
                if (document.selection) {
                    this.focus();
                    var sel = document.selection.createRange();
                    sel.text = myValue;
                    this.focus();
                } else if (this.selectionStart || this.selectionStart == '0') {
                    var startPos = this.selectionStart;
                    var endPos = this.selectionEnd;
                    var scrollTop = this.scrollTop;
                    this.value = this.value.substring(0, startPos) +
                        myValue + this.value.substring(endPos,this.value.length);
                    this.focus();
                    this.selectionStart = startPos + myValue.length;
                    this.selectionEnd = startPos + myValue.length;
                    this.scrollTop = scrollTop;
                } else {
                    this.value += myValue;
                    this.focus();
                }
            });
            return this;
        }
    });

    // Init Bootstrap Tooltips
    //$('[data-toggle="tooltip"]').tooltip();

    // Init Bootstrap Popovers
    //$('[data-toggle="popover"]').popover();

    app.init();
});

function postModalForm(form, btnValue, btnName) {
    if(!btnValue) btnValue = 1;
    if(!btnName) btnName = 'save';
    var formName = $(form).attr('id').substring(0, ($(form).attr('id').length - 5));
    var data = new FormData($(form).get(0));
    data.append(formName + '[' + btnName + ']', btnValue);
    app.setProgress(true);

    $.ajax({
        type: 'POST',
        url: $(form).attr('action'),
        data: data,
        cache: false,
        processData: false,
        contentType: false,
        success: function(data) {
            $('#confirm-delete').modal('hide');
            if(data) {
                processJSONResponse(data);
                app.setProgress(false);
                app.reInit();
            }
        }
    });
}

function pageRedirect(url){
    document.location = url;
}

function modalFormPageRefresh(formname) {
    $('#ajax-modal').modal('hide');

    var $form = $('form');
    if ($form.length > 0) {
        $form = $form.first();
        $form.append('<input type="hidden" name="modalform" value="' + formname + '" />');
        if ($form.hasClass('parsley-form')) {
            $form.parsley().destroy();
        }
        $form.submit();
    } else {
        var location = window.location.href;
        if (location.indexOf('?') > -1) {
            window.location.href += '&modalform=' + formname;
        } else {
            window.location.href += '?modalform=' + formname;
        }
    }
}

/**
 * @deprecated
 * @param data
 */
function modalFormPageUpdate(data){
    $('#ajax-modal').modal('hide');

    if(data){
        if(typeof data !== 'object'){
            data = JSON.parse(data);
        }
        processJSONResponse(data);
    }
}

function fillValues(data) {
    if(data.fill){
        $.each(data.fill, function(key, value){
            $(key).val(value);
        });
    }
}

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
                }else if(method === 'remove'){
                    $(selector).remove();
                }else if(method === 'html'){
                    $(selector).html(value);
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

function initSortable(){
    $('#s-l-base').remove();
    $('.s-l-opener').remove();
    $('.sortableLists').unbind().removeData();

    if($('.sortableLists').length > 0) {
        $('.sortableLists').sortableLists({
            maxLevels: 2,
            hintClass: 'hint',
            placeholderClass: 'placeholder',
            ignoreClass: 'no-sort',
            insertZonePlus: true,
            opener: {
                active: true,
                as: 'html',
                close: '<i class="far fa-minus-square"></i>',
                open: '<i class="far fa-plus-square"></i>',
                openerCss: {
                    'width': '18px',
                    'height': '18px',
                    'margin-left': '-17px',
                    'margin-right': '-2px',
                },
            },
            isAllowed: function(currEl, hint, target){
                if((!currEl.data('category') && target.data('category')) || target.length === 0) {
                    hint.css('background-color', 'rgba(27, 185, 52, 0.1)');
                    return true;
                }else{
                    hint.css('background-color', 'rgba(237, 28, 36, 0.1)');
                    return false;
                }
            },
            onChange: function (cEl) {
                var $sorter = $(cEl).parents('.sortableLists');
                var listid = $sorter.data('listid');
                var url = $sorter.data('url');
                if(!url){
                    var list = $sorter.data('list');
                    url = "/ajax/listHandler/" + list + "/sort/";
                }

                $.ajax({
                    type: "POST",
                    url: url,
                    data: JSON.stringify({listid: listid, items: $sorter.sortableListsToArray()}),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    success: function (data) {
                        processJSONResponse(data);
                    },
                });
            }
        });
    }
}

function initInfiniteScroll() {
    if ($('.infinite_scroll').length > 0) {
        $(window).scroll(function () {
            var pageBottom = $(window).scrollTop() + $(window).height() + 400;
            $('.infinite_scroll:visible:not(.loading)').each(function(){
                var offsetTop = $(this).offset().top;
                if (offsetTop < pageBottom) {
                    var current = parseInt($(this).data('current'));
                    var pagenum = parseInt($(this).data('pagenum'));
                    if (pagenum > current) {
                        $(this).addClass('loading');
                        current = current * 1 + 1;
                        $(this).data('current', current);
                        if ($(this).data('callback')) {
                            executeFunctionByName($(this).data('callback'), window, this);
                        } else if ($(this).data('url')) {
                            $.ajax({
                                url: $(this).data('url') + current,
                                success: $.proxy(processInfiniteScroll, this)
                            });
                        }
                    } else {
                        $(this).hide();
                    }
                }
            });
        });
        $(window).scroll();
    }
}

function executeFunctionByName(functionName, context /*, args */) {
    var args = Array.prototype.slice.call(arguments, 2);
    var namespaces = functionName.split(".");
    var func = namespaces.pop();
    for (var i = 0; i < namespaces.length; i++) {
        context = context[namespaces[i]];
    }
    return context[func].apply(context, args);
}

function processInfiniteScroll(data) {
    $( $(this).data('container') ).append(data);
    $(this).removeClass('loading');
}

function resetInfiniteScroll(id, pagenum) {
    var $scroll = $('#' + id);
    $scroll.removeClass('loading').data('current', 1).data('pagenum', pagenum);
    if (pagenum > 1) {
        $scroll.show();
    } else {
        $scroll.hide();
    }
}