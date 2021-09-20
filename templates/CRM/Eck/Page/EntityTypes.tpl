{crmScope extensionKey='de.systopia.eck'}
<table>
  <thead>
    <tr>
      <th>{ts}Entity type{/ts}</th>
      <th>{ts}Internal Name{/ts}</th>
      <th>{ts}Table name{/ts}</th>
      <th>{ts}Logging{/ts}</th>
    </tr>
  </thead>
  <tbody>
  {foreach from=$entity_types item=entity_type key=entity_type_name}
    <tr>
      <td>{$entity_type.label}</td>
      <td>{$entity_type_name}</td>
      <td>{$entity_type.table_name}</td>
      <td>{$entity_type.log}</td>
    </tr>
  {/foreach}
  </tbody>
</table>
{/crmScope}
