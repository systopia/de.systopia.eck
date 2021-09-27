<div class="crm-block crm-form-block">

  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="top"}
  </div>

    {foreach from=$elementNames item=elementName}
      <div class="crm-section">
        <div class="label">{$form.$elementName.label}</div>
        <div class="content">{$form.$elementName.html}</div>
        <div class="clear"></div>
      </div>
    {/foreach}

    {* Custom groups links *}
    <div class="crm-accordion-wrapper">
      <div class="crm-accordion-header">{ts}Custom Groups{/ts}</div>
      <div class="crm-accordion-body">
        <p class="description">{ts 1=$customGroupAdminUrl}You may add custom fields to this entity type using <a href="%1">CiviCRM's custom groups</a>.{/ts}</p>
          {if !empty($customGroups)}
            <p class="description">{ts}The following custom groups extend this entity type:{/ts}</p>
            <ul>
                {foreach from=$customGroups item=customGroup}
                  <li><a href="{$customGroup.browse_url}">{$customGroup.title}</a></li>
                {/foreach}
            </ul>
          {/if}
      </div>
    </div>

  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>

</div>