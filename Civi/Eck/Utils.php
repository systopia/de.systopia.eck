<?php

namespace Civi\Eck;

use Civi\Api4\SearchDisplay;

class Utils {

  /**
   * Backport of the SearchDisplay.getMarkup api (added in Civi 6.12)
   *
   * TODO: Remove this after core version requirement is bumped to 6.12
   * @param string $searchName
   * @param string $displayName
   * @param array<string,mixed> $filters
   */
  public static function searchDisplayMarkup(string $searchName, string $displayName, array $filters = []): ?string {
    $display = SearchDisplay::get(FALSE)
      ->addWhere('name', '=', $displayName)
      ->addWhere('saved_search_id.name', '=', $searchName)
      ->addSelect('type:name', 'settings', 'saved_search_id.api_entity')
      ->execute()->first();

    if (!is_array($display)) {
      return NULL;
    }

    return sprintf('<%s search="%s" display="%s" api-entity="%s" settings="%s" filters="%s"></%s>',
      $display['type:name'],
      htmlspecialchars(\CRM_Utils_JS::encode($searchName), ENT_COMPAT),
      htmlspecialchars(\CRM_Utils_JS::encode($displayName), ENT_COMPAT),
      htmlspecialchars($display['saved_search_id.api_entity'], ENT_COMPAT),
      htmlspecialchars(\CRM_Utils_JS::encode($display['settings']), ENT_COMPAT),
      htmlspecialchars(\CRM_Utils_JS::encode($filters), ENT_COMPAT),
      $display['type:name']
    );
  }

}
