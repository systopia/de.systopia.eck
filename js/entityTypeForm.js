'use strict';

(function($, _) {
  $(function() {
    const $form = $('form.CRM_Eck_Form_EntityType');
    // Auto-generate name from label.
    // On update the field is hidden and must not be changed.
    const $name = $('#name[type!="hidden"]', $form);
    if ($name.length === 0) {
      return;
    }

    $('#label', $form).on('keyup', function() {
      $name.val($(this).val()).trigger('blur');
    });
    // Replace special characters in name.
    $name.on('keyup blur', function(e) {
      $(this).val(_.trimStart(_.deburr($(this).val()).replace(/[^a-z0-9]+/gi, '_'), ' _'));
      // On blur remove trailing underscore.
      if (e.type === 'blur') {
        $(this).val(_.trim($(this).val(), ' _'));
      }
    });
  });
})(CRM.$, CRM._);
