# Entities

As in some languages the term *Entity* is not that widely used in everyday
talking, let's look into what an entity is and what it exactly is being used for
in CiviCRM.

The term *Entity* is an abstract name for a *Thing* or a *Person* or a *Process*
in general. In technical terms, it is a description of some data that form a
semantic unit and are to be persisted as a whole.

## Entity types

For distinguishing different types of *things* or *persons*, there are different
*entity types*, in order to name, define and differentiate them from one
another. While e.g. a vehicle and a piece of equipment are both *things*, they
are still different, and compared to a *person* or a *project*, their properties
will have to be defined and stored very differently. Also, there is different
behavior connected to any of those entities, or more specific, entity types, as
you can't necessarily send a mailing to a vehicle as you can to a person, while
a person will not utilize the concept of being owned by someone, which is
crucial information for a vehicle though.

## Entities in CiviCRM

CiviCRM brings a lot of entity types in order to differentiate processes and
persons that it is managing. The most important entity type is *Contact*, as it
models the people you as an organization are involved with. Other important
entity types in CiviCRM Core are *Activity*, *Event*, *Contribution*, and
*Membership*, but there are more entity types you might not notice being
technically the same-level data structure, such as *Address*, *Recurring
Cotribution*, *Custom Field*, or *Group*, or an a more internal level
*OptionGroup*, *MembershipType*, or *PaymentProcessor*.

Entity types can also be brought by extensions, which provide additional
functionality for managing another part of a business model, e.g. the
*CiviGrant* extension provides the *Grant* entity type, allowing users to manage
entities of that type and use functionality attached to them. Oftentimes, those
entity types are very specific to functionality the extension provides, and are
thus limited to the extension they are defined in. Sometimes, such entity types
can be made use of by other processes, e.g. the *BankingAccount* entity type
defined by the
*[CiviBanking](https://github.com/project60/org.project60.banking)* extension,
allowing you to just create and manage bank accounts for contacts, without
having to use the extension's entire functional range.

## Custom Entities

But what if you wanted to keep track of other things or processes that do not
quite fit in any of the existing entity types that CiviCRM or extensions
provide?

Let's assume you run an association that manages a bunch of vehicles and
event equipment for people to rent. You'd want to keep track of when an
inspection is due for your cars, who's currently in possession of which piece of
equipment, and what condition each of those items is in. CiviCRM seems a bit
limited when it comes to storing this information. You could make up a separate
*contact type* for both, *Vehicle* and *Equipment*, but what's the purpose of
them showing up in mailing lists or being assigned an e-mail address? You would
have to always filter those types of contacts out when performing
contact-related tasks. You might be able to only track the tasks and assignments
as *Activity* entities and typing information in their *Details* field, but that
would be duplicating data and making it confusing for your staff to keep track
of everything. Other entity types do not really model the semantics of those
things and processes as well.

Enter *Entity Construction Kit*. This extension allows you to define arbitrary
semantics as a CiviCRM entity type, each with a name and a label, and as many
properties as you like by attaching custom fields to them. You can also
introduce another level of hierarchy by defining sub-types. This allows you to
have an entity type *Vehicle*, optionally with sub-types *Cargo Bike*,
*Compact* and *Van*, and an entity type *Equipment* with sub-types like *Chair*,
*Beer Tent*, or *Office Equipment*. As instances of those types are "real"
CiviCRM entities, other extensions can build upon that data model. E.g. the
*Search Kit* extension can be used to create search forms, overview pages, etc.,
and the *Form Builder* extension can be used to create forms for editing
properties or linking entities (e.g. for defining the owner or manager of a
vehicle by attaching a *Contact Reference* field to the *Vehicle* entity type).

Also, other extensions are able to attach their functionality to those entities.
E.g. the *[CiviResource](https://github.com/systopia/de.systopia.resource)*
extension can be used to mark entities as resources that can then be assigned to
resource demands for e.g. events.
