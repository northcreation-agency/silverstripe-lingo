# Lingo

Lingo lets developers define keys in yml-files in the same style as the normal lang-files but that can be viewed and translated from within the SilverStripe admin.

## Requirements

* SilverStripe 4 (tested with 4.2)

## Installation

`composer require northcreationagency/silverstripe-lingo`

## How to use

Set the location of the file/s that should be used for the texts to be handled in the yml config file of your project, ie:  `app/_config/mysite.yml`

`moduleCatalog` is the catalog that the `textCatalog` is placed in (at the "first level").

`textCatalog` is the catalog that will contain the yml-files to be read by the module.

Example: app/admintext

Where `app` is the "moduleCatalog" and `admintext` is the "textCatalog".

```
NorthCreationAgency\SilverStripeLingo\Lingo:
  moduleCatalog: app
  textCatalog: admintext
```
Place one or more yml-files (one for each language) for your texts in the `textCatalog` catalog.
The files should follow the same structure as SilverStripes yml lang files.
Example:

```
en:
  List:
    Header: 'This is a list header'
  Company:
    Header: This is a company header
```

Then when you run `dev/build` the texts in the yml-file(s) are read and stored in the database and can be edited from the admin.
The Lingo cache is also cleared on build.

Use the SilverStripe translation functions as normal. It will first search the regular yml-files for the entity. If it not 
exists there it will check if a Lingo translation entity exists in the DB. If it does the value of that will be cached and returned.


#### Use in php function

```

// Simple string translation
_t('LeftAndMain.FILESIMAGES','Files & Images');

// Using injection to add variables into the translated strings.
_t('CMSMain.RESTORED',
    "Restored {value} successfully",
    ['value' => $itemRestored]
);

```

#### Use in template
```
//with string
<%t Foo.BAR 'Bar' %>

//with variable
<%t Member.WELCOME 'Welcome {name} to {site}' name=$Member.Name site="Foobar.com" %>

```
