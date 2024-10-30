jQuery(document).ready(function($) {
    var hfff_field_name = hfff_settings.hfff_field_name;
    forminator_honeypot_hidden = '<div class="forminator-row forminator-hidden"><div class="forminator-field"><label for="' + hfff_field_name + '">' + hfff_field_name + '<input type="text" name="' + hfff_field_name + '" id="' + hfff_field_name + '" autocomplete="off" /></label></div></div>';
    $('form.forminator-custom-form').append(forminator_honeypot_hidden);
});