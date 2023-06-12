jQuery('document').ready(function () {
    let $labelId = jQuery('#wpforms-panel-field-settings-pipedrive_leadlabel'),
        $companyName = jQuery('#wpforms-panel-field-settings-pipedrive_companydomain'),
        $apikey = jQuery('#wpforms-panel-field-settings-pipedrive_apikey');

    function cleanLabels() {
        jQuery('.lead-label-card').remove();
    }

    function getPipedriveLabels() {
        if ( $labelId.length && $companyName.length && $apikey.length ) {
            if ( $companyName.val() && $apikey.val() ) {

                let url = 'https://' + $companyName.val() + '.pipedrive.com/v1/leadLabels?api_token=' + $apikey.val();

                jQuery.ajax({
                    url: url,
                    cache: true,
                    context: document.body
                }).done(function(responce) {

                    if ( responce.success ) {
                        cleanLabels();

                        jQuery.each(responce.data,function(index, item) {
                            let itemClass = 'lead-label-card';

                            if ( item.id == $labelId.val() ) {
                                itemClass = itemClass + ' active';
                            }

                            $labelId.before('<span class="' + itemClass + '" data-id="' + item.id + '">' + item.name + '</span>');
                        } );
                    }

                }).error(function(responce) {
                    cleanLabels();
                });

            }
        }
    }

    getPipedriveLabels();

    jQuery('body').on('click', '.lead-label-card', function (e){
        jQuery('.lead-label-card.active').removeClass('active');
        jQuery(this).addClass('active');
        jQuery('#wpforms-panel-field-settings-pipedrive_leadlabel').val( jQuery(this).data('id') );
    });

    $companyName.on('change', function (e){
        getPipedriveLabels();
    });

    $apikey.on('change', function (e){
        getPipedriveLabels();
    });

});