(function ($) {
    console.debug('[live-search] script loaded');
    // jQuery-based live search with debounce and loading indicator
    function debounce(fn, wait) {
        let t;
        return function () {
            const args = arguments;
            clearTimeout(t);
            t = setTimeout(function () { fn.apply(null, args); }, wait);
        };
    }

    function fetchResults(paramsStr) {
        let url = '/documents';
        if (paramsStr) {
            url += paramsStr.indexOf('?') === 0 ? paramsStr : ('?' + paramsStr);
        }

        const $tbody = $('#documents-tbody');
        const $pagination = $('#documents-pagination');
        const $spinner = $('#live-search-spinner');
        $spinner.show();

        console.debug('[live-search] ajax url (page) =', url);
        // build AJAX endpoint explicitly
        const ajaxUrl = '/documents/list' + (url.indexOf('?') !== -1 ? url.substring(url.indexOf('?')) : '');
        console.debug('[live-search] ajaxUrl =', ajaxUrl);
        return $.ajax({
            url: ajaxUrl,
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        }).done(function (html) {
            // parse returned HTML and extract fragments
            const $doc = $('<div>').html(html);
            const $newTbody = $doc.find('#documents-tbody');
            const $newPagination = $doc.find('#documents-pagination');
            const $newPaginationTop = $doc.find('#documents-pagination-top');
            if ($newTbody.length && $tbody.length) $tbody.html($newTbody.html());
            if ($newPagination.length && $pagination.length) $pagination.html($newPagination.html());
            if ($newPaginationTop.length) {
                $('#documents-pagination-top').html($newPaginationTop.html());
            }

            // update URL to reflect query (strip page param)
            const qOnly = paramsStr ? paramsStr.replace(/^\?/, '').split('&').filter(function (p) { return !p.startsWith('page='); }).join('&') : '';
            const newUrl = '/documents' + (qOnly ? ('?' + qOnly) : '');
            history.replaceState({}, '', newUrl);
        }).fail(function () {
            console.warn('Live search request failed');
        }).always(function () {
            $spinner.hide();
        });
    }

    $(function () {
        const $input = $('input[name="q"]');
        if (!$input.length) return;

        // add a small spinner element next to input if missing
        if ($('#live-search-spinner').length === 0) {
            $input.after('<div id="live-search-spinner" style="display:none;margin-left:8px;vertical-align:middle">' +
                '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        }

        const doFetch = debounce(function () {
            const q = $input.val() || '';
            console.debug('[live-search] trigger fetch q=', q);
            fetchResults('q=' + encodeURIComponent(q));
        }, 300);

        $input.on('input', doFetch);
        // also listen to keyup as a fallback in some browsers
        $input.on('keyup', doFetch);

        // clear button (target by id)
        $(document).on('click', '#search-clear-btn', function (e) {
            e.preventDefault();
            console.debug('[live-search] clear clicked');
            $input.val('');
            doFetch();
            return false;
        });

        // pagination links (both top and bottom)
        $(document).on('click', '#documents-pagination a, #documents-pagination-top a', function (e) {
            e.preventDefault();
            const href = $(this).attr('href');
            const query = '?' + (href.split('?')[1] || '');
            fetchResults(query);
        });

        // if the input already has a value on page load, trigger search once
        if ($input.val()) {
            console.debug('[live-search] initial input value present, fetching');
            doFetch();
        }
    });
})(jQuery);
