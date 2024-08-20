/*jQuery(document).ready(function($) {
    function calculateDateDifference() {
        var dateFrom = new Date($('#delivery_date_from').val());
        var dateTo = new Date($('#delivery_date_to').val());

        if (dateFrom && dateTo && dateFrom < dateTo) {
            var timeDiff = dateTo.getTime() - dateFrom.getTime();
            var dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)); // Difference in days

            $.ajax({
                url: ajax_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'update_cart_fee',
                    day_diff: dayDiff
                },
                dataType: 'json',
                success: function(response) {
                    console.log('AJAX Response:', response);
                    if (response && response.status === 'success') {
                        console.log('Success Message:', response.message);
                        if (response.fragments) {
                            // Update the cart fragments
                            $.each(response.fragments, function(key, value) {
                                $(key).replaceWith(value);
                            });
                        }
                    } else {
                        console.log('Error Message:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.log('Response Text:', xhr.responseText); // Log the raw response
                }
            });
        }
    }

    $('#delivery_date_from, #delivery_date_to').change(calculateDateDifference);
});
*/


/*jQuery(document).ready(function($) {
    function calculateDateDifference() {
        var dateFrom = new Date($('#delivery_date_from').val());
        var dateTo = new Date($('#delivery_date_to').val());

        if (dateFrom && dateTo && dateFrom < dateTo) {
            var timeDiff = dateTo.getTime() - dateFrom.getTime();
            var dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)); // Difference in days

            $.ajax({
                url: ajax_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'update_cart_fee',
                    day_diff: dayDiff
                },
                dataType: 'json',
                success: function(response) {
                    console.log('AJAX Response:', response);
                    if (response.status === 'success') {
                        console.log('Success Message:', response.message);
                        if (response.fragments) {
                            // Update the cart fragments
                            $.each(response.fragments, function(key, value) {
                                $(key).replaceWith(value);
                            });
                        }
                    } else {
                        console.log('Error Message:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.log('Response Text:', xhr.responseText); // Log the raw response
                }
            });
        }
    }

    $('#delivery_date_from, #delivery_date_to').change(calculateDateDifference);
});
*/








jQuery(document).ready(function($) {
    function calculateDateDifference() {
        var dateFrom = new Date($('#delivery_date_from').val());
        var dateTo = new Date($('#delivery_date_to').val());

        if (dateFrom && dateTo && dateFrom < dateTo) {
            var timeDiff = dateTo.getTime() - dateFrom.getTime();
            var dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)); // Difference in days

            $.ajax({
                url: ajax_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'update_cart_fee',
                    day_diff: dayDiff
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Trigger a refresh of the checkout fragments
                        $(document.body).trigger('update_checkout');
                    } else {
                        console.log('Error Message:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        }
    }

    $('#delivery_date_from, #delivery_date_to').change(calculateDateDifference);
});
