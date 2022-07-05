# CiviCRM Entity Construction Kit

The Entity Construction Kit (ECK) provides a user interface and an API for
creating and managing custom CiviCRM entities. In conjunction with custom fields
being attached to those entities, things in your business logic can be modeled
more clearly, when standard CiviCRM entities are not sufficient.

The extension is licensed
under [AGPL-3.0](https://www.gnu.org/licenses/agpl-3.0).

## Requirements

* PHP v7.2+
* CiviCRM (5.51)

## Known Issues

Since "dynamic" entities, or, to be more specific, entity types, traditionally
have not been supported by CiviCRM Core, custom entities created by ECK might
not work as expected everywhere. The technical reason for this is that usually
every CiviCRM entity (type) needs a dedicated BAO class, present as a physical
PHP file, which does not apply for ECK entities, that share a single controller
class. This has already been worked on in CiviCRM Core, most notably by Coleman
Watts of the CiviCRM Core team, who is to be hugely credited for his work!
See [this Pull Request](https://github.com/civicrm/civicrm-core/pull/21853)
for more information.
