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
    <table class="row-highlight">
      <thead>
      <tr>
        {foreach from=$fields item=field}
          <th>{$field.title}</th>
        {/foreach}
        <th>{ts}Operations{/ts}</th>
      </tr>
      </thead>
      <tbody>
      {if not empty($entities)}
          {foreach from=$entities item=entity key=entity_id}
            <tr>
              {foreach from=$fields item=field key=field_name}
                <td>
                    {if $field_name == 'title'}
                      <a href="{crmURL p="civicrm/eck/entity" q="reset=1&action=view&id=$entity_id"}">
                          {$entity.$field_name}
                      </a>
                    {else}
                        {$entity.$field_name}
                    {/if}
                </td>
              {/foreach}
              <td></td>
            </tr>
          {/foreach}
      {else}
        <tr>
          <td colspan="1">{ts}No entities{/ts}</td>
        </tr>
      {/if}
      </tbody>
    </table>
  </div>

  <div class="action-link">
      {capture assign=entity_type_name}{$entity_type.name}{/capture}
      {crmButton p='civicrm/eck/entity' q="reset=1&action=add&type=$entity_type_name" id="newEckEntity"  icon="plus-circle"}{ts 1=$entity_type.label}Add %1{/ts}{/crmButton}
  </div>
{/crmScope}
