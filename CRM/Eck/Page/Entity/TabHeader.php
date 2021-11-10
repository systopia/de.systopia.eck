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
   * @return array
   * @throws \CRM_Core_Exception
   */
  public static function build(&$page) {
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
   * @return array
   * @throws Exception
   */
  public static function process(&$page) {
    if ($page->getVar('_id') <= 0) {
      return NULL;
    }

    $default = [
      'link' => NULL,
      'valid' => TRUE,
      'active' => TRUE,
      'current' => FALSE,
      'class' => 'ajaxForm',
    ];

    $tabs = [];
    $tabs['view'] = ['title' => ts('View')] + $default;

    $entityID = $page->getVar('_id');
    $entityType = $page->getVar('_entityType');

    // see if any other modules want to add any tabs
    // note: status of 'valid' flag of any injected tab, needs to be taken care in the hook implementation.
    CRM_Utils_Hook::tabset(
      'civicrm/eck/entity',
      $tabs,
      ['entity_id' => $entityID, 'entity_type' => $entityType]
    );

    $fullName = $page->getVar('_name');
    $className = CRM_Utils_String::getClassName($fullName);
    $new = '';

    // hack for special cases.
    switch ($className) {
      default:
        $class = strtolower($className);
        break;
    }

    if (array_key_exists($class, $tabs)) {
      $tabs[$class]['current'] = TRUE;
      $qfKey = $page->get('qfKey');
      if ($qfKey) {
        $tabs[$class]['qfKey'] = "&qfKey={$qfKey}";
      }
    }

    if ($entityID) {
      $reset = !empty($_GET['reset']) ? 'reset=1&' : '';

      foreach ($tabs as $key => $value) {
        if (!isset($tabs[$key]['qfKey'])) {
          $tabs[$key]['qfKey'] = NULL;
        }

        $action = 'view';
        $link = "civicrm/eck/entity/{$key}";
        $query = "{$reset}type={$entityType['name']}&id={$entityID}{$tabs[$key]['qfKey']}";

        $tabs[$key]['link'] = (isset($value['link']) ? $value['link'] :
          CRM_Utils_System::url($link, $query));
      }
    }

    return $tabs;
  }

  /**
   * @param CRM_Eck_Page_Entity $page
   */
  public static function reset(&$page) {
    $tabs = self::process($page);
    $page->set('tabHeader', $tabs);
  }

  /**
   * @param $tabs
   *
   * @return int|string
   */
  public static function getCurrentTab($tabs) {
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

    $current = $current ? $current : 'view';
    return $current;
  }

}
