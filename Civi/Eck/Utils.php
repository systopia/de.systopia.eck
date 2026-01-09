<?php

namespace Civi\Eck;

use Civi\Api4\SearchDisplay;

class Utils {

  /**
   * Generates the markup needed to embed a search display in a page.
   *
   * TODO: This probably belongs in core.
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

    return sprintf('<%s search="\'%s\'" display="\'%s\'" api-entity="%s" settings="%s" filters="%s"></%s>',
      htmlspecialchars($display['type:name']),
      htmlspecialchars($searchName),
      htmlspecialchars($displayName),
      htmlspecialchars($display['saved_search_id.api_entity']),
      htmlspecialchars(\CRM_Utils_JS::encode($display['settings'])),
      htmlspecialchars(\CRM_Utils_JS::encode($filters)),
      htmlspecialchars($display['type:name'])
    );
  }

}
