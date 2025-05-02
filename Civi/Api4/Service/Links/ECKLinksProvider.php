<?php

namespace Civi\Api4\Service\Links;

use Civi\API\Event\RespondEvent;
use Civi\Api4\Utils\CoreUtil;

/**
 * @service
 * @internal
 */
class ECKLinksProvider extends \Civi\Core\Service\AutoSubscriber {
  use LinksProviderTrait;

  public static function getSubscribedEvents(): array {
    return [
      'civi.api.respond' => 'alterECKLinksResult',
    ];
  }

  public static function alterECKLinksResult(RespondEvent $e): void {
    $request = $e->getApiRequest();
    if (
      is_object($request)
      && is_a($request, '\Civi\Api4\Action\GetLinks') && str_starts_with($request->getEntityName(), 'Eck_')
    ) {
      /** @phpstan-var array<int, array{path: string, text: string, icon: string}> $links */
      $links = (array) $e->getResponse();
      $addLinkIndex = self::getActionIndex($links, 'add');
      // Expand the "add" link to multiple subtypes if it exists (otherwise the WHERE clause excluded "add" so we should
      // too).
      if ($request->getExpandMultiple() && isset($addLinkIndex)) {
        [, $entityTypeName] = explode('_', $request->getEntityName(), 2);
        $addLinks = [];
        /** @phpstan-var array<string, array{path: string, text: string, icon: string}> $paths */
        $paths = CoreUtil::getInfoItem($request->getEntityName(), 'paths');
        $addPath = $paths['add'];
        foreach (\CRM_Eck_BAO_EckEntityType::getSubTypes($entityTypeName, FALSE) as $subType) {
          /** @phpstan-var array{value: string, label: string, icon: string} $subType */
          $addLink = $links[$addLinkIndex];
          if (is_string($addLink['path']) && '' !== $addLink['path']) {
            /** @phpstan-var array{path: string, text: string, icon: string} $addLink */
            $addLink['path'] = str_replace('[subtype]', $subType['value'], $addPath);
            $values = $request->getValues();
            // Add values to url to pass to the form
            if (count($values) > 0) {
              $addLink['path'] .= '#?' . http_build_query($values);
            }
          }
          if (array_key_exists('icon', $addLink)) {
            $addLink['icon'] = $subType['icon'] ?? NULL;
          }
          $addLink['text'] = $subType['label'];
          $addLinks[] = $addLink;
        }
        // Replace the one generic "add" link with multiple per-subtype links.
        array_splice($links, $addLinkIndex, 1, $addLinks);
        // @phpstan-ignore-next-line
        $e->getResponse()->exchangeArray(array_values($links));
      }
    }
  }

}
