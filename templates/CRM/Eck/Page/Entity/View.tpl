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
  <div class="crm-block crm-content-block">

    <div class="crm-container section-shown">
      <table class="no-border">
        <tbody>
        {foreach from=$fields item="field" key="field_name"}
          <tr>
            <th>{$field.title}</th>
            <td>{$entity.$field_name}</td>
          </tr>
        {/foreach}
        </tbody>
      </table>
    </div>

      {if $viewCustomData}
          {capture assign="multiRecordDisplay"}0{/capture}
          {capture assign="groupId"}0{/capture}
          {capture assign="skipTitle"}0{/capture}
          {include file="CRM/Custom/Page/CustomDataView.tpl"}
      {/if}

  </div>
{/crmScope}
