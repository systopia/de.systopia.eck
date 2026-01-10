'use strict';

(function($, _) {
  $(function() {
    const $form = $('form.CRM_Eck_Form_EntityType');

    function toggleSubtypes() {
      $('#eck-subtype-details').toggle($(this).is(':checked'));
      $('#eck-subtype-warning').toggle(!$(this).is(':checked'));
    }
    $('#has_subtypes', $form).each(toggleSubtypes).change(toggleSubtypes);

    // Auto-generate name from label.
    // On update the field is hidden and must not be changed.
    const $name = $('#name[type!="hidden"]', $form);
    if ($name.length === 0) {
      return;
    }

    $('#label', $form).on('keyup change', function() {
      $name.val($(this).val()).trigger('blur');
    });
    // Replace special characters in name.
    $name.on('keyup blur', function(e) {
      $(this).val(_.deburr($(this).val()).replace(/[^a-z0-9]+/gi, '_'));
      // On blur, trim leading/trailing whitespace & underscores.
      if (e.type === 'blur') {
        $(this).val(_.trim($(this).val(), ' _'));
      }
    });
  });
})(CRM.$, CRM._);
