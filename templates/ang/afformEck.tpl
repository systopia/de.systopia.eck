<af-form ctrl="afform">
  <af-entity type="{$entityType.entity_name}" name="{$entityType.entity_name}" label="{$subType.label}" actions="{ldelim}create: true, update: true{rdelim}" security="RBAC" url-autofill="1" data="{ldelim}subtype: '{$subType.value}'{rdelim}" ></af-entity>
  <fieldset af-fieldset="{$entityType.entity_name}" class="af-container" af-title="{$subType.label}">
  {foreach item="field" from=$fields}
    <af-field name="{$field.name}"></af-field>
  {/foreach}
  {foreach item="customGroup" from=$customGroups}
    <fieldset>
      <legend>{$customGroup.title}</legend>
      {if $customGroup.is_multiple}
        <div af-join="Custom_{$customGroup.name}" af-repeat="{ts}Add{/ts}" af-copy="{ts}Copy{/ts}" min="0"{if $customGroup.max_multiple} max="{$customGroup.max_multiple}"{/if} actions="{ldelim}update: true, delete: true{rdelim}">
          <afblock-custom-{$customGroup.afName}></afblock-custom-{$customGroup.afName}>
        </div>
      {else}
        <afblock-custom-{$customGroup.afName}></afblock-custom-{$customGroup.afName}>
      {/if}
    </fieldset>
  {/foreach}
  </fieldset>
  <button class="af-button btn btn-primary" crm-icon="fa-check" ng-click="afform.submit()">{ts}Submit{/ts}</button>
</af-form>
