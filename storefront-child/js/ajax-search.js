jQuery(document).ready(function($) {
    $('#cities-search-input').on('input', function() {
        var search = $(this).val();
        if (search.length < 3) {
            // min 3 symbols for search
            return;
        }
        $.ajax({
            url: cities_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'search_cities',
                nonce: cities_ajax.nonce,
                search: search
            },
            success: function(response) {
                if (response.success) {
                    $('#cities-table tbody').html(response.data.html);
                }
            }
        });
    });
});
