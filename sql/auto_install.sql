-- +--------------------------------------------------------------------+
-- | Copyright CiviCRM LLC. All rights reserved.                        |
-- |                                                                    |
-- | This work is published under the GNU AGPLv3 license with some      |
-- | permitted exceptions and without any warranty. For full license    |
-- | and copyright information, see https://civicrm.org/licensing       |
-- +--------------------------------------------------------------------+
--
-- Generated from schema.tpl
-- DO NOT EDIT.  Generated by CRM_Core_CodeGen
--
-- /*******************************************************
-- *
-- * Clean up the existing tables - this section generated from drop.tpl
-- *
-- *******************************************************/

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `civicrm_eck_entity_type`;

SET FOREIGN_KEY_CHECKS=1;
-- /*******************************************************
-- *
-- * Create new tables
-- *
-- *******************************************************/

-- /*******************************************************
-- *
-- * civicrm_eck_entity_type
-- *
-- * Custom CiviCRM entity types
-- *
-- *******************************************************/
CREATE TABLE `civicrm_eck_entity_type` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique EckEntityType ID',
  `name` varchar(58) NOT NULL COMMENT 'The entity type name, also used in the sql table name',
  `label` text NOT NULL COMMENT 'The entity type\'s human-readable name',
  `icon` varchar(255) DEFAULT NULL COMMENT 'crm-i icon class',
  `in_recent` tinyint NOT NULL DEFAULT 1 COMMENT 'Does this entity type get added to the recent items list',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `UI_name`(name)
)
ENGINE=InnoDB;
