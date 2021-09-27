{crmScope extensionKey='de.systopia.eck'}
  <div class="crm-block crm-content-block">
    <table class="row-highlight">
      <thead>
      <tr>
        <th>{ts}Entity type{/ts}</th>
        <th>{ts}Internal Name{/ts}</th>
        <th>{ts}Table name{/ts}</th>
        <th>{ts}Operations{/ts}</th>
      </tr>
      </thead>
      <tbody>
      {foreach from=$entity_types item=entity_type key=entity_type_id}
          {capture assign="entity_type_name"}{$entity_type.name}{/capture}
        <tr>
          <td>{$entity_type.label}</td>
          <td>{$entity_type.name}</td>
          <td>{$entity_type.table_name}</td>
          <td>
            <ul>
              <li>
                <a href="{crmURL p='civicrm/admin/eck/entity-type' q="reset=1&action=update&type=$entity_type_name"}">{ts}Edit{/ts}</a>
              </li>
              <li>
                <a href="{crmURL p='civicrm/admin/eck/entity-type' q="reset=1&action=delete&type=$entity_type_name"}">{ts}Delete{/ts}</a>
              </li>
            </ul>
          </td>
        </tr>
      {/foreach}
      </tbody>
    </table>
  </div>
{/crmScope}
