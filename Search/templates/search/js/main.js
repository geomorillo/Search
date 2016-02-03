jQuery(document).ready(function () {
$("#ls_query").hide();//hiding the input so it wont appear before the css is loaded
    $.post("start", function (data) {
     $("#ls_query").show();   

        jQuery('#ls_query').ajaxlivesearch({
            loaded_at: data.time,
            token: data.token,
            maxInput: data.maxInputLength,
            onResultClick: function (e, data) {
                // get the index 1 (second column) value
                var selectedOne = jQuery(data.selected).find('td').eq('0').text();

                // set the input value
                jQuery('#ls_query').val(selectedOne);

                // hide the result
                jQuery('#ls_query').trigger('ajaxlivesearch:hide_result');
            }
        });

    },'json');

});
