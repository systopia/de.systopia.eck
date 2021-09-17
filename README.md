# civiCRM Entity Construction Kit

This extension provides a user interface and an API for creating and managing
CiviCRM entities. In conjunction with custom fields being attached to those
entities, things in your business logic can be modelled more clearly, when
standard CiviCRM entities are not sufficient.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.2+
* CiviCRM (5.40)

## Installation (Web UI)

Learn more about installing CiviCRM extensions in the
[CiviCRM Sysadmin Guide](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/).

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl de.systopia.eck@https://github.com/systopia/de.systopia.eck/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git)
repo for this extension and install it with the command-line tool
[cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/FIXME/de.systopia.eck.git
cv en eck
```

## Development

This extension employs principles lined out in the following references:

* https://docs.civicrm.org/dev/en/latest/step-by-step/create-entity/#3-add-a-new-entity
* https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes/
* https://docs.civicrm.org/dev/en/latest/extensions/civix/#generate-entity
