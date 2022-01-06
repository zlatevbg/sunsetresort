//=require plugins/*.js
//=require vendor/plugins/combine/*.js
//=require vendor/headroom.min.js
//=require vendor/js.cookie.js
//=require vendor/history.min.js

var unikat = function() {
    'use strict';

    var variables = {
        tables: [],
        rows_selected: {},
        lock_time: 0,
        mergeFields: 0,
        adultsFields: 0,
        childrenFields: 0,
        jsTestHook: '.js-test',
        jsPrintHook: '.js-print',
        jsSendHook: '.js-send',
        jsMultipleHook: '.js-multiple',
        jsActivateHook: '.js-activate',
        jsSendProfileHook: '.js-send-profile',
        jsCancelRentalContractHook: '.js-cancel-rental-contract',
        jsPreviewHook: '.js-preview',
        jsCreateHook: '.js-create',
        jsEditHook: '.js-edit',
        jsSaveHook: '.js-save',
        jsDestroyHook: '.js-destroy',
        jsUploadHook: '.js-upload',
        jsReuploadHook: '.js-reupload',
        jsAddMergeHook: '.js-add-merge',
        jsRemoveMergeHook: '.js-remove-merge',
        jsAddAdultHook: '.js-add-adult',
        jsRemoveAdultHook: '.js-remove-adult',
        jsAddChildHook: '.js-add-child',
        jsRemoveChildHook: '.js-remove-child',
        filterClass: '.dataTables_filter',
        datatablePrefix: 'datatable',
        datatableWarapper: '.dataTableWrapper',
    };

    var htmlLoading;

    var mergeFieldHtml = '<div class="form-group"><label for="input-merge-{id}">Merge Field {id}</label><div class="input-group"><input id="input-merge-{id}" class="form-control" placeholder="Merge Field {id}" name="merge[]" type="text"><span class="input-group-btn"><button data-num="{id}" class="btn btn-danger js-remove-merge" type="button"><span class="glyphicon glyphicon-remove"></span></button></span></div></div>';
    var adultFieldHtml = '<div class="form-group"><label for="input-adult-{id}">Adult Name {id}</label><div class="input-group"><input id="input-adult-{id}" class="form-control" placeholder="Adult Name {id}" name="adults[]" type="text"><span class="input-group-btn"><button data-num="{id}" class="btn btn-danger js-remove-adult" type="button"><span class="glyphicon glyphicon-remove"></span></button></span></div></div>';
    var childFieldHtml = '<div class="form-group"><label for="input-child-{id}">Child Name {id}</label><div class="input-group"><input id="input-child-{id}" class="form-control" placeholder="Child Name {id}" name="children[]" type="text"><span class="input-group-btn"><button data-num="{id}" class="btn btn-danger js-remove-child" type="button"><span class="glyphicon glyphicon-remove"></span></button></span></div></div>';

    var progressBarHtml = '<div id="upload-progress-bar-container" class="qq-total-progress-bar-container-selector progress"><div id="upload-progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" class="qq-total-progress-bar-selector progress-bar progress-bar-success progress-bar-striped active"></div></div>';

    var errorWrapperHtmlStart = '<div class="alert alert-danger alert-dismissible"><button type="button" class="close"><span aria-hidden="true">&times;</span></button>';
    var errorMessageHtmlStart =  '<ul>';
    var errorListHtmlStart =      '<li>';
    var errorListHtmlEnd =        '</li>';
    var errorMessageHtmlEnd =    '</ul>';
    var errorWrapperHtmlEnd =   '</div>';

    var successWrapperHtmlStart = '<div class="alert alert-success alert-dismissible"><button type="button" class="close"><span aria-hidden="true">&times;</span></button><span class="glyphicon glyphicon-ok"></span>';
    var successWrapperHtmlEnd =   '</div>';

    var alertMessagesHtmlStart = '<div class="alert-messages">';
    var alertMessagesAbsoluteHtmlStart = '<div class="alert-messages absolute">';
    var alertMessagesHtmlEnd =   '</div>';

    var glyphiconWarning = '<span class="glyphicon glyphicon-warning-sign"></span>';
    var glyphiconRemove = '<span class="glyphicon glyphicon-remove form-control-feedback"></span>';
    var glyphiconRemoveSpan = 'span.glyphicon-remove';

    var formGroupClass = '.form-group';
    var hasErrorClass = 'has-error has-feedback';
    var hasErrorClasses = hasErrorClass + ' has-addon-feedback';
    var ajaxLockClass = '.ajax-lock';
    var ajaxLockedClass = '.ajax-locked';
    var alertMessagesClass = '.alert-messages';
    var alertSuccessClass = '.alert-success';
    var alertErrorClass = '.alert-danger';
    var alertClass = '.alert';
    var inputGroupAddonClass = 'input-group-addon';
    var inputGroupBtnClass = 'input-group-btn';
    var buttonCloseClass = 'button.close';

    function run() {
        htmlLoading = '<div tabindex="-1" class="ajax-locked"><div><div><img src="' + variables.loadingImageSrc + '" alt="' + variables.loadingImageAlt + '" title="' + variables.loadingImageTitle + '">' + variables.loadingText + '</div></div></div>';
        errorMessageHtmlStart = variables.ajaxErrorMessage + errorMessageHtmlStart;

        if (!variables.is_auth) {
            var location = window.history.location || window.location;

            $(window).on('popstate', function(e) {
                var $sidebars = $('.sidebar');
                var $tabs = $('.sidebar-tabs li');
                var hash = window.location.hash; //.substring(1);
                var $index = (hash ? $sidebars.index($(hash)) : 0);
                var jsCookies = Cookies.getJSON('jsCookies');

                $sidebars.removeClass('sidebar-active');
                $tabs.removeClass('sidebar-tab-active');
                $sidebars.eq($index).addClass('sidebar-active').css('opacity', 0).animate({opacity: 1}, 'fast');
                $tabs.eq($index).addClass('sidebar-tab-active');

                Cookies.set('jsCookies', { sidebar: $index, navState: (jsCookies ? jsCookies.navState : null) }, { expires: 365 });
            });

            $('#fixed-header').headroom({
                offset: variables.headroomOffset
            });

            $(document).on('click', '#generate-password', function(e) {
                e.preventDefault();
                $('#input-password, #input-password_confirmation').attr('type', 'text').val(Password.generate(8));
                return false;
            });

            $('.nav-toggle').on('click', 'a', function(e) {
                e.preventDefault();

                $(this).parent().toggleClass('collapsed');
                $('#wrapper').toggleClass('collapsed');

                var navState;
                if ($('#wrapper').hasClass('collapsed')) {
                    navState = 'collapsed';
                } else {
                    navState = null;
                }

                var jsCookies = Cookies.getJSON('jsCookies');
                Cookies.set('jsCookies', { navState: navState, sidebar: (jsCookies ? jsCookies.sidebar : null) }, { expires: 365 });
            });

            $('.sidebar-tabs').on('click', 'a', function(e) {
                e.preventDefault();

                var $parent = $(this).parent();
                var $tabs = $('.sidebar-tabs li');
                var $sidebars = $('.sidebar');
                var $index = $tabs.index($parent);

                $tabs.removeClass('sidebar-tab-active');
                $parent.addClass('sidebar-tab-active');

                $sidebars.removeClass('sidebar-active');
                $sidebars.eq($index).addClass('sidebar-active').css('opacity', 0).animate({opacity: 1}, 'fast');

                history.pushState(null, document.title, $(this).attr('href'));

                var jsCookies = Cookies.getJSON('jsCookies');
                Cookies.set('jsCookies', { sidebar: $index, navState: (jsCookies ? jsCookies.navState : null) }, { expires: 365 });
            });

            $(document).on('mouseenter', '.tooltip', function() {
                $(this).qtip({
                    position: {
                        container: $('#wrapper'),
                        viewport: $(window),
                        my: 'bottom center',
                        at: 'top center',
                        target: [$(this).offset().left + 8, $(this).offset().top + 6],
                    },
                    content: {
                        title: $(this).children('.tooltip-content').attr('title'),
                        text: $(this).children('.tooltip-content'),
                    },
                    overwrite: false,
                    show: {
                        ready: true,
                    },
                    hide: {
                        fixed: true,
                        delay: 300,
                    },
                    style: {
                        tip: {
                            width: 12,
                            height: 12,
                        },
                    },
                });
            });

            $('.popup-gallery').magnificPopup({
                delegate: 'a.popup',
                type: 'image',
                tClose: variables.magnificPopup.close,
                tLoading: variables.magnificPopup.loading,
                gallery: {
                    enabled: true,
                    preload: [0, 2],
                    tPrev: variables.magnificPopup.prev,
                    tNext: variables.magnificPopup.next,
                },
                preloader: true,
                mainClass: 'mfp-zoom-in',
                zoom: {
                    enabled: true,
                },
            });

            var magnificPopupOptions = {
                type: 'ajax',
                key: 'popup-form',
                focus: ':input:visible:not(.hasDatepicker)',
                closeOnBgClick: false,
                alignTop: true,
                tClose: variables.magnificPopup.close,
                tLoading: variables.magnificPopup.loading,
                ajax: {
                    tError: variables.magnificPopup.ajaxError
                },
                preloader: true,
                removalDelay: 500,
                mainClass: 'mfp-zoom-in',
            };

            $(document).on('click', variables.jsTestHook, function(e) {
                e.preventDefault();

                ajax_clear_datatables_messages($(this).closest(variables.datatableWarapper));

                var tableId = variables.datatablePrefix + $(this).data('table');
                if (variables.rows_selected[tableId]) {
                    var $rowId = variables.rows_selected[tableId][0];

                    var that = $(this).closest('.ajax-lock');

                    ajaxify({
                        that: that,
                        method: 'get',
                        queue: that.data('ajaxQueue'),
                        action: $(this).attr('href') + '/' + $rowId,
                    });
                }
            });

            $(document).on('click', variables.jsPrintHook, function(e) {
                e.preventDefault();

                ajax_clear_datatables_messages($(this).closest(variables.datatableWarapper));

                var tableId = variables.datatablePrefix + $(this).data('table');
                if (variables.rows_selected[tableId]) {
                    var $rowId = variables.rows_selected[tableId][0];

                    window.location.href = $(this).attr('href') + '/' + $rowId;
                }
            });

            $(document).on('click', variables.jsSendProfileHook, function(e) {
                e.preventDefault();

                ajax_clear_datatables_messages($(this).closest(variables.datatableWarapper));

                var tableId = variables.datatablePrefix + $(this).data('table');
                if (variables.rows_selected[tableId]) {
                    var $rowId = variables.rows_selected[tableId][0];

                    var that = $(this).closest('.ajax-lock');

                    ajaxify({
                        that: that,
                        method: 'get',
                        queue: that.data('ajaxQueue'),
                        action: $(this).attr('href') + '/' + $rowId,
                    });
                }
            });

            $(document).on('click', variables.jsSendHook, function(e) {
                e.preventDefault();

                ajax_clear_datatables_messages($(this).closest(variables.datatableWarapper));

                var tableId = variables.datatablePrefix + $(this).data('table');
                if (variables.rows_selected[tableId]) {
                    var $rowId = variables.rows_selected[tableId][0];

                    var that = $(this).closest('.ajax-lock');

                    ajaxify({
                        that: that,
                        method: 'get',
                        queue: that.data('ajaxQueue'),
                        action: $(this).attr('href') + '/' + $rowId,
                    });
                }
            });

            $(document).on('click', variables.jsMultipleHook + ', ' + variables.jsActivateHook, function(e) {
                e.preventDefault();

                ajax_clear_datatables_messages($(this).closest(variables.datatableWarapper));

                var tableId = variables.datatablePrefix + $(this).data('table');
                if (variables.rows_selected[tableId]) {
                    var $rowId = variables.rows_selected[tableId][0];

                    var that = $(this).closest('.ajax-lock');

                    ajaxify({
                        that: that,
                        method: 'get',
                        queue: that.data('ajaxQueue'),
                        action: $(this).attr('href'),
                    });
                }
            });

            $(document).on('click', variables.jsPreviewHook, function(e) {
                e.preventDefault();

                var src = $(this).attr('href');

                $.magnificPopup.open($.extend(magnificPopupOptions, {
                    items: {
                        src: src,
                    },
                    callbacks: {
                        parseAjax: function(mfpResponse) {
                          mfpResponse.data = typeof mfpResponse.data == 'string' ? mfpResponse.data : mfpResponse.data[0];
                        },
                        ajaxContentAdded: function() {
                            $('#fixed-header').addClass('headroom--unpinned');
                        },
                        close: function() {
                            $('#fixed-header').removeClass('headroom--unpinned');
                        },
                    },
                }));
            });

            $(document).on('click', variables.jsAddMergeHook, function(e) {
                e.preventDefault();

                var html = mergeFieldHtml.replace(new RegExp(escapeRegExp('{id}'), 'g'), ++variables.mergeFields);
                $('#merge-fields').append(html);

                $(variables.jsRemoveMergeHook).not("[data-num='" + variables.mergeFields + "']").prop('disabled', true);
            });

            $(document).on('click', variables.jsRemoveMergeHook, function(e) {
                e.preventDefault();

                $(this).closest('.form-group').remove();
                $(variables.jsRemoveMergeHook + '[data-num="' + (--variables.mergeFields) + '"]').prop('disabled', false);
            });

            $(document).on('change', 'input[name="merge_by"]', function() {
                if ($(this).val() > 0) {
                    $('#merge-fields').removeClass('hidden');
                } else {
                    $('#merge-fields').addClass('hidden');
                }
            });

            $(document).on('click', variables.jsAddAdultHook, function(e) {
                e.preventDefault();

                var html = adultFieldHtml.replace(new RegExp(escapeRegExp('{id}'), 'g'), ++variables.adultsFields);
                $(html).insertBefore($('#adults-fields .btn-group-wrapper'));

                $(variables.jsRemoveAdultHook).not("[data-num='" + variables.adultsFields + "']").prop('disabled', true);
            });

            $(document).on('click', variables.jsRemoveAdultHook, function(e) {
                e.preventDefault();

                $(this).closest('.form-group').remove();
                $(variables.jsRemoveAdultHook + '[data-num="' + (--variables.adultsFields) + '"]').prop('disabled', false);
            });

            $(document).on('click', variables.jsAddChildHook, function(e) {
                e.preventDefault();

                var html = childFieldHtml.replace(new RegExp(escapeRegExp('{id}'), 'g'), ++variables.childrenFields);
                $(html).insertBefore($('#children-fields .btn-group-wrapper'));

                $(variables.jsRemoveChildHook).not("[data-num='" + variables.childrenFields + "']").prop('disabled', true);
            });

            $(document).on('click', variables.jsRemoveChildHook, function(e) {
                e.preventDefault();

                $(this).closest('.form-group').remove();
                $(variables.jsRemoveChildHook + '[data-num="' + (--variables.childrenFields) + '"]').prop('disabled', false);
            });

            $(document).on('click', variables.jsCreateHook, function(e) {
                e.preventDefault();

                var table = $(this).data('table');
                var owner = $(this).data('owner');
                var project = $(this).data('project');
                var building = $(this).data('building');
                var floor = $(this).data('floor');
                var apartment = $(this).data('apartment');
                var contract = $(this).data('contract');
                var domain = $(this).data('domain');
                var locale = $(this).data('locale');
                var year = $(this).data('year');
                var parent = $(this).data('parent');
                var slugs = $(this).data('slugs');
                var separator = $(this).attr('href').indexOf('?') == -1 ? '?' : '&';
                var src = $(this).attr('href') + (table ? separator + 'table=' + table : '');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (owner ? separator + 'owner=' + owner : '');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (project ? separator + 'project=' + project : '');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (building ? separator + 'building=' + building : '');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (floor ? separator + 'floor=' + floor : '');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (apartment ? separator + 'apartment=' + apartment : '');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (contract ? separator + 'contract=' + contract : '');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (domain ? separator + 'domain=' + domain : '');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (locale ? separator + 'locale=' + locale : '');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (year ? separator + 'year=' + year : '');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (parent ? separator + 'parent=' + parent : '');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (slugs ? separator + 'slugs=' + slugs : '');

                $.magnificPopup.open($.extend(magnificPopupOptions, {
                    items: {
                        src: src,
                    },
                    callbacks: {
                        parseAjax: function(mfpResponse) {
                          mfpResponse.data = typeof mfpResponse.data == 'string' ? mfpResponse.data : mfpResponse.data[0];
                        },
                        ajaxContentAdded: function() {
                            $('#fixed-header').addClass('headroom--unpinned');

                            if (typeof CKEDITOR == 'object') {
                                ckeditorSetup(unikat.ckeditorConfig);
                            }

                            if (typeof $.multiselect == 'object') {
                                multiselectSetup(unikat.multiselect);
                            }

                            if (typeof unikat.magnificPopupCreateCallback == 'function') {
                                unikat.magnificPopupCreateCallback();
                            }
                        },
                        close: function() {
                            $('#fixed-header').removeClass('headroom--unpinned');
                        },
                    },
                }));
            });

            $(document).on('click', variables.jsDestroyHook + ', ' + variables.jsMultipleHook + ', ' + variables.jsActivateHook, function(e) {
                e.preventDefault();

                ajax_clear_datatables_messages($(this).closest(variables.datatableWarapper));

                var table = $(this).data('table');
                var owner = $(this).data('owner');
                var apartment = $(this).data('apartment');
                var year = $(this).data('year');
                var contract = $(this).data('contract');
                var project = $(this).data('project');
                var building = $(this).data('building');
                var floor = $(this).data('floor');
                var separator = $(this).attr('href').indexOf('?') == -1 ? '?' : '&';
                var src = $(this).attr('href') + (table ? separator + 'table=' + table : '');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (owner ? separator + 'owner=' + owner : '');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (apartment ? separator + 'apartment=' + apartment : '');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (year ? separator + 'year=' + year : '');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (contract ? separator + 'contract=' + contract : '');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (project ? separator + 'project=' + project : '');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (building ? separator + 'building=' + building : '');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (floor ? separator + 'floor=' + floor : '');
                var slugs = $(this).data('slugs');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (slugs ? separator + 'slugs=' + slugs : '');

                $.magnificPopup.open($.extend(magnificPopupOptions, {
                    items: {
                        src: src,
                    },
                    callbacks: {
                      parseAjax: function(mfpResponse) {
                        mfpResponse.data = typeof mfpResponse.data == 'string' ? mfpResponse.data : mfpResponse.data[0];
                      },
                    },
                }));
            });

            $(document).on('click', variables.jsEditHook, function(e) {
                e.preventDefault();

                ajax_clear_datatables_messages($(this).closest(variables.datatableWarapper));

                var table = $(this).data('table');
                var apartment = $(this).data('apartment');
                var project = $(this).data('project');
                var building = $(this).data('building');
                var floor = $(this).data('floor');
                var separator = $(this).attr('href').indexOf('?') == -1 ? '?' : '&';

                var param = null;
                var tableId = variables.datatablePrefix + table;
                if (variables.rows_selected[tableId]) {
                    param = '/' + variables.rows_selected[tableId][0];
                }

                var src = $(this).attr('href') + param + (table ? separator + 'table=' + table : '');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (project ? separator + 'project=' + project : '');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (apartment ? separator + 'apartment=' + apartment : '');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (building ? separator + 'building=' + building : '');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (floor ? separator + 'floor=' + floor : '');

                var slugs = $(this).data('slugs');
                separator = src.indexOf('?') == -1 ? '?' : '&';
                src += (slugs ? separator + 'slugs=' + slugs : '');

                $.magnificPopup.open($.extend(magnificPopupOptions, {
                    items: {
                        src: src,
                    },
                    callbacks: {
                        parseAjax: function(mfpResponse) {
                          mfpResponse.data = typeof mfpResponse.data == 'string' ? mfpResponse.data : mfpResponse.data[0];
                        },
                        ajaxContentAdded: function() {
                            $('#fixed-header').addClass('headroom--unpinned');

                            if (typeof CKEDITOR == 'object') {
                                ckeditorSetup(unikat.ckeditorConfig);
                            }

                            if (typeof $.multiselect == 'object') {
                                multiselectSetup(unikat.multiselect);
                            }

                            if (typeof unikat.magnificPopupEditCallback == 'function') {
                                unikat.magnificPopupEditCallback();
                            }

                            if (typeof unikat.merge == 'number') {
                                variables.mergeFields = unikat.merge;
                                $(variables.jsRemoveMergeHook).not("[data-num='" + variables.mergeFields + "']").prop('disabled', true);
                            }

                            if (typeof unikat.adults == 'number') {
                                variables.adultsFields = unikat.adults;
                                $(variables.jsRemoveAdultHook).not("[data-num='" + variables.adultsFields + "']").prop('disabled', true);
                            }

                            if (typeof unikat.children == 'number') {
                                variables.childrenFields = unikat.children;
                                $(variables.jsRemoveChildHook).not("[data-num='" + variables.childrenFields + "']").prop('disabled', true);
                            }
                        },
                        close: function() {
                            $('#fixed-header').removeClass('headroom--unpinned');
                        },
                    },
                }));

                ajax_unlock($(this).closest(ajaxLockClass));
            });

            $(variables.jsUploadHook + ', ' + variables.jsReuploadHook).each(function() {
                if (typeof qq !== 'undefined') {
                    var params = {
                        id: $(this).attr('id'),
                        table: $(this).data('table'),
                        url: $(this).data('url'),
                        multiple: $(this).data('multiple'),
                        reupload: $(this).data('reupload'),
                        isFile: $(this).data('isFile'),
                        headerWrapper: $(this).data('headerWrapper'),
                        form: $(this).data('form'),
                    };

                    unikat.fineUploaderConfig = unikat.fineUploaderConfig || {};
                    $.extend(unikat.fineUploaderConfig, {
                        multiple: params.multiple,
                    });

                    fineUploaderSetup(params, unikat.fineUploaderConfig);
                }
            });

            $(document).on('click', variables.jsSaveHook, function(e) {
                e.preventDefault();

                var extra = [];
                var table = $('#input-table').val();
                if (typeof table != 'undefined') {
                    var tableId = variables.datatablePrefix + table;
                    if (variables.rows_selected[tableId]) {
                        $.each(variables.rows_selected[tableId], function(key, value) {
                            extra.push({ name: 'id[]', value: value });
                        });
                    }
                }

                var form = $(this).closest('form');

                var data = form.serialize();
                extra = $.param(extra);
                if (extra) {
                    data += '&' + extra;
                }

                ajaxify({
                    that: form,
                    method: 'post',
                    queue: form.data('ajaxQueue'),
                    action: form.attr('action'),
                    data: data,
                });

                return false;
            });

            if (variables.datatables) {
                // Handle click on "Select all" checkbox
                $(document).on('click', '.table-checkbox thead input[type="checkbox"]', function(e) {
                    var tableId = $(this).closest('table').attr('id');

                    if (this.checked) {
                        $('#' + tableId + ' tbody input[type="checkbox"]:not(:checked)').trigger('click');
                    } else {
                        $('#' + tableId + ' tbody input[type="checkbox"]:checked').trigger('click');
                    }

                    e.stopPropagation(); // Prevent click event from propagating to parent
                });

                // Handle click on table cells with checkboxes
                $(document).on('click', '.table-checkbox tbody td, .table-checkbox thead th:first-child', function(e) {
                    if (e.target.tagName.toLowerCase() !== 'a' && e.target.tagName.toLowerCase() !== 'img' && e.target.tagName.toLowerCase() !== 'span') {
                        $(this).parent().find('input[type="checkbox"]').trigger('click');
                    }
                });

                // Handle click on checkbox
                $(document).on('click', '.table-checkbox tbody input[type="checkbox"]', function(e) {
                    var tableId = $(this).closest('table').attr('id');
                    var $row = $(this).closest('tr');
                    var rowId = $row.attr('id');

                    var index = $.inArray(rowId, variables.rows_selected[tableId]);

                    if (this.checked && index === -1) { // If checkbox is checked and row ID is not in list of selected row IDs
                        variables.rows_selected[tableId].push(rowId);
                    } else if (!this.checked && index !== -1) { // Otherwise, if checkbox is not checked and row ID is in list of selected row IDs
                        variables.rows_selected[tableId].splice(index, 1);
                    }

                    if (variables.rows_selected[tableId].length == 1 && $('#' + variables.rows_selected[tableId][0]).hasClass('new-profile')) {
                        $(variables.jsSendProfileHook).removeClass('hidden');
                    }

                    if (variables.rows_selected[tableId].length == 1 && $('#' + variables.rows_selected[tableId][0]).hasClass('deleted')) {
                        $(variables.jsCancelRentalContractHook).addClass('hidden');
                    }

                    if (variables.rows_selected[tableId].length == 1 && !$('#' + variables.rows_selected[tableId][0]).hasClass('deleted')) {
                        $(variables.jsCancelRentalContractHook).removeClass('hidden');
                    }

                    if (this.checked) {
                        $row.addClass('selected');
                    } else {
                        $row.removeClass('selected');
                    }

                    datatablesUpdateCheckbox(tableId);

                    e.stopPropagation(); // Prevent click event from propagating to parent
                });
            }

            if (typeof $.multiselect == 'object') {
                multiselectSetup(unikat.multiselect);
            }
        }

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.submenu').length) {
                $('.dropdown-menu').each(function() {
                    if (!$(this).hasClass('menu-static')) {
                        $(this).removeClass('active');
                    }
                });

                $('.slidedown-menu').each(function() {
                    if (!$(this).hasClass('menu-static')) {
                        $(this).slideUp();
                    }
                });
            }
        });

        $(document).on('click', '.dropdown-toggle', function(e) {
            e.preventDefault();

            $(this).toggleClass('active');

            var that = $(this).next();

            $('.dropdown-menu.active').not(that[0]).removeClass('active');

            that.toggleClass('active');
        });

        $(document).on('click', '.slidedown-toggle', function(e) {
            e.preventDefault();

            if (!$(e.target).closest('.dropdown-menu').length) {
                $('.dropdown-menu.active').removeClass('active');
            }

            $(this).toggleClass('active');
            $(this).next().slideToggle();
        });

        $(document).on('click', alertMessagesClass + ' ' + buttonCloseClass, function() {
            $(this).closest(alertClass).remove();
        });

        if (typeof this.callback == 'function') {
            this.callback();
        }

        $(document).on('submit', 'form', function(e) {
            e.preventDefault();

            var extra = [];
            var table = $('#input-table').val();
            if (typeof table != 'undefined') {
                var tableId = variables.datatablePrefix + table;
                if (variables.rows_selected[tableId]) {
                    $.each(variables.rows_selected[tableId], function(key, value) {
                        extra.push({ name: 'id[]', value: value });
                    });
                }
            }

            var data = $(this).serialize();
            extra = $.param(extra);
            if (extra) {
                data += '&' + extra;
            }

            ajaxify({
                that: $(this),
                method: 'post',
                queue: $(this).data('ajaxQueue'),
                action: $(this).attr('action'),
                data: data,
            });

            return false;
        });

        $(document).on('click', '.change-status', function(e) {
            e.preventDefault();

            var that = $(this);

            ajaxify({
                that: that,
                method: 'get',
                queue: $(this).data('ajaxQueue'),
                action: $(this).attr('href'),
                skipLock: true,
                skipErrors: true,
                functionParams: ['href', 'img'],
                function: function(params) {
                    that.attr('href', params.href);
                    that.children('img').replaceWith(params.img);
                }
            });

            return false;
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        });
    }

    function escapeRegExp(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"); // $& means the whole matched string
    }

    function ckeditorSetup(config) {
        var config = config || {};

        if (typeof CKFinder == 'object') {
            CKFinder.basePath = CKEDITOR.basePath.replace('ckeditor', 'ckfinder');

            CKFinder.config({
                configPath: '', // don't load config.js
                language: variables.language,
                defaultLanguage: variables.defaultLanguage, // used if language is not available
                defaultDisplayDate: false,
                defaultDisplayFileName: false,
                defaultDisplayFileSize: false,
                defaultSortBy: 'date',
                defaultSortByOrder: 'desc',
                editImageAdjustments: [
                    'brightness', 'clip', 'contrast', 'exposure', 'gamma', 'hue', 'noise', 'saturation', 'sepia',
                    'sharpen', 'stackBlur', 'vibrance'
                ],
                editImagePresets: [
                    'clarity', 'concentrate', 'crossProcess', 'glowingSun', 'grungy', 'hazyDays', 'hemingway', 'herMajesty',
                    'jarques', 'lomo', 'love', 'nostalgia', 'oldBoot', 'orangePeel', 'pinhole', 'sinCity', 'sunrise', 'vintage'
                ],
                jquery: 'https://sky.sunsetresort.bg/js/sky/vendor/jquery-2.2.0.min.js',
                // connectorInfo: 'token=7901a26e4bc422aef54eb45', // Additional (GET) parameters to send to the server with each request.
            });

            CKFinder.setupCKEditor();
        } else {
            $.extend(config, {
                removePlugins: 'uploadimage',
            });
        }

        var defaultConfig = {
            customConfig: '', // don't load config.js
            language: variables.language,
            disableNativeSpellChecker: false,
            removePlugins: '',
            extraPlugins: 'autosave,article,placeholder_elements',
            autosave_delay: 300, // seconds
            autosave_messageType: 'statusbar',
            autoGrow_maxHeight: 400,
            autoGrow_minHeight: 200,
            autoGrow_onStartup: true,
            extraAllowedContent: 'section(*); article(*); span(*); td[valign,class](*)',
            disallowedContent: 'img{width,height,border*}', // Disallow setting inline borders, width & height for images.
            /*filebrowserBrowseUrl: '/js/sky/vendor/ckfinder/ckfinder.html',
            filebrowserUploadUrl: '/js/sky/vendor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
            uploadUrl: '/js/sky/vendor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images&responseType=json',*/
            colorButton_colors: 'ffffff,000000,364f9d,0e76bc,3a99d3,6ebbe8,f4fbfe,f6f6f6', // set brand colors
            contentsCss: '/css/owners/main.css', // replace with css/www/main.min.css
            contentsLangDirection: variables.languageScript,
            defaultLanguage: variables.defaultLanguage, // used if language is not available
            fontSize_sizes: '12/.75em;14/.875em;16/1em;20/1.25em;24/1.5em',
            font_names: 'Helvetica, Arial, sans-serif; Roboto/Roboto, Helvetica, Arial, sans-serif',
            format_tags: 'p;h1;h2;h3;h4;h5;h6;div',
            justifyClasses: ['text-left', 'text-center', 'text-right', 'text-justify'],
            removeButtons: '', // maybe needed
            removeDialogTabs: '', // maybe needed
            stylesSet: [ // see the styles.js
                { name: 'Strong Emphasis', element: 'strong' },
                { name: 'Emphasis', element: 'em' },
            ],
            templates: 'unikat',
            templates_files: ['/js/sky/templates/unikat.js'],
            placeholder_elements: {
                css: '.cke_placeholder_element { background: #ffff00; } a .cke_placeholder_element { text-decoration: underline }',
                draggable: true,
                /**
                 * A list of placeholders, defined as objects with `label` and `value`
                 * properties, where the label is being displayed in the menu, and value
                 * is used as the placeholder text.
                 *
                 * Note that delimiters are added automatically, so the value should be
                 * defined without!
                 *
                 * [
                 *     {label: 'Placeholder 1', value: 'PLACEHOLDER_1'},
                 *     {label: 'Placeholder 2', value: 'PLACEHOLDER_2'},
                 *     {label: 'Placeholder 3', value: 'PLACEHOLDER_3'},
                 *     // ...
                 * ]
                 *
                 * When using the `combo` UI type, it's also possible to define groups
                 * using the `group` and `placeholders` keys, where `group` defines the
                 * title of group that is displayed in the menu, and `placeholders` is an
                 * array that holds the groups placeholders.
                 *
                 * Note that grouping is only a visual thing, placeholder values must still
                 * be unique!
                 *
                 * [
                 *     {
                 *         group: 'Group 1',
                 *         placeholders: [
                 *             {label: 'Placeholder 1', value: 'PLACEHOLDER_1'},
                 *             {label: 'Placeholder 2', value: 'PLACEHOLDER_2'}
                 *         ]
                 *     },
                 *     {
                 *         group: 'Group 2',
                 *         placeholders: [
                 *             {label: 'Placeholder 3', value: 'PLACEHOLDER_4'},
                 *             {label: 'Placeholder 4', value: 'PLACEHOLDER_5'}
                 *         ]
                 *     },
                 *     // ...
                 * ]
                 */
                placeholders: [
                    { label: 'Owner E-mail', value: 'OWNER_EMAIL' },
                    { label: 'Owner First Name', value: 'OWNER_FIRST_NAME' },
                    { label: 'Owner Last Name', value: 'OWNER_LAST_NAME' },
                    { label: 'Owner Full Name', value: 'OWNER_FULL_NAME' },
                    { label: 'Owner Password', value: 'OWNER_PASSWORD' },
                    { label: 'Inline Image', value: 'IMAGE' },
                    { label: 'Signature', value: 'SIGNATURE' },
                    { label: 'Merge Apartment', value: 'MERGE_APARTMENT' },
                    { label: 'Merge Owner', value: 'MERGE_OWNER' },
                    { label: 'Merge Field', value: 'MERGE' },
                    { label: 'Token', value: 'TOKEN' },
                ],
                startDelimiter: '{',
                endDelimiter: '}',
                /**
                 * Defines the type of UI element that holds the placeholders. Either
                 * `button` or `combo`.
                 */
                uiType: 'button'
            },
        };

        $.extend(defaultConfig, config);

        CKEDITOR.replaceAll(function(textarea, config) {
            if ($(textarea).hasClass('ckeditor')) {
                $.extend(config, defaultConfig);
            } else {
                return false;
            }
        });
    }

    function multiselectSetup(multiselect) {
        $.extend($.multiselect.multiselect.prototype.options, {
            checkAllText: variables.multiselect.checkAll,
            uncheckAllText: variables.multiselect.uncheckAll,
            noneSelectedText: variables.multiselect.noneSelected,
            noneSelectedSingleText: variables.multiselect.noneSelectedSingle,
            selectedText: variables.multiselect.selected,
            filterLabel: variables.multiselect.filterLabel,
            filterPlaceholder: variables.multiselect.filterPlaceholder,
        });

        $.each(multiselect, function(key, value) {
            $('#' + key).multiselect(value);
        });
    }

    function fineUploaderSetup(params, config) {
        var config = config || {};

        if (params.headerWrapper) {
            params.headerWrapper = $(params.headerWrapper);
        } else {
            params.headerWrapper = $('#' + params.id).closest(variables.datatableWarapper).children(':first');
        }

        var defaultConfig = {
            // debug: true,
            button: document.getElementById(params.id),
            multiple: true,
            maxConnections: 1, // there are problems with multiple connections: the files are not uploaded or the records in the DB are duplicated.
            chunking: {
                enabled: true,
                concurrent: {
                    enabled: true,
                },
                success: {
                    endpoint: params.url + '/done',
                },
            },
            paste: {
                targetElement: document,
            },
            request: {
                endpoint: params.url,
                customHeaders: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
            },
            resume: {
                enabled: true,
            },
            retry: {
               enableAuto: true,
            },
            validation: {
                allowedExtensions: params.isFile ? variables.fineUploader.fileExtensions : variables.fineUploader.imageExtensions,
                stopOnFirstInvalidFile: false,
            },
            text: {
                defaultResponseError: variables.fineUploader.defaultResponseError,
            },
            messages: {
                emptyError: variables.fineUploader.emptyError,
                maxHeightImageError: variables.fineUploader.maxHeightImageError,
                maxWidthImageError: variables.fineUploader.maxWidthImageError,
                minHeightImageError: variables.fineUploader.minHeightImageError,
                minWidthImageError: variables.fineUploader.minWidthImageError,
                minSizeError: variables.fineUploader.minSizeError,
                noFilesError: variables.fineUploader.noFilesError,
                onLeave: variables.fineUploader.onLeave,
                retryFailTooManyItemsError: variables.fineUploader.retryFailTooManyItemsError,
                sizeError: variables.fineUploader.sizeError,
                tooManyItemsError: variables.fineUploader.tooManyItemsError,
                typeError: variables.fineUploader.typeError,
                unsupportedBrowserIos8Safari: variables.fineUploader.unsupportedBrowserIos8Safari,
            },
            callbacks: {
                onTotalProgress: function(totalUploadedBytes, totalBytes) {
                    var progress;
                    var speed = '';
                    var minSamples = 6;
                    var maxSamples = 20;
                    var uploadSpeeds = [];
                    var progressPercent = (totalUploadedBytes / totalBytes).toFixed(2);
                    var totalSize = formatSize(totalBytes, this._options.text.sizeSymbols);
                    var totalUploadedSize = formatSize(totalUploadedBytes, this._options.text.sizeSymbols);

                    uploadSpeeds.push({
                        totalUploadedBytes: totalUploadedBytes,
                        currentTime: new Date().getTime(),
                    });

                    if (uploadSpeeds.length > maxSamples) {
                        uploadSpeeds.shift(); // remove first element
                    }

                    if (uploadSpeeds.length >= minSamples) {
                        var firstSample = uploadSpeeds[0];
                        var lastSample = uploadSpeeds[uploadSpeeds.length - 1];
                        var progressBytes = lastSample.totalUploadedBytes - firstSample.totalUploadedBytes;
                        var progressTimeMS = lastSample.currentTime - firstSample.currentTime;
                        var Mbps = ((progressBytes * 8) / (progressTimeMS / 1000) / (1000 * 1000)).toFixed(2); // megabits per second
                        // var MBps = (progressBytes / (progressTimeMS / 1000) / (1024 * 1024)).toFixed(2); // megabytes per second

                        if (Mbps > 0) {
                            speed = ' / ' + Mbps + ' Mbps';
                        }
                    }

                    if (isNaN(progressPercent)) {
                        progress = '0%';
                    } else {
                        progress = (progressPercent * 100).toFixed() + '%';
                    }

                    $('#upload-progress-bar').css('width', progress).text(progress + ' (' + totalUploadedSize + ' of ' + totalSize + speed + ')');
                },
                onAllComplete: function(succeeded, failed) {
                    $('#upload-progress-bar-container').remove();

                    if (failed.length > 0) {
                        var that = this;
                        var msg = errorWrapperHtmlStart + errorMessageHtmlStart;
                        $.each(failed, function(key) {
                            msg += errorListHtmlStart + glyphiconWarning + that.getName(key) + errorListHtmlEnd;
                        });
                        msg += errorMessageHtmlEnd + errorWrapperHtmlEnd;
                        ajax_message(params.headerWrapper, msg, 'insertAfter');
                    }

                    if (succeeded.length > 0) {
                        ajax_message(params.headerWrapper, successWrapperHtmlStart + variables.fineUploader.uploadComplete + successWrapperHtmlEnd, 'insertAfter');
                    }
                },
                onComplete: function(id, name, responseJSON, xhr) {
                    if (responseJSON.data) {
                        var tableId = variables.datatablePrefix + params.table;
                        var table = variables.tables[tableId]; // get the DataTables api
                        if (typeof table.ajax.url() == 'function') {
                            table.clearPipeline().draw(false); // update clearPipeline to reset only current table // no pipeline: table.ajax.reload();
                        } else {
                            if (responseJSON.reupload) {
                                var row = table.row('#' + responseJSON.row);
                                row.data(responseJSON.data).draw(false);
                                $(row.node()).removeClass('selected');
                                variables.rows_selected[tableId] = [];
                            } else {
                                table.row.add(responseJSON.data).draw(false);
                            }
                        }
                    }

                    if (responseJSON.updateRows) {
                        $.each(responseJSON, function(key) {
                            if (responseJSON[key] && typeof responseJSON[key] == 'object' && responseJSON[key].updateTable) {
                                var tableId = variables.datatablePrefix + key;
                                var table = variables.tables[tableId]; // get the api
                                if (responseJSON[key].ajax && typeof table.ajax.url() == 'function') {
                                    table.clearPipeline().draw(false); // update clearPipeline to reset only current table // no pipeline: table.ajax.reload();
                                } else if (responseJSON[key].data) {
                                    table.clear().rows.add(responseJSON[key].data).draw(false);
                                } else {
                                    // datatable initialized with DOM data so I can't use ajax to reload the data: table.ajax.url(data[key].url).load();
                                }

                                $('#' + tableId + ' tbody input[type="checkbox"]:checked').trigger('click');
                                variables.rows_selected[tableId] = [];
                            }
                        });
                    }
                },
                onSubmit: function(id, name) {
                    var button = $('#' + params.id);
                    var options = {
                        id: button.data('datatablesId'),
                    };

                    if (params.form) {
                        for (var i = 0, elements = $('#' + params.table + '-form')[0].elements; i < elements.length; i++) {
                            if (elements[i].name && elements[i].name != 'qqfile' && elements[i].name != '_token') {
                                options[elements[i].name] = elements[i].value;
                            }
                        }
                    }

                    if (!this._options.multiple) {
                        button.addClass('disabled');
                    }

                    if (params.reupload) {
                        options.reupload = true
                        var tableId = variables.datatablePrefix + params.table;
                        if (variables.rows_selected[tableId]) {
                            options.row = variables.rows_selected[tableId][0];
                        }
                    }

                    this.setParams(options);
                },
                onSubmitted: function(id, name) {
                    ajax_clear_messages(params.headerWrapper, alertSuccessClass);
                    if (!$('#upload-progress-bar-container').length) {
                        $(progressBarHtml).insertAfter(params.headerWrapper);
                    }
                },
                onError: function(id, name, errorReason, xhr) {
                    if (xhr && xhr.responseText) {
                        var response = $.parseJSON(xhr.responseText);
                        if (response.refresh) { // VerifyCsrfToken exception
                            this.cancelAll();
                            window.location.reload(true);
                        }
                    }

                    var msg = errorWrapperHtmlStart + glyphiconWarning + errorReason + ' [<strong>' + name + '</strong>]' + errorWrapperHtmlEnd;
                    ajax_message(params.headerWrapper, msg, 'insertAfter');
                },
            },
        };

        $.extend(defaultConfig, config);

        var uploader = new qq.FineUploaderBasic(defaultConfig);
    }

    function formatSize(bytes, sizeSymbols) {
        var i = -1;
        do {
            bytes = bytes / 1024;
            i++;
        } while (bytes > 1023);

        return Math.max(bytes, 0.1).toFixed(1) + sizeSymbols[i];
    }

    function ajaxify(params) {
        var deferred = $.Deferred();

        ajax_abort(params.that);
        if (!params.skipLock) {
            ajax_lock(params.that);
        }

        if (params.method == 'get') {
            var jqxhr = $.getq(params.queue, params.action, params.data);
        } else {
            var jqxhr = $.postq(params.queue, params.action, params.data);
        }

        jqxhr.done(function(data, status, xhr) {
            var that = params.that;

            $.each(data, function(key) {
                if (data[key] && typeof data[key] == 'object' && (data[key].updateTable || data[key].reloadTable)) {
                    var tableId = variables.datatablePrefix + key;
                    if (data[key].updateTable) {
                        that = $('#' + tableId).closest(ajaxLockClass);
                        var table = variables.tables[tableId]; // get the api
                        if (data[key].ajax && typeof table.ajax.url() == 'function') {
                            table.clearPipeline().draw(false); // update clearPipeline to reset only current table // no pipeline: table.ajax.reload();
                        } else if (data[key].data) {
                            table.clear().rows.add(data[key].data).draw(false);
                        } else {
                            // datatable initialized with DOM data so I can't use ajax to reload the data: table.ajax.url(data[key].url).load();
                        }
                    } else if (data[key].reloadTable) {
                        deferred.resolve(data[key]);
                    }

                    $('#' + tableId + ' tbody input[type="checkbox"]:checked').trigger('click');
                    variables.rows_selected[tableId] = [];

                    if (data.showTable) {
                        $('#' + tableId).closest(variables.datatableWarapper).removeClass('table-invisible');
                    }
                }
            });

            if (data.redirect) {
                window.location.href = data.redirect;
            } else if (data.refresh) {
                window.location.reload(true);
            } else {
                if (!params.skipLock) {
                    ajax_unlock(params.that);
                }
                ajax_reset(params.that, data);
                if (data.success) {
                    if (data.closePopup) {
                        params.that = that;
                        $.magnificPopup.close();
                        ajax_unlock(params.that);
                    }

                    if (data.enable) {
                        $.each(data.enable, function(key, value) {
                            $('#' + value).removeClass('disabled');
                        });
                    }

                    if (data.disable) {
                        $.each(data.disable, function(key, value) {
                            $('#' + value).addClass('disabled');
                        });
                    }

                    if (data.hide) {
                        $.each(data.hide, function(key, value) {
                            $('#' + value).addClass('hidden');
                        });
                    }

                    if (params.function && $.isFunction(params.function)) {
                        var functionParams = {};
                        $.each(params.functionParams, function(key, value) {
                            if (typeof data[value] !== 'undefined') {
                                functionParams[value] = data[value];
                            }
                        });

                        params.function(functionParams);
                    }

                    if (data.success !== true) {
                        ajax_success_text(params.that, data.success);
                    }
                }

                if (data.errors) {
                    if (!params.skipErrors) {
                        ajax_error(params.that, data);
                    }
                }
            }
        });

        jqxhr.fail(function(xhr, textStatus, errorThrown) {
            if (textStatus != 'abort' && !params.skipErrors) {
                ajax_unlock(params.that);
                if (xhr.status == 422) { // laravel response for validation errors
                    ajax_error_validation(params.that, xhr.responseJSON);
                } else {
                    ajax_error_text(params.that, textStatus + ': ' + errorThrown);
                }
            }
        });

        return deferred.promise();
    };

    function ajax_reset_form(form, excluded, included) {
        var inputs = form.find('input:not(:button, :submit, :reset, :radio, :checkbox, [type=hidden]), select, textarea');
        var options = form.find('input:radio, input:checkbox');

        if (included) {
            inputs.each(function() {
                if ($(this).is(included)) {
                    $(this).val('');
                }
            });
            options.each(function() {
                if ($(this).is(included)) {
                    $(this).removeAttr('checked').removeAttr('selected');
                }
            });
        } else if (excluded) {
            inputs.not(excluded).val('');
            options.not(excluded).removeAttr('checked').removeAttr('selected');
        } else {
            inputs.val('');
            options.removeAttr('checked').removeAttr('selected');
        }
    };

    function ajax_lock(that) {
        $('[type=submit]', that).prop('disabled', true);

        that.lock_timer = window.setTimeout(function() {
            $(htmlLoading).prependTo(that.closest(ajaxLockClass)).focus();

            $(ajaxLockedClass, that).on('keydown', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var code = e.keyCode ? e.keyCode : e.which;
                if (code == 27) { // ESC
                    ajax_unlock(that);
                }
                return false;
            });
        }, variables.lock_time);
        return true;
    };

    function ajax_unlock(that) {
        window.clearTimeout(that.lock_timer);
        ajax_clear_messages(that);
        $('[type=submit]', that).prop('disabled', false);
        $(ajaxLockedClass, that).remove();
        return true;
    };

    function ajax_abort(that) {
        if (/^sync-/.test(that.data('ajaxQueue')) && $.ajaxq.isRunning(that.data('ajaxQueue'))) {
            $.ajaxq.abort(that.data('ajaxQueue'));
        }
    };

    function ajax_message(that, msg, method) {
        var method = method || 'insertBefore';
        var $messages;

        var container = that.data('alertContainer');
        if (container) {
            method = 'prependTo';
            that = $('.' + container);
            alertMessagesHtmlStart = alertMessagesAbsoluteHtmlStart;
        }

        $messages = that.prev(alertMessagesClass);
        if ($messages.length > 0) {
            $messages.append(msg)
        } else {
            $messages = that.next(alertMessagesClass);
            if ($messages.length > 0) {
                $messages.append(msg)
            } else {
                $(alertMessagesClass).remove();
                $messages = $(alertMessagesHtmlStart + msg + alertMessagesHtmlEnd);
                $messages[method](that);
            }
        }

        if (!isElementInViewport($messages[0])) {
            $.scrollTo($messages[0], { offset: -20 });
            $('.mfp-wrap').scrollTo($messages[0], { offset: -20 });
        }
    };

    function ajax_clear_messages(that, group) {
        if (group) {
            that.prev(alertMessagesClass).find(group).remove();
            that.next(alertMessagesClass).find(group).remove();
        } else {
            that.prev(alertMessagesClass).empty();
            that.next(alertMessagesClass).empty();
        }
    };

    function ajax_clear_datatables_messages(that, group) {
        if (group) {
            that.find(alertMessagesClass).find(group).remove();
        } else {
            that.find(alertMessagesClass).empty();
        }
    };

    function ajax_clear(that) {
        $(glyphiconRemoveSpan, that).filter(function() {
            return !$(this).parent().is('button');
        }).remove();
        $(formGroupClass, that).removeClass(hasErrorClasses);
    };

    function ajax_error_validation(that, data) {
        ajax_clear(that);

        var msg = '';

        if (typeof data == 'object') {
            msg += errorWrapperHtmlStart + errorMessageHtmlStart;
            for (var key in data) {
                if (~key.indexOf('[]')) {
                    var $input = $('input[name="' + key + '"]');
                } else if (~key.indexOf('.')) {
                    var keys = key.split('.');
                    var $input = $('#input-' + keys[0] + '-' + (parseInt(keys[1]) + 1));
                } else {
                    var $input = $('#input-' + key);
                }

                if ($input.next().hasClass(inputGroupAddonClass) || $input.next().hasClass(inputGroupBtnClass)) {
                    $input.after(glyphiconRemove).closest(formGroupClass).addClass(hasErrorClasses);
                } else {
                    if ($input.closest('form').hasClass('form-inline')) {
                        $input.closest(formGroupClass).addClass(hasErrorClass);
                    } else {
                        $input.closest(formGroupClass).addClass(hasErrorClass).append(glyphiconRemove);
                    }
                }

                for (var i = 0; i < data[key].length; i++) {
                    msg += errorListHtmlStart + glyphiconWarning + data[key][i] + errorListHtmlEnd;
                }
            }
            msg += errorMessageHtmlEnd + errorWrapperHtmlEnd;
        }

        ajax_message(that, msg);
    };

    function ajax_reset(that, data) {
        var excluded = [];
        var included = [];
        var i;

        if (data.resetOnly) {
            for (i = 0; i < data.resetOnly.length; i++) {
                included.push('#input-' + data.resetOnly[i]);
            }
            if (included.length) {
                ajax_reset_form(that, null, included.join());
            }
        } else if (data.resetExcept) {
            for (i = 0; i < data.resetExcept.length; i++) {
                excluded.push('#input-' + data.resetExcept[i]);
            }
            if (excluded.length) {
                ajax_reset_form(that, excluded.join());
            }
        } else if (data.reset) {
            ajax_reset_form(that);
        }

        if (data.resetMultiselect) {
            $.each(data.resetMultiselect, function(key, value) {
                var $multiselect = $('#' + key);

                if ($.inArray('empty', value) !== -1) {
                    $multiselect.empty();
                }

                if ($.inArray('disable', value) !== -1) {
                    $multiselect.multiselect('disable');
                }

                if ($.inArray('refresh', value) !== -1) {
                    $multiselect.multiselect('refresh');
                }
            });
        }

        if (data.resetEditor) {
            $.each(CKEDITOR.instances, function(key) {
                CKEDITOR.instances[key].setData();
            });
        }
    }

    function ajax_error(that, data) {
        ajax_clear(that);

        var msg = '';
        if (typeof data == 'object') {
            if (data.errors) {
                msg += errorWrapperHtmlStart + errorMessageHtmlStart;
                for (var i = 0; i < data.errors.length; i++) {
                    msg += errorListHtmlStart + glyphiconWarning + data.errors[i] + errorListHtmlEnd;
                }
                msg += errorMessageHtmlEnd + errorWrapperHtmlEnd;
            }

            if (data.ids) {
                for (var i = 0; i < data.ids.length; i++) {
                    if ($('#input-' + data.ids[i]).next().hasClass(inputGroupAddonClass) || $('#input-' + data.ids[i]).next().hasClass(inputGroupBtnClass)) {
                        $('#input-' + data.ids[i]).after(glyphiconRemove).closest(formGroupClass).addClass(hasErrorClasses);
                    } else {
                        if ($('#input-' + data.ids[i]).closest('form').hasClass('form-inline')) {
                            $('#input-' + data.ids[i]).closest(formGroupClass).addClass(hasErrorClass);
                        } else {
                            $('#input-' + data.ids[i]).closest(formGroupClass).addClass(hasErrorClass).append(glyphiconRemove);
                        }
                    }
                }
            }
        }

        ajax_message(that, msg);
    };

    function ajax_error_text(that, msg) {
        ajax_clear(that);
        msg = errorWrapperHtmlStart + glyphiconWarning + msg + errorWrapperHtmlEnd;
        ajax_message(that, msg);
    };

    function ajax_success_text(that, msg) {
        ajax_clear(that);
        msg = successWrapperHtmlStart + msg + successWrapperHtmlEnd;
        ajax_message(that, msg);
    };

    function datatablesColumns(id, data) {
        var columns = [];

        $.each(data, function(key, value) {
            var render;

            if (value.checkbox) {
                render = function (data, type, full, meta) {
                    return '<input type="checkbox">';
                }
            } else if (value.data) {
                render = {
                    _: 'display',
                };
                render[value.data.type] = value.data.type;
            } else {
                render = null;
            }

            columns.push({
                data: value.id,
                title: (value.checkbox ? '<input type="checkbox" value="1" name="check-' + id + '" id="input-check-' + id + '">' : value.name),
                searchable: (value.search ? true : false),
                orderable: (typeof value.order !== 'undefined' ? false : true),
                className: (value.class ? value.class : ''),
                width: value.width ? value.width : value.checkbox ? '1.25em' : null,
                render: render,
            });
        });

        return columns;
    }

    // Updates "Select all" checkbox in a datatable
    function datatablesUpdateCheckbox(tableId) {
        var table = $('#' + tableId);
        var $checkbox_all = $('tbody input[type="checkbox"]', table);
        var $checkbox_checked = $('tbody input[type="checkbox"]:checked', table);
        var checkbox_select_all = $('thead input[type="checkbox"]', table).get(0);
        var $tableWrapper = table.closest(variables.datatableWarapper);

        if ($checkbox_checked.length === 0) { // If none of the checkboxes are checked
            $tableWrapper.find(variables.jsDestroyHook).addClass('disabled');
            $tableWrapper.find(variables.jsEditHook).addClass('disabled');
            $tableWrapper.find(variables.jsTestHook).addClass('hidden');
            $tableWrapper.find(variables.jsPrintHook).addClass('hidden');
            $tableWrapper.find(variables.jsSendHook).addClass('hidden');
            $tableWrapper.find(variables.jsMultipleHook).addClass('hidden');
            $tableWrapper.find(variables.jsActivateHook).addClass('hidden');
            $tableWrapper.find(variables.jsReuploadHook).addClass('disabled');
            $tableWrapper.find(variables.jsSendProfileHook).addClass('hidden');
            $tableWrapper.find(variables.jsCancelRentalContractHook).addClass('hidden');

            if (checkbox_select_all) {
                checkbox_select_all.checked = false;
                if ('indeterminate' in checkbox_select_all) {
                    checkbox_select_all.indeterminate = false;
                }
            }
        } else {
            $tableWrapper.find(variables.jsDestroyHook).removeClass('disabled');
            if ($checkbox_checked.length == 1) {
                $tableWrapper.find(variables.jsEditHook).removeClass('disabled');
                $tableWrapper.find(variables.jsTestHook).removeClass('hidden');
                $tableWrapper.find(variables.jsPrintHook).removeClass('hidden');
                $tableWrapper.find(variables.jsSendHook).removeClass('hidden');
                $tableWrapper.find(variables.jsActivateHook).removeClass('hidden');
                $tableWrapper.find(variables.jsMultipleHook).addClass('hidden');
                $tableWrapper.find(variables.jsReuploadHook).removeClass('disabled');
            } else {
                if ($checkbox_checked.length > 1) {
                  $tableWrapper.find(variables.jsMultipleHook).removeClass('hidden');
                  $tableWrapper.find(variables.jsActivateHook).removeClass('hidden');
                }

                $tableWrapper.find(variables.jsEditHook).addClass('disabled');
                $tableWrapper.find(variables.jsTestHook).addClass('hidden');
                $tableWrapper.find(variables.jsPrintHook).addClass('hidden');
                $tableWrapper.find(variables.jsSendHook).addClass('hidden');
                $tableWrapper.find(variables.jsReuploadHook).addClass('disabled');
                $tableWrapper.find(variables.jsSendProfileHook).addClass('hidden');
            }

            if (checkbox_select_all) {
                if ($checkbox_checked.length === $checkbox_all.length) { // If all of the checkboxes are checked
                    checkbox_select_all.checked = true;
                    if ('indeterminate' in checkbox_select_all) {
                        checkbox_select_all.indeterminate = false;
                    }
                } else { // If some of the checkboxes are checked
                    checkbox_select_all.checked = true;
                    if ('indeterminate' in checkbox_select_all) {
                        checkbox_select_all.indeterminate = true;
                    }
                }
            }
        }
    }

    function datatables(params) {
        $.each(params, function(id, param) {
            var tableId = variables.datatablePrefix + id;
            variables.rows_selected[tableId] = [];

            var checkbox = false;
            $.each(param.columns, function(key, value) {
                if (value.checkbox) {
                    checkbox = true;
                    return false;
                }
            });

            variables.tables[tableId] = $('#' + tableId).DataTable({
                dom: param.dom ? param.dom : "<'clearfix'<'dataTableL'l><'dataTableF'f>>tr<'clearfix'<'dataTableI'i><'dataTableP'p>>",
                stateSave: true,
                stateDuration: -1,
                deferRender: true,
                autoWidth: false,
                retrieve: true,
                rowId: 'id',
                defaultContent: '',
                language: {
                    url: variables.datatablesLanguage
                },
                paging: param.count > variables.datatablesPaging ? true : false,
                searchDelay: param.ajax ? variables.datatablesSearchDelay : 100,
                serverSide: param.ajax ? true : false,
                pagingType: variables.datatablesPagingType[param.size],
                pageLength: variables.datatablesPageLength[param.size],
                lengthMenu: variables.datatablesLengthMenu[param.size],
                order: isNaN(param.orderByColumn) ? [] : [[param.orderByColumn, param.order]],
                ajax: param.ajax ? ajaxifyDatatables({ url: param.url }) : null,
                data: param.data ? param.data : null,
                columns: datatablesColumns(id, param.columns),
                createdRow: function(row, data, dataIndex) {
                    if (checkbox) {
                        if ($.inArray(row.id, variables.rows_selected[tableId]) !== -1) {
                            $(row).find('input[type="checkbox"]').prop('checked', true);
                            $(row).addClass('selected');
                        }
                    } else {
                        if (!$(row).find('input[type="checkbox"]').length) {
                            $(row).addClass('non-selectable');
                        }
                    }

                    if (data.temp_password) {
                        $(row).addClass('new-profile');
                    }

                    if (data.deleted) {
                        $(row).addClass('deleted');
                    }
                },
                drawCallback: checkbox ? function(settings) {
                    // Update state of "Select all" checkbox
                    datatablesUpdateCheckbox(tableId);
                } : null,
                footerCallback: param.footer ? function(settings, json) {
                    var api = this.api();
                    var total;

                    $.each(param.footer.columns, function(column, value) {
                        if (value.sum) {
                            total = api.column(column).data().reduce(function(a, b) {
                                return (parseFloat((a || 0).toString().replace(',', '')) + parseFloat((b || 0).toString().replace(',', '')));
                            }, 0).toFixed(2).replace('.00', '');

                            $(api.column(column).footer()).find('span').html(total); // $(tfoot).find('th').eq(column)
                        }

                        if (value.count) {
                            var span = $(api.column(column).footer()).find('span');
                            var total = span.data('total');
                            var count = api.column(column).data().count();
                            if (total) {
                              span.html(count + ' (' + ((count / total) * 100).toFixed(2) + '%)');
                            } else {
                              span.html(count);
                            }
                        }

                        if (value.filter) {
                            $.each(value.filter, function(key, filter) {
                                total = api.column(column).data().filter(function(val, index) {
                                    if (/<[a-zA-Z][\s\S]*>/i.test(val)) {
                                        val = $(val).text();
                                    }

                                    if (filter.comparison == '=') {
                                        return val == filter.value ? true : false;
                                    }
                                }).count();

                                if (filter.selector) {
                                    $(api.column(column).footer()).find(filter.selector).find('span').html(total);
                                } else {
                                    $(api.column(column).footer()).find('span').html(total);
                                }
                            });
                        }
                    });

                    $(api.table().footer()).show();
                } : null,
            });
        });

        // Register an API method that will empty the pipelined data, forcing an Ajax
        // fetch on the next draw (i.e. `table.clearPipeline().draw()`)
        $.fn.dataTable.Api.register('clearPipeline()', function() {
            return this.iterator('table', function(settings) {
                settings.clearCache = true;
            });
        });

        $(document).on('preInit.dt', function(e, settings) {
            var api = new $.fn.dataTable.Api(settings);
            var $table = api.table().node();
            var tableId = $($table).attr('id');
            var $wrapper = $($table).closest(variables.datatableWarapper);
            var $filter = $($wrapper).find(variables.filterClass + ' input');
            $filter.off('keyup.DT input.DT'); // disable global search events except: search.DT paste.DT cut.DT
            $filter.on('keyup.DT input.DT', $.debounce(settings.searchDelay, function(e) {
                $('#' + tableId + ' tbody input[type="checkbox"]:checked').trigger('click');
                variables.rows_selected[tableId] = [];
                api.search(this.value).draw();
            }));
        });
    }

    function ajaxifyDatatables(params) {
        var params = $.extend({
            pipeline: variables.datatablesPipeline, // number of pages to cache/pipeline
            url: '',  // script url
            data: null, // function or object with parameters to send to the server matching how `ajax.data` works in DataTables
            method: 'get' // Ajax HTTP method
        }, params);

        // Private variables for storing the cache
        var cacheLower = -1;
        var cacheUpper = null;
        var cacheLastRequest = null;
        var cacheLastJson = null;

        return function (request, callback, settings) {
            var ajax = false;
            var requestStart = request.start;
            var drawStart = request.start;
            var requestLength = request.length;
            if (requestLength < 0) { // all
                requestLength = 0;
            }
            var requestEnd = requestStart + requestLength;

            if (settings.clearCache) { // API requested that the cache be cleared
                ajax = true;
                settings.clearCache = false;
            } else if (requestLength == 0 || cacheLower < 0 || requestStart < cacheLower || requestEnd > cacheUpper) { // outside cached data - need to make a request
                ajax = true;
            } else if (JSON.stringify(request.order) !== JSON.stringify(cacheLastRequest.order) || JSON.stringify(request.columns) !== JSON.stringify(cacheLastRequest.columns) || JSON.stringify(request.search) !== JSON.stringify(cacheLastRequest.search)) { // properties changed (ordering, columns, searching)
                ajax = true;
            }

            // Store the request for checking next time around
            cacheLastRequest = $.extend(true, {}, request);

            if (ajax) { // Need data from the server
                if (requestStart < cacheLower) {
                    requestStart = requestStart - (requestLength * (params.pipeline - 1));
                    if (requestStart < 0) {
                        requestStart = 0;
                    }
                }

                cacheLower = requestStart;
                cacheUpper = requestStart + (requestLength * params.pipeline);

                request.start = requestStart;
                request.length = requestLength * params.pipeline;

                // Provide the same `data` options as DataTables.
                if ($.isFunction(params.data)) {
                    // As a function it is executed with the data object as an arg
                    // for manipulation. If an object is returned, it is used as the
                    // data object to submit
                    var d = params.data(request);
                    if (d) {
                        $.extend(request, d);
                    }
                } else if ($.isPlainObject(params.data)) { // As an object, the data given extends the default
                    $.extend(request, params.data);
                }

                var that = $(this).closest(ajaxLockClass);

                var result = ajaxify({
                    that: that,
                    method: params.method,
                    queue: that.data('ajaxQueue'),
                    action: params.url,
                    data: request
                });

                result.done(function(data) {
                    cacheLastJson = $.extend(true, {}, data);
                    if (cacheLower != drawStart) {
                        data.data.splice(0, drawStart - cacheLower);
                    }

                    if (requestLength > 0) {
                        data.data.splice(requestLength, data.data.length);
                    }

                    if (data.search) {
                        var api = new $.fn.dataTable.Api(settings);
                        var $table = api.table().node();
                        var $wrapper = $($table).closest(variables.datatableWarapper);
                        var $filter = $($wrapper).find(variables.filterClass + ' input');
                        $filter.focus();
                    }
                    callback(data);
                });
            } else {
                var json = $.extend(true, {}, cacheLastJson);
                json.draw = request.draw; // Update the echo for each response
                json.data.splice(0, requestStart - cacheLower);
                if (requestLength > 0) {
                    json.data.splice(requestLength, json.data.length);
                }
                callback(json);
            }
        }
    };

    var Password = {
        pattern: /[a-zA-Z0-9_\-\+\.\!\@\#\$\%\&\*\(\)\,]/,

        getRandomByte: function() {
            if (window.crypto && window.crypto.getRandomValues) {
                var result = new Uint8Array(1);
                window.crypto.getRandomValues(result);
                return result[0];
            } else if (window.msCrypto && window.msCrypto.getRandomValues) {
                var result = new Uint8Array(1);
                window.msCrypto.getRandomValues(result);
                return result[0];
            } else {
                return Math.floor(Math.random() * 256);
            }
        },

        generate: function(length) {
            return Array.apply(null, { 'length': length }).map(function() {
                var result;
                while(true) {
                    result = String.fromCharCode(this.getRandomByte());
                    if (this.pattern.test(result)) {
                        return result;
                    }
                }
            }, this).join('');
        }
    };

    return {run: run, variables: variables, datatables: datatables, ajaxify: ajaxify}
}();
