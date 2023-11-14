<div af-fieldset>
  <div class="pull-right btn-group">
    <button type="button" class="btn dropdown-toggle btn-primary" crm-icon="fa-plus" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      {ts 1=$entityType.label}Add %1{/ts} <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
      {foreach item="subType" from=$subTypes}
        <li>
          {literal}
            <a class="crm-popup" ng-href="{{:: crmUrl('civicrm/eck/entity/edit/{/literal}{$entityType.name}/{$subType.value}'){literal} }}">
                <i class="fa-fw{/literal}{if $subType.icon} crm-i {$subType.icon}{/if}{literal}"></i>
                {/literal}{$subType.label}{literal}
            </a>
          {/literal}
        </li>
      {/foreach}
    </ul>
  </div>
  <div class="af-container af-layout-inline">
    <af-field name="title" defn="{ldelim}required: false, input_attrs: {ldelim}placeholder: '{ts}Filter by Title{/ts}'{rdelim}, label: false{rdelim}" ></af-field>
    <af-field name="subtype" defn="{ldelim}input_type: 'Select', input_attrs: {ldelim}multiple: true, placeholder: '{ts}Filter by Type{/ts}'{rdelim}, required: false, label: false{rdelim}" ></af-field>
  </div>
  <crm-search-display-table search-name="ECK_Listing_{$entityType.name}" display-name="ECK_Listing_Display{$entityType.name}"></crm-search-display-table>
</div>
