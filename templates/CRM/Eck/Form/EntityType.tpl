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

    {/if}

    {if $action == 1}

      <div class="help">
        <p>{ts}You can add subtypes and custom fields after saving this new entity type.{/ts}</p>
      </div>

    {elseif $action == 2}

      {* Subtypes (toggled by entityTypeForm.js) *}
      <details id="eck-subtype-details" class="crm-accordion-bold" open style="display: none">
        <summary>
          <i class="crm-i fa-cube" aria-hidden="true" role="img"></i>
          {ts}Subtypes{/ts}
        </summary>
        <div class="crm-accordion-body">
          <crm-angular-js modules="crmSearchDisplayTable">
            {$subtypeDisplay}
          </crm-angular-js>
        </div>
      </details>

      {if $subtypesExist}
        {* Warning toggled by entityTypeForm.js *}
        <div id="eck-subtype-warning" class="messages warning no-popup" style="display: none">
          <i class="crm-i fa-exclamation-triangle" aria-hidden="true" role="img"></i>
          {ts 1=$entityType.label}Subtype information will be deleted from all existing %1 records.{/ts}
        </div>
      {/if}

      {* Custom groups table *}
      <details class="crm-accordion-bold" open>
        <summary>
          <i class="crm-i fa-rectangle-list" aria-hidden="true" role="img"></i>
          {ts}Custom Fields{/ts}
        </summary>
        <div class="crm-accordion-body">
          <crm-angular-js modules="crmSearchDisplayTable">
            {$groupDisplay}
          </crm-angular-js>
        </div>
      </details>

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
