jQuery(document).ready(function(){
    jQuery('#crm-form').submit(function(e){
        e.preventDefault();
        var crm_name = jQuery('#crm-name').val();
        var crm_email = jQuery('#crm-email').val();
        var crm_phone = jQuery('#crm-phone').val();
        var crm_country = jQuery('#crm-country').val();
        var crm_yfd = jQuery('#crm-yfd').val();
        var crm_currency = jQuery('#crm-currency').val();
        var crm_amount_sale = jQuery('#crm-amount-lose').val();
        var crm_deposit_method = jQuery('#crm-deposit-method').val();
        var crm_total_amount = jQuery('#crm-total-amount').val();
        var crm_description = jQuery('#crm-description').val();
        var crm_privacy_policy = jQuery('#crm-privacy-policy').val();
        var crm_title = jQuery('#crm-title').val();

        jQuery.ajax({
            url : crm_ajaxurl, //'<?php echo admin_url('admin-ajax.php'); ?>',
            type : 'post',
            async: false,
            data : {
                action : 'submit_crm_form',
                crm_data : {
                    name : crm_name,
                    email : crm_email,
                    phone : crm_phone,
                    country : crm_country,
                    first_deposit : crm_yfd,
                    currency : crm_currency,
                    sale_amount : crm_amount_sale,
                    deposit_method : crm_deposit_method,
                    total_amount : crm_total_amount,
                    description : crm_description,
                    privacy_policy : crm_privacy_policy,
                    crm_title : crm_title,
                }
            },
            dataType: 'JSON',
            success : function( response ) {
                if(response){
                    window.location.replace("/thank-you");
                }else{
                    alert('somethis is wrong, please try again');
                }
            }
        });

    })
})
function validateCRMForm(){
    return true;
}
