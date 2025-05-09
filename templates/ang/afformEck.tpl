<af-form ctrl="afform">
  <af-entity type="{$entityType.entity_name}" name="{$entityType.entity_name}" label="{$subType.label}" actions="{ldelim}create: true, update: true{rdelim}" security="RBAC" url-autofill="1" data="{ldelim}subtype: '{$subType.value}'{rdelim}" ></af-entity>
  <fieldset af-fieldset="{$entityType.entity_name}" class="af-container" af-title="{$subType.label}">
  {foreach item="field" from=$fields}
    <af-field name="{$field.name}"></af-field>
  {/foreach}
  {foreach item="customGroup" from=$customGroups}
    <fieldset>
      <legend>{$customGroup.title}</legend>
      <afblock-custom-{$customGroup.afName}></afblock-custom-{$customGroup.afName}>
    </fieldset>
  {/foreach}
  </fieldset>
  <button class="af-button btn btn-primary" crm-icon="fa-check" ng-click="afform.submit()">{ts}Submit{/ts}</button>
</af-form>
