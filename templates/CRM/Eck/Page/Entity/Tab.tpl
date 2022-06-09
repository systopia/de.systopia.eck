{*-------------------------------------------------------+
| CiviCRM Entity Construction Kit                        |
| Copyright (C) 2021 SYSTOPIA                            |
| Author: J. Schuppe (schuppe@systopia.de)               |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*}

{crmScope extensionKey='de.systopia.eck'}
    {include file="CRM/common/TabHeader.tpl"}
    {* script to refresh & switch to view tab after submitting edit form *}
    {literal}
    <script type="text/javascript">
      CRM.$(function($) {
        $('#panel_edit').on('crmFormSuccess', function() {
          CRM.tabHeader.resetTab('#tab_view');
          CRM.tabHeader.focus('#tab_view');
        });
      });
    </script>
    {/literal}
{/crmScope}
