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
  {capture assign="entity_type_name"}{$entity_type.name}{/capture}
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
      {if $entities}
          {foreach from=$entities item=entity key=entity_id}
            <tr>
              {foreach from=$fields item=field key=field_name}
                <td>
                    {if $field_name == 'title'}
                      <a href="{crmURL p="civicrm/eck/entity" q="reset=1&type=$entity_type_name&id=$entity_id"}">
                          {$entity.$field_name}
                      </a>
                    {else}
                        {$entity.$field_name}
                    {/if}
                </td>
              {/foreach}
              <td>
                  {capture assign=subtypeValue}{$entity.subtype}{/capture}
                  {capture assign=editButtonPath}civicrm/eck/entity/edit/{$entity_type.name}/{$subtypes.$subtypeValue.name}{/capture}
                  {capture assign=editButtonFragment}?Eck_{$entity_type.name}={$entity.id}{/capture}
                  {crmButton p=$editButtonPath f=$editButtonFragment}{ts}Edit{/ts}{/crmButton}
              </td>
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
      {foreach from=$subtypes item=subtype}
          {capture assign=buttonId}newEck{$entityType.name}{$subtype.name}Entity{/capture}
          {capture assign=buttonPath}civicrm/eck/entity/edit/{$entity_type.name}/{$subtype.name}{/capture}
          {crmButton p=$buttonPath id=$buttonId  icon="plus-circle"}
          {ts 1=$subtype.label}Add %1{/ts}
          {/crmButton}
      {/foreach}
  </div>
{/crmScope}
