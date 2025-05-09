<div af-fieldset>
  <div class="af-container af-layout-inline">
    <af-field name="title" defn="{ldelim}required: false, input_attrs: {ldelim}placeholder: '{ts}Filter by Title{/ts}'{rdelim}, label: false{rdelim}" ></af-field>
    <af-field name="subtype" defn="{ldelim}input_type: 'Select', input_attrs: {ldelim}multiple: true, placeholder: '{ts}Filter by Type{/ts}'{rdelim}, required: false, label: false{rdelim}" ></af-field>
  </div>
  <crm-search-display-table search-name="ECK_Listing_{$entityType.name}" display-name="ECK_Listing_Display{$entityType.name}"></crm-search-display-table>
</div>
