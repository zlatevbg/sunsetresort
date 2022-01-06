//=require plugins/*.js
//=require vendor/plugins/combine/*.js
//=require vendor/headroom.min.js

var unikat = function() {
    'use strict';

    var variables = {
        tables: [],
        rows_selected: {},
        lock_time: 0,
        jsPopupHook: '.js-popup',
        jsDismissHook: '.js-dismiss',
        jsCreateHook: '.js-create',
        jsEditHook: '.js-edit',
        jsDestroyHook: '.js-destroy',
        filterClass: '.dataTables_filter',
        datatablePrefix: 'datatable',
        datatableWarapper: '.dataTableWrapper',
    };

    var htmlLoading;

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
    var buttonCloseClass = 'button.close';

    function run() {
        htmlLoading = '<div tabindex="-1" class="ajax-locked"><div><div><img src="' + variables.loadingImageSrc + '" alt="' + variables.loadingImageAlt + '" title="' + variables.loadingImageTitle + '">' + variables.loadingText + '</div></div></div>';
        errorMessageHtmlStart = variables.ajaxErrorMessage + errorMessageHtmlStart;

        if (!variables.is_auth) {
            var $menu = $('#menu');

            $('#mobile-nav').click(function() {
                $('#mobile-profile').removeClass('active');
                $('#profile-menu').removeClass('profile-open');
                $('#header').removeClass('profile-open');

                $(this).toggleClass('active');
                $menu.toggleClass('menu-open');
                $('#header').toggleClass('nav-open');
            });

            $('#mobile-profile').click(function() {
                $('#mobile-nav').removeClass('active');
                $menu.removeClass('menu-open');
                $('#header').removeClass('nav-open');

                $(this).toggleClass('active');
                $('#profile-menu').toggleClass('profile-open');
                $('#header').toggleClass('profile-open');
            });

            $('.tooltip-notices').qtip({
                position: {
                    viewport: $(window),
                    my: 'top center',
                    at: 'bottom center',
                },
                content: {
                    title: function(event, api) {
                        return $(this).attr('title');
                    },
                    text: function(event, api) {
                        return $(this).find('.tooltip-data');
                    },
                },
                show: {
                    ready: true,
                    solo: true,
                    delay: 200,
                },
                hide: {
                    fixed: true,
                    event: false,
                    delay: 200,
                },
                style: {
                    tip: {
                        width: 10,
                        height: 10,
                    },
                    classes: 'tooltip-notices ajax-lock',
                },
            });

            $(document).on('mouseenter', '.tooltip', function() {
                $(this).qtip({
                    position: {
                        viewport: $(window),
                        my: 'top center',
                        at: 'bottom center',
                    },
                    show: {
                        ready: true,
                    },
                    hide: {
                        fixed: true,
                        delay: 100,
                    },
                    style: {
                        tip: {
                            width: 10,
                            height: 5,
                        },
                    },
                });
            });

            enquire.register("screen and (min-width:960px)", {
                setup: function() {
                    $('#header').headroom({
                        offset: variables.headroomMobileOffset,
                    });
                },
                match: function() {
                    $('#header').headroom('destroy').removeData('headroom');
                    $('#fixed-nav').headroom('destroy').removeData('headroom');
                    $('#fixed-nav').headroom({
                        offset: variables.headroomOffset,
                    });
                },
                unmatch: function() {
                    $('#fixed-nav').headroom('destroy').removeData('headroom');
                    $('#header').headroom('destroy').removeData('headroom');
                    $('#header').headroom({
                        offset: variables.headroomMobileOffset,
                    });
                },
            }, true);

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
                callbacks: {
                    open: function() {
                        $('.headroom').addClass('headroom--top');
                    },
                    close: function() {
                        $('.headroom').removeClass('headroom--top');
                    },
                },
            });

            var magnificPopupOptions = {
                type: 'ajax',
                key: 'popup-form',
                focus: ':input:visible',
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

            $(document).on('click', variables.jsDismissHook, function(e) {
                e.preventDefault();

                var that = $(this);

                ajaxify({
                    that: that,
                    method: 'get',
                    queue: $(this).data('ajaxQueue'),
                    action: $(this).attr('href'),
                    skipErrors: true,
                    functionParams: ['qtip'],
                    function: function(params) {
                        $(params.qtip).qtip('api').destroy();
                    }
                });

                return false;
            });

            $(document).on('click', variables.jsPopupHook, function(e) {
                e.preventDefault();

                var that = $(this);
                var src = that.attr('href');

                $.magnificPopup.open($.extend(magnificPopupOptions, {
                    items: {
                        src: src,
                    },
                    closeOnBgClick: true,
                    callbacks: {
                        parseAjax: function(mfpResponse) {
                          mfpResponse.data = typeof mfpResponse.data == 'string' ? mfpResponse.data : mfpResponse.data[0];
                        },
                        ajaxContentAdded: function() {
                            $('.headroom').addClass('headroom--top').addClass('headroom--unpinned');

                            if (typeof unikat.callback == 'function') {
                                unikat.callback();
                            }

                            if (that.hasClass('js-notice-unread')) {
                                that.removeClass('js-notice-unread');

                                $('.notices-counter').each(function() {
                                    var num = +($(this).text());
                                    if (num == 1) {
                                        $(this).hide();
                                    } else {
                                        $(this).text(--num);
                                    }
                                });
                            }

                            if (that.hasClass('js-newsletter-unread')) {
                                that.removeClass('js-newsletter-unread');
                            }
                        },
                        close: function() {
                            $('.headroom').removeClass('headroom--top').removeClass('headroom--unpinned');
                        },
                    },
                }));
            });

            $(document).on('click', variables.jsCreateHook, function(e) {
                e.preventDefault();

                var table = $(this).data('table');
                var separator = $(this).attr('href').indexOf('?') == -1 ? '?' : '&';
                var src = $(this).attr('href') + (table ? separator + 'table=' + table : '');

                $.magnificPopup.open($.extend(magnificPopupOptions, {
                    items: {
                        src: src,
                    },
                    callbacks: {
                        parseAjax: function(mfpResponse) {
                          mfpResponse.data = typeof mfpResponse.data == 'string' ? mfpResponse.data : mfpResponse.data[0];
                        },
                        ajaxContentAdded: function() {
                            $('#fixed-nav').addClass('headroom--top');

                            if (typeof $.multiselect == 'object') {
                                multiselectSetup(unikat.multiselect);
                            }

                            if (typeof unikat.magnificPopupCreateCallback == 'function') {
                                unikat.magnificPopupCreateCallback();
                            }
                        },
                        close: function() {
                            $('#fixed-nav').removeClass('headroom--top');
                        },
                    },
                }));
            });

            $(document).on('click', variables.jsDestroyHook, function(e) {
                e.preventDefault();

                ajax_clear_datatables_messages($(this).closest(variables.datatableWarapper));

                var table = $(this).data('table');
                var separator = $(this).attr('href').indexOf('?') == -1 ? '?' : '&';
                var src = $(this).attr('href') + (table ? separator + 'table=' + table : '');

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
                var separator = $(this).attr('href').indexOf('?') == -1 ? '?' : '&';

                var param = null;
                var tableId = variables.datatablePrefix + table;
                if (variables.rows_selected[tableId]) {
                    param = '/' + variables.rows_selected[tableId][0];
                }

                var src = $(this).attr('href') + param + (table ? separator + 'table=' + table : '');

                $.magnificPopup.open($.extend(magnificPopupOptions, {
                    items: {
                        src: src,
                    },
                    callbacks: {
                        parseAjax: function(mfpResponse) {
                          mfpResponse.data = typeof mfpResponse.data == 'string' ? mfpResponse.data : mfpResponse.data[0];
                        },
                        ajaxContentAdded: function() {
                            $('#fixed-nav').addClass('headroom--top');

                            if (typeof $.multiselect == 'object') {
                                multiselectSetup(unikat.multiselect);
                            }

                            if (typeof unikat.magnificPopupEditCallback == 'function') {
                                unikat.magnificPopupEditCallback();
                            }
                        },
                        close: function() {
                            $('#fixed-nav').removeClass('headroom--top');
                        },
                    },
                }));

                ajax_unlock($(this).closest(ajaxLockClass));
            });

            if (variables.datatables) {
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
                    var $wrapper = $($table).closest(variables.datatableWarapper);
                    var $filter = $($wrapper).find(variables.filterClass + ' input');
                    $filter.off('keyup.DT input.DT'); // disable global search events except: search.DT paste.DT cut.DT
                    $filter.on('keyup.DT input.DT', $.debounce(settings.searchDelay, function(e) {
                        api.search(this.value).draw();
                    }));
                });

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
                    if (e.target.tagName.toLowerCase() !== 'a' && e.target.parentElement.tagName.toLowerCase() !== 'a' && e.target.tagName.toLowerCase() !== 'img' && $(e.target).parent()) {
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

                    if (this.checked) {
                        $row.addClass('selected');
                    } else {
                        $row.removeClass('selected');
                    }

                    datatablesUpdateCheckbox(tableId);

                    e.stopPropagation(); // Prevent click event from propagating to parent
                });

                $(document).on('click', '.row-details', function() {
                    var tableId = $(this).closest('table').attr('id');
                    var table = variables.tables[tableId]; // get the api
                    var tr = $(this).closest('tr');
                    var row = table.row(tr);

                    if (row.child.isShown()) {
                        row.child.hide();
                    } else {
                        row.child(row.data().row_details, 'row-details').show();
                    }
                });
            }
        }

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dropdown-toggle').length) {
                $('.dropdown-menu').each(function() {
                    if (!$(this).hasClass('menu-static')) {
                        $(this).removeClass('active');
                        $(this).prev().removeClass('active');
                    }
                });

                $('.slidedown-menu').each(function() {
                    if (!$(this).hasClass('menu-static')) {
                        $(this).slideUp();
                    }
                });
            }

            if (!$(e.target).closest('#mobile-nav').length && !$(e.target).closest('#mobile-profile').length && !$(e.target).closest('#menu').length) {
                $('#mobile-nav').removeClass('active').children('.dropdown-toggle').removeClass('active');
                $('#menu').removeClass('menu-open');
                $('#header').removeClass('nav-open');
            }
        });

        $(document).on('click', '.dropdown-toggle', function(e) {
            e.preventDefault();

            if ($(e.target).closest('#menu').length) {
                var $nav = $('#mobile-nav .dropdown-toggle');
                $('.dropdown-toggle.active').not($(this)).not($nav[0]).removeClass('active');
            } else {
                $('.dropdown-toggle.active').not($(this)).removeClass('active');
            }

            $(this).toggleClass('active');

            var parent = $(this).parent();
            var next = $(this).next();

            $('.dropdown-menu.active').not(next[0]).removeClass('active');

            if ($('#header').hasClass('nav-open')) {
                $('.dropdown-menu').not(next[0]).removeClass('active');
                next.toggleClass('active');
            } else {
                if (next.width() > ($(window).width() - parent.offset().left)) {
                    next.addClass('dropdown-menu-right');
                }
                next.toggleClass('active');
            }
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

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
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
                if (typeof data[key] == 'object' && (data[key].updateTable || data[key].reloadTable)) {
                    var tableId = variables.datatablePrefix + key;
                    if (data[key].updateTable) {
                        that = $('#' + tableId).closest(ajaxLockClass);
                        var table = variables.tables[tableId]; // get the api
                        if (data[key].ajax && typeof table.ajax.url() == 'function') {
                            table.clearPipeline().draw(false); // update clearPipeline to reset only current table // no pipeline: table.ajax.reload();
                        } else if (data[key].data) {
                            table.clear().rows.add(data[key].data).draw();
                        } else {
                            // datatable initialized with DOM data so I can't use ajax to reload the data: table.ajax.url(data[key].url).load();
                        }
                    } else if (data[key].reloadTable) {
                        deferred.resolve(data[key]);
                    }

                    $('#' + tableId + ' tbody input[type="checkbox"]:checked').trigger('click');
                    variables.rows_selected[tableId] = [];
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
                } else if (data.errors) {
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
        $(glyphiconRemoveSpan, that).remove();
        $(formGroupClass, that).removeClass(hasErrorClasses);
    };

    function ajax_error_validation(that, data) {
        ajax_clear(that);

        var msg = '';

        if (typeof data == 'object') {
            msg += errorWrapperHtmlStart + errorMessageHtmlStart;
            for (var key in data) {
                if ($('#input-' + key).next().hasClass(inputGroupAddonClass)) {
                    $('#input-' + key).after(glyphiconRemove).closest(formGroupClass).addClass(hasErrorClasses);
                } else {
                    if ($('#input-' + key).closest('form').hasClass('form-inline')) {
                        $('#input-' + key).closest(formGroupClass).addClass(hasErrorClass);
                    } else {
                        $('#input-' + key).closest(formGroupClass).addClass(hasErrorClass).append(glyphiconRemove);
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
                    if ($('#input-' + data.ids[i]).next().hasClass(inputGroupAddonClass)) {
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
            } else if (value.render) {
                render = {
                    _: 'display',
                };
                render[value.render] = value.render;
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

    /*function datatablesColumnDefs(data) {
        var columnDefs = [];

        $.each(data, function(key, value) {
            if (value.checkbox) {
                columnDefs.push({
                    targets: key,
                    width: '1.25em',
                    searchable: (value.search ? true : false),
                    orderable: (typeof value.order !== 'undefined' ? false : true),
                    className: (value.class ? value.class : ''),
                    render: function (data, type, full, meta) {
                        return '<input type="checkbox">';
                    }
                });
            }
        });

        return columnDefs;
    }*/

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

            checkbox_select_all.checked = false;
            if ('indeterminate' in checkbox_select_all) {
                checkbox_select_all.indeterminate = false;
            }
        } else {
            $tableWrapper.find(variables.jsDestroyHook).removeClass('disabled');
            if ($checkbox_checked.length == 1) {
                $tableWrapper.find(variables.jsEditHook).removeClass('disabled');
            } else {
                $tableWrapper.find(variables.jsEditHook).addClass('disabled');
            }

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
                retrieve: true,
                autoWidth: false,
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
                // columnDefs: datatablesColumnDefs(param.columns),
                createdRow: checkbox ? function(row, data, dataIndex) {
                    if ($.inArray(row.id, variables.rows_selected[tableId]) !== -1) {
                        $(row).find('input[type="checkbox"]').prop('checked', true);
                        $(row).addClass('selected');
                    }
                } : null,
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

    return {run: run, variables: variables, datatables: datatables}
}();
