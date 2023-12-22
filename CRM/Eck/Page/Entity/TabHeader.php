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

use CRM_Eck_ExtensionUtil as E;

/**
 * Helper class to build navigation links
 */
class CRM_Eck_Page_Entity_TabHeader {

  /**
   * @param CRM_Eck_Page_Entity $page
   *
   * @return array<string,array<mixed>>|null
   * @throws \CRM_Core_Exception
   */
  public static function build(&$page): ?array {
    $tabs = $page->get('tabHeader');
    if (!$tabs || empty($_GET['reset'])) {
      $tabs = self::process($page);
      $page->set('tabHeader', $tabs);
    }
    $page->assign_by_ref('tabHeader', $tabs);
    CRM_Core_Resources::singleton()
      ->addScriptFile(
        'civicrm',
        'templates/CRM/common/TabHeader.js',
        1,
        'html-header'
      )
      ->addSetting([
        'tabSettings' => [
          'active' => self::getCurrentTab($tabs),
        ],
      ]);
    return $tabs;
  }

  /**
   * @param CRM_Eck_Page_Entity $page
   *
   * @return array<string,array<mixed>>|null
   * @throws Exception
   */
  public static function process(&$page): ?array {
    if ($page->getVar('_id') <= 0) {
      return NULL;
    }

    $default = [
      'link' => NULL,
      'valid' => TRUE,
      'active' => TRUE,
      'current' => FALSE,
      'class' => 'livePage',
      'extra' => FALSE,
      'template' => FALSE,
      'count' => FALSE,
      'icon' => FALSE,
    ];

    $entityID = $page->getVar('_id');
    $entityType = $page->getVar('_entityType');
    $subtype = $page->getVar('_subtype');

    $tabs = [];
    $tabs['view'] = [
      'title' => E::ts('View'),
      'link' => CRM_Utils_System::url(
          'civicrm/eck/entity/view',
          "reset=1&type={$entityType['name']}&id={$entityID}"
      ),
    ] + $default;

    $afform_name = 'afform' . 'Eck_' . $entityType['name'] . '_' . $subtype;
    $module = _afform_angular_module_name($afform_name);
    $tabs['edit'] = [
      'title' => E::ts('Edit'),
      'template' => 'CRM/Eck/Page/Entity/Edit.tpl',
      'module' => $module,
      'directive' => _afform_angular_module_name($afform_name, 'dash'),
    ] + $default;
    Civi::service('angularjs.loader')->addModules($module);

    // see if any other modules want to add any tabs
    // note: status of 'valid' flag of any injected tab, needs to be taken care in the hook implementation.
    CRM_Utils_Hook::tabset(
      'civicrm/eck/entity',
      $tabs,
      ['entity_id' => $entityID, 'entity_type' => $entityType]
    );
    // Add default properties to each tab in case implementations missed that.
    // @url https://github.com/civicrm/civicrm-core/pull/22135
    foreach ($tabs as &$tab) {
      $tab += $default;
    }

    if ($entityID) {
      foreach ($tabs as $key => $value) {
        if (!isset($tabs[$key]['qfKey'])) {
          $tabs[$key]['qfKey'] = NULL;
        }

        $tabs[$key]['link'] = $value['link'] ?? $value['url'] ?? NULL;
      }
    }

    // Load requested tab.
    $current = CRM_Utils_Request::retrieve(
      'selectedChild',
      'Alphanumeric'
    );
    if (isset($tabs[$current])) {
      $tabs[$current]['current'] = TRUE;
    }

    return $tabs;
  }

  /**
   * @param CRM_Eck_Page_Entity $page
   */
  public static function reset(&$page): void {
    $tabs = self::process($page);
    $page->set('tabHeader', $tabs);
  }

  /**
   * @param array<string,array<mixed>>|null $tabs
   *
   * @return string
   */
  public static function getCurrentTab(?array $tabs): string {
    static $current = FALSE;

    if ($current) {
      return $current;
    }

    if (is_array($tabs)) {
      foreach ($tabs as $subPage => $pageVal) {
        if (CRM_Utils_Array::value('current', $pageVal) === TRUE) {
          $current = $subPage;
          break;
        }
      }
    }

    $current = FALSE !== $current ? $current : 'view';
    return $current;
  }

}
