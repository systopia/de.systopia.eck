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
  <div class="crm-block crm-form-block">

    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="top"}
    </div>

      {if $action == 1 or $action == 2}

          {foreach from=$elementNames item=elementName}
            <div class="crm-section">
              <div class="label">{$form.$elementName.label}</div>
              <div class="content">
                  {if $elementName == 'name'}<span>Eck_</span>{/if}
                  {$form.$elementName.html}
              </div>
              <div class="clear"></div>
            </div>
          {/foreach}

          {* Sub types *}
        <div class="crm-accordion-wrapper">
          <div class="crm-accordion-header">{ts}Sub types{/ts}</div>
          <div class="crm-accordion-body">

            <div class="crm-block crm-content-block">
              <table class="row-highlight">
                <thead>
                <tr>
                  <th>{ts}Subtype{/ts}</th>
                  <th>{ts}Operations{/ts}</th>
                </tr>
                </thead>
                  {if !empty($subTypes)}
                      {foreach from=$subTypes item=subTypeLabel key=subTypeValue}
                        <tr>
                          <td>{$subTypeLabel}</td>
                          <td>
                            <ul>
                              <li>
                                <a href="{crmURL p="civicrm/admin/eck/subtype" q="reset=1&action=update&subtype=$subTypeValue"}">{ts}Edit{/ts}</a>
                              </li>
                              <li>
                                <a href="{crmURL p="civicrm/admin/eck/subtype" q="reset=1&action=delete&subtype=$subTypeValue"}">{ts}Delete{/ts}</a>
                              </li>
                            </ul>
                          </td>
                        </tr>
                      {/foreach}
                  {else}
                    <tr>
                      <td colspan="2">{ts}No subtypes{/ts}</td>
                    </tr>
                  {/if}
              </table>
            </div>
              {if $action == 2}
                <div class="action-link">
                    {capture assign=entityTypeName}{$entityType.name}{/capture}
                    {crmButton p='civicrm/admin/eck/subtype' q="reset=1&action=add&type=$entityTypeName" id="newEckSubtype"  icon="plus-circle"}{ts}Add Subtype{/ts}{/crmButton}
                </div>
              {else}
                <p class="description">{ts}You may add subtypes after saving this new entity type.{/ts} {ts}Note: ECK entities require that you add at least one subtype.{/ts}</p>
              {/if}

          </div>
        </div>
          {* Custom groups links *}
        <div class="crm-accordion-wrapper">
          <div class="crm-accordion-header">{ts}Custom Groups{/ts}</div>
          <div class="crm-accordion-body">
            <p class="description">{ts 1=$customGroupAdminUrl}You may add custom fields to this entity type using
                <a href="%1">CiviCRM's custom groups</a>
                .{/ts}</p>
            <p class="description">{ts 1=$customGroupAdminUrl}You may administer custom fields for subtypes of this entity type by editing the subtype.{/ts}</p>
              {if !empty($customGroups)}
                <p class="description">{ts}The following custom groups extend all subtypes of this entity type:{/ts}</p>
                <ul>
                    {foreach from=$customGroups item=customGroup}
                      <li>
                        <a href="{$customGroup.browse_url}">{$customGroup.title}</a>
                      </li>
                    {/foreach}
                </ul>
              {/if}
          </div>
        </div>
      {elseif $action == 8}
        <div class="crm-section no-label">
          <div class="status">
            <p>{ts 1=$entityType.label}Do you want to delete the entity type<em>%1</em>?{/ts}
            </p>
            <p>{ts}This involves deleting all custom fields attached to this entity type and all currently existing entities of this type.{/ts}</p>
            <p class="crm-error">{ts}This action cannot be undone.{/ts}</p>
          </div>
        </div>
      {/if}

    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>

  </div>
{/crmScope}
