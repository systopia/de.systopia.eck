# CiviCRM Entity Construction Kit

The Entity Construction Kit (ECK) provides a user interface and an API for
creating and managing custom CiviCRM entities. In conjunction with custom fields
being attached to those entities, things in your business logic can be modeled
more clearly, when standard CiviCRM entities are not sufficient.

The extension is licensed
under [AGPL-3.0](https://www.gnu.org/licenses/agpl-3.0).

## Development

This extension employs principles lined out in the following references:

* https://docs.civicrm.org/dev/en/latest/step-by-step/create-entity/#3-add-a-new-entity
* https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes/
* https://docs.civicrm.org/dev/en/latest/extensions/civix/#generate-entity

## Known Issues

Since "dynamic" entities, or, to be more specific, entity types, have not been
supported by CiviCRM Core, custom entities created by ECK might not work as
expected everywhere. The technical reason for this is that traditionally every
CiviCRM entity (type) needs a dedicated BAO class, present as a physical PHP
file, which does not apply for ECK entities, that share a single controller
class. This is still being worked on in CiviCRM Core.
See [this Pull Request](https://github.com/civicrm/civicrm-core/pull/21853)
for more information.
