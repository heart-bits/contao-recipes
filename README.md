# Recipe bundle for Contao Open Source CMS

This bundle provides a way for Contao to manage recipes as well as recipe categories, units and ingredients.

## Installation

Install the bundle via Composer:

```
composer require heart-bits/contao-recipes-bundle
```

## Configuration

### Migrations

Once installed, if you don't have any units and/or ingredients set, the migration/s will import a basic set of units/ingredients into your system:
```
vendor/bin/contao-console contao:migrate
```
Currently, the ingredients and units are only imported in german language.
But you can edit these imported values afterwards or add more to the list of course.

If you don't want to import these at all, you can also skip the migrations by adding `--schema-only` to the command above and add your individual values.

### Modules

The bundle provides a list and reader module which can be created and should be added to separate pages.

The list modules should redirect to the page, where the reader module was placed. 

After that the page with the reader module must also have the `Require an item` setting checked.

Recipes inside the list module will then automatically add a `read more` link for the reader page correctly, if contents exist inside them.

### Elements

Inside the recipes, you have the possibility to add detailed contents to it.

Possible elements currently include:
* `Recipe step` Adds numbered steps to the recipe
* `Recipe image` Adds an image to the recipe
* `Gallery` Adds a Contao core gallery to the recipe


**NOTE: It is not possible to choose other Contao core or third party elements inside the recipe without overwriting the `options_callback` of the `type` field in `tl_content`.**
