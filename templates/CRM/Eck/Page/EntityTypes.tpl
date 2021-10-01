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
  <div class="crm-block crm-content-block">
    <table class="row-highlight">
      <thead>
      <tr>
        <th>{ts}Entity type{/ts}</th>
        <th>{ts}Internal Name{/ts}</th>
        <th>{ts}Sub types{/ts}</th>
        <th>{ts}Operations{/ts}</th>
      </tr>
      </thead>
      <tbody>
      {foreach from=$entity_types item=entity_type key=entity_type_id}
          {capture assign="entity_type_name"}{$entity_type.name}{/capture}
        <tr>
          <td>{$entity_type.label}</td>
          <td>{$entity_type.name}</td>
          <td>
              {if !empty($entity_type.sub_types)}
                  <ul>
                      {foreach from=$entity_type.sub_types item=sub_type}
                          <li>{$sub_type}</li>
                      {/foreach}
                  </ul>
              {/if}
          </td>
          <td>
            <ul>
              <li>
                <a href="{crmURL p='civicrm/eck/entity/list' q="reset=1&type=$entity_type_name"}">{ts}List entities{/ts}</a>
              </li>
              <li>
                <a href="{crmURL p='civicrm/admin/eck/entity-type' q="reset=1&action=update&type=$entity_type_name"}">{ts}Edit{/ts}</a>
              </li>
              <li>
                <a href="{crmURL p='civicrm/admin/eck/entity-type' q="reset=1&action=delete&type=$entity_type_name"}">{ts}Delete{/ts}</a>
              </li>
              {if !empty($entity_type.custom_groups)}
                <!--
                <li>
                    {ts}Manage custom fields{/ts}
                  <ul>
                      {foreach from=$entity_type.custom_groups item=custom_group key=custom_group_id}
                        <li>
                          <a href="{crmURL p='civicrm/admin/custom/group/field' q="reset=1&action=browse&gid=$custom_group_id"}">{$custom_group.title}</a>
                        </li>
                      {/foreach}
                  </ul>
                </li>
                -->
              {/if}
            </ul>
          </td>
        </tr>
      {/foreach}
      </tbody>
    </table>

    <div class="action-link">
        {crmButton p='civicrm/admin/eck/entity-type' q="action=add&reset=1" id="newEckEntityType"  icon="plus-circle"}{ts}Add Entity Type{/ts}{/crmButton}
    </div>
  </div>
{/crmScope}
