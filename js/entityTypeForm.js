'use strict';

(function($, _) {
  $(function() {
    let $form = $('form.CRM_Eck_Form_EntityType');
    // Auto-generate name from label.
    $('#label', $form).on('keyup', function() {
      $('#name', $form).val($(this).val()).trigger('blur');
    });
    // Replace special characters in name.
    $('#name', $form).on('keyup blur', function(e) {
      $(this).val(_.trimLeft(_.deburr($(this).val()).replace(/[^a-z0-9]+/gi, '_'), ' _'));
      // On blur remove trailing underscore.
      if (e.type === 'blur') {
        $(this).val(_.trim($(this).val(), ' _'));
      }
    });
  });
})(CRM.$, CRM._);
