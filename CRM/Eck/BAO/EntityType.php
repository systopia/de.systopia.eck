<?php
/*-------------------------------------------------------+
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
+--------------------------------------------------------*/

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use \Civi\API\Event\PrepareEvent;

/**
 *
 */
class CRM_Eck_BAO_EntityType extends CRM_Eck_DAO_EntityType implements EventSubscriberInterface {

  public static function getSubscribedEvents() {
    return [
      'civi.api.prepare' => 'civiApiPrepare',
    ];
  }

  public function civiApiPrepare(PrepareEvent $event) {

  }

}
