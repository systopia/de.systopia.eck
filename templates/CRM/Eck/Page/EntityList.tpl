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
  </div>
{/crmScope}
