jQuery(function ($) {

    // $("#pc-loadMoreBtn").on("click", function () {

    //     let btn = $(this);
    //     let offset = parseInt(btn.attr("data-offset"));

    //     btn.text("Loading...");

    //     $.post(wgtData.ajax_url, {
    //         action: "pc_load_more",
    //         offset: offset,
    //         nonce: wgtData.nonce
    //     }, function (response) {

    //         if (!response.success) return;

    //         if (response.data.end === true) {
    //             btn.text("No More Participants").prop("disabled", true);
    //             return;
    //         }

    //         $("#pc-list").append(response.data.html);

    //         let newOffset = offset + 15;
    //         btn.attr("data-offset", newOffset);
    //         btn.text("Load More Participants");

    //     });
    // });
    $(document).on('click', '.pc-holder-btn', function () {

        const name = $(this).data('name');
        const title = $(this).data('title');

        const $modal = $('#certificateModal');

        $modal.find('.name').text(name);
        $modal.find('.title').text(title);

        $modal.modal('show'); // ✅ Now Bootstrap exists
    });

    $(document).on('click', '.close-modal', function () {
        $('#certificateModal').modal('hide');
    });

    jQuery(document).ready(function ($) {
        $('.add-to-cart').on('click', function (e) {
            e.preventDefault();

            // Get row data
            var $btn = $(this);
            var row = $(this).closest('.participant-row');
            var recordId = $btn.data('record-id');
            var productId = $btn.data('product-id');
            var participantId = $btn.data('participant-id');
            var quantity = row.find('.quantity-select').val();

            // AJAX request to WooCommerce
            $.ajax({
                url: wc_add_to_cart_params.ajax_url, // WooCommerce AJAX URL
                type: 'POST',
                data: {
                    action: 'add_participant_to_cart',
                    participant_id: participantId,
                    product_id: productId,
                    record_id: recordId,
                    quantity: quantity,
                },
                success: function (response) {
                    if (response.success) {
                        $btn.replaceWith('<a href="' + wc_add_to_cart_params.cart_url + '" class="go-to-cart-button button"><span class="cart-icon">🛒</span>Go to Cart</a>');
                    } else {
                        alert('Error adding to cart.');
                    }
                }
            });
        });
    });

    let typingTimer;
    const typingDelay = 500; // 0.5s delay after typing stops

    function fetchParticipants(page = 1, search = '', record_id) {
        $.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'GET',
            data: {
                action: 'load_participants',
                record_id: record_id,
                page: page,
                search: search
            },
            dataType: 'json',
            success: function (response) {
                $('#pc-list').html(response.html);
                $('.pc-pagination-wrap').html(response.pagination);
                $('.pc-total-count').html(response.total_count);

                // Set record_id on pagination for future clicks
                $('.pc-pagination').attr('data-record-id', record_id);

                // Optional: update URL query params
                const newUrl = new URL(window.location.href);
                newUrl.searchParams.set('page', page);
                newUrl.searchParams.set('search', search);
                history.pushState({}, '', newUrl);
            }
        });
    }

    // Trigger AJAX after typing stops
    $(document).on('input', '.pc-search-input', function () {
        clearTimeout(typingTimer);
        const search = $(this).val();
        const record_id = $(this).data('record-id');
        typingTimer = setTimeout(function () {
            fetchParticipants(1, search, record_id); // reset to first page
        }, typingDelay);
    });

    // Delegate click on pagination links
    $(document).on('click', '.pc-pagination a', function (e) {
        e.preventDefault();

        const record_id = $(this).closest('.pc-pagination').data('record-id');
        const href = $(this).attr('href');
        const urlParams = href.includes('?') ? new URLSearchParams(href.split('?')[1]) : new URLSearchParams();
        const page = urlParams.get('page') || 1;
        const search = $('.pc-search-input').val();

        fetchParticipants(page, search, record_id);
    });

    // Handle back/forward browser buttons
    window.addEventListener('popstate', function () {
        const params = new URLSearchParams(window.location.search);
        const page = params.get('page') || 1;
        const search = params.get('search') || '';
        const record_id = $('.pc-search-input').data('record-id');
        fetchParticipants(page, search, record_id);
    });

    $('.pc-country-select').on('change', function () {
        const country = $(this).val();

        $.ajax({
            url: wc_add_to_cart_params.ajax_url,
            type: 'POST',
            data: {
                action: 'pc_set_shipping_country',
                country: country
            },
            success: function () {
                // Trigger WooCommerce refresh (cart/checkout)
                $(document.body).trigger('update_checkout');
                $(document.body).trigger('wc_fragment_refresh');
            }
        });
    });

});
