{crmScope extensionKey='de.systopia.eck'}
<table>
  <thead>
  <tr>
    <th>{ts}Entity ID{/ts}</th>
  </tr>
  </thead>
  <tbody>
  {if not empty($entities)}
      {foreach from=$entities item=entity key=id}
        <tr>
          <td>{$id}</td>
        </tr>
      {/foreach}
  {else}
      <tr>
        <td colspan="1">{ts}No entities{/ts}</td>
      </tr>
  {/if}
  </tbody>
</table>
{/crmScope}
