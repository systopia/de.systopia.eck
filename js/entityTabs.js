CRM.$(function($) {
  $('#panel_edit').on('crmFormSuccess', function() {
    CRM.tabHeader.resetTab('#tab_view');
    CRM.tabHeader.focus('#tab_view');
  });
});
