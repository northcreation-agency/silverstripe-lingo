# Lingo

Lingo lets developers define keys in yml-files in the same style as the normal lang-files but that can be viewed and translated from within the SilverStripe admin.

## TODO
* ~~Make Lingo translations work with variables~~ 
## Requirements

* SilverStripe 4.2+

##Installation
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

Use the SilverStripe translation functions as normal. If a Lingo translation entity exists in the DB the value of that will be returned, otherwise it will look in the yml-files and see if the entity exists there.


#### Use in php function

```
//with string
_t('Namespace.Entity','String to translate');

```

#### Use in template
```
//with string
<%t Namespace.Entity "String to translate" %>

```
