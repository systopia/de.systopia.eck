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

<div class="crm-block crm-form-block">

  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="top"}
  </div>

    {if $action == 1 or $action == 2}

        {foreach from=$elementNames item=elementName}
          <div class="crm-section">
            <div class="label">{$form.$elementName.label}</div>
            <div class="content">
                {if $elementName == 'name'}<span>Eck</span>{/if}
                {$form.$elementName.html}
            </div>
            <div class="clear"></div>
          </div>
        {/foreach}

        {* Custom groups links *}
      <div class="crm-accordion-wrapper">
        <div class="crm-accordion-header">{ts}Custom Groups{/ts}</div>
        <div class="crm-accordion-body">
          <p class="description">{ts 1=$customGroupAdminUrl}You may add custom fields to this subtype using <a href="%1">CiviCRM's custom groups</a>.{/ts}</p>
          {capture assign="entityType"}{$subType.grouping}{/capture}
          <p class="description">{ts 1=$customGroupAdminUrl}You may administer custom fields all subtypes of this entity type by editing the <a href="{crmURL p="civicrm/admin/eck/entity-type" q="reset=1&action=update&type=$entityType"}">entity type</a>.{/ts}</p>
            {if !empty($customGroups)}
              <p class="description">{ts}The following custom groups extend this subtype:{/ts}</p>
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
          <p>{ts 1=$subType.label}Do you want to delete the subtype <em>%1</em>?{/ts}</p>
          <p>{ts}This involves deleting all custom fields attached to this subtype and all currently existing entities of this subtype.{/ts}</p>
          <p class="crm-error">{ts}This action cannot be undone.{/ts}</p>
        </div>
      </div>

    {/if}

  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>

</div>
