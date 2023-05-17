jQuery('document').ready(function () {
    let $labelId = jQuery('#wpforms-panel-field-settings-pipedrive_leadlabel'),
        $companyName = jQuery('#wpforms-panel-field-settings-pipedrive_companydomain'),
        $apikey = jQuery('#wpforms-panel-field-settings-pipedrive_apikey');

    if ( $labelId.length && $companyName.length && $apikey.length ) {
        if ( $companyName.val() && $apikey.val() ) {

            let url = 'https://' + $companyName.val() + '.pipedrive.com/v1/leadLabels?api_token=' + $apikey.val();

            jQuery.ajax({
                url: url,
                context: document.body
            }).done(function(responce) {

                if ( responce.success ) {
                   jQuery.each(responce.data,function(index, item) {
                       $labelId.before('<span class="lead-label-card" data-id="' + item.id + '">' + item.name + '</span>');
                   } );
                }

            });

        }
    }

    jQuery('body').on('click', '.lead-label-card', function (e){
        jQuery('#wpforms-panel-field-settings-pipedrive_leadlabel').val( jQuery(this).data('id') );
    });

});