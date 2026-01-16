# CiviCRM Entity Construction Kit

The Entity Construction Kit (ECK) provides a user interface and an API for
creating and managing custom CiviCRM entities. In conjunction with custom fields
being attached to those entities, things in your business logic can be modeled
more clearly, when standard CiviCRM entities are not sufficient.

ECK allows you to define arbitrary semantics as a CiviCRM entity type, each with
a name and a label, and as many properties as you like by attaching custom
fields to them. You can also introduce another level of hierarchy by defining
sub-types.

As instances of those entity types are "real" CiviCRM entities, other extensions
can build upon that data model. E.g. the *SearchKit* extension can be used to
create search forms, overview pages, etc., and the *FormBuilder* extension can
be used to create forms for editing properties or linking entities.

Also, other extensions are able to attach their functionality to those entities.
E.g. the [CiviResource](https://github.com/systopia/de.systopia.resource)
extension can be used to mark entities as resources that can then be assigned to
resource demands for e.g. events.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## We need your support

This CiviCRM extension is provided as Free and Open Source Software, and we are
happy if you find it useful. However, we have put a lot of work into it (and
continue to do so), much of it unpaid for. So if you benefit from our software,
please consider making a financial contribution so we can continue to maintain
and develop it further.

If you are willing to support us in developing this CiviCRM extension, please
send an email to info@systopia.de to get an invoice or agree a different payment
method. Thank you!
