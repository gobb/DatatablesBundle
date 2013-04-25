# DatatablesBundle

## Installation

Neben dem DatatablesBundle werden noch jQuery, das DataTables-Plugin und Bootstrap benötigt.

Dazu wird die composer.json erweitert:

```twig
{
    "repositories": {
        "datatables": {
            "type": "package",
            "package": {
                "name": "datatables/datatables",
                "version": "1.9.4",
                "source": {
                    "url": "git://github.com/DataTables/DataTables.git",
                    "type": "git",
                    "reference": "origin/1_9"
                }
            }
        },
        "jquery": {
            "type": "package",
            "package": {
                "name": "jquery/jquery",
                "version": "2.0.0",
                "dist": {
                    "url": "http://code.jquery.com/jquery-2.0.0.min.js",
                    "type": "file"
                }
            }
        }
    },

    "require": {

        "jquery/jquery": "2.0.0",
        "datatables/datatables": "1.9.4",
        "twitter/bootstrap": "v2.3.1",
        "sg/datatablesbundle": "dev-master"
    },
}
```

## Assetic konfigurieren

Alle CSS und JavaScript Dateien können dann mit Assetic eingebunden werden.

Ein Beispiel:

```twig
{# app/Resources/base.html.twig #}

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>{% block title %}Welcome!{% endblock %}</title>
        {% block javascripts %}{% endblock %}
        {% block stylesheets %}{% endblock %}
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />
    </head>
    <body>
        {% block body %}{% endblock %}
    </body>
</html>
```

```yml
## config.yml ##

assetic:
    debug:          %kernel.debug%
    use_controller: false
    bundles:        [ SgAppBundle ]
    #java: /usr/bin/java
    filters:
        cssrewrite: ~
        #closure:
        #    jar: %kernel.root_dir%/Resources/java/compiler.jar
        #yui_css:
        #    jar: %kernel.root_dir%/Resources/java/yuicompressor-2.4.7.jar
    assets:
        jquery_js:
            inputs:
                - '%kernel.root_dir%/../vendor/jquery/jquery/jquery-2.0.0.min.js'
        bootstrap_js:
            inputs:
                - '%kernel.root_dir%/../vendor/twitter/bootstrap/docs/assets/js/bootstrap.min.js'
        datatables_js:
            inputs:
                - '%kernel.root_dir%/../vendor/datatables/datatables/media/js/jquery.dataTables.js'
        datatablesbundle_js:
            inputs:
                - '%kernel.root_dir%/../vendor/sg/datatablesbundle/Sg/DatatablesBundle/Resources/public/js/dataTables_bootstrap.js'
                - '%kernel.root_dir%/../vendor/sg/datatablesbundle/Sg/DatatablesBundle/Resources/public/js/strtr.js'
        bootstrap_css:
            inputs:
                - '%kernel.root_dir%/../vendor/twitter/bootstrap/docs/assets/css/bootstrap.css'
        datatablesbundle_css:
            inputs:
                - '%kernel.root_dir%/../vendor/sg/datatablesbundle/Sg/DatatablesBundle/Resources/public/css/dataTables_bootstrap.css'
```

```twig
{# src/Sg/AppBundle/Resources/views/layout.html.twig #}

{% extends '::base.html.twig' %}

{% block title %}AppBundle{% endblock %}

{% block javascripts %}
    {% javascripts '@jquery_js' '@bootstrap_js' '@datatables_js' '@datatablesbundle_js' %}
        <script src="{{ asset_url }}"></script>
    {% endjavascripts %}
{% endblock %}

{% block stylesheets %}
    {% stylesheets '@bootstrap_css' '@datatablesbundle_css' %}
        <link href="{{ asset_url }}" type="text/css" rel="stylesheet" />
    {% endstylesheets %}
{% endblock %}

{% block body%}
{% endblock %}
```

## Anwendungsbeispiel

### Controller

```php
/**
 * @Route("/draft", name="recipe_draft_admin")
 * @Template("SgRecipeBundle:Admin/RecipeAdmin:index.html.twig")
 *
 * @return array
 */
public function draftAction()
{
    /**
     * @var \Sg\DatatablesBundle\Factory\DatatableFactory $factory
     */
    $factory = $this->get('sg_datatables.factory');

    /**
     * @var \Sg\DatatablesBundle\Datatable\DatatableView $datatableView
     */
    $datatableView = $factory->getTable('Sg\RecipeBundle\Datatables\RecipeDraftAdminDatatable');

    return array(
        'title' => 'Rezeptentwürfe',
        'datatable' => $datatableView,
    );
}

/**
 * @Route("/draft/results", name="recipe_draft_admin_results")
 *
 * @return array
 */
public function draftResultsAction()
{
    /**
     * @var \Sg\DatatablesBundle\Datatable\DatatableData $datatable
     */
    $datatable = $this->get('sg_datatables')->getDatatable('SgRecipeBundle:Recipe');

    /**
     * @var \Doctrine\ORM\QueryBuilder $qb
     */
    $callbackFunction =

        function($qb) {

            $andExpr = $qb->expr()->andX();
            $andExpr->add($qb->expr()->eq('recipe.approved', '0'));
            $andExpr->add($qb->expr()->eq('recipe.private', '0'));
            $andExpr->add($qb->expr()->eq('recipe.isPublished', '0'));
            $qb->andWhere($andExpr);

        };

    $datatable->addWhereBuilderCallback($callbackFunction);

    return $datatable->getSearchResults();
}
```

### Associations

If the field contains a association is the mapping as follows:

new Field(sourceField + underscore + targetField);

**Example:**
```php
$authorField = new Field('createdBy_username');
```

### DatatableView class

```php
class RecipeDraftAdminDatatable extends DatatableView
{
    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->setTableId('recipe_draft_admin_datatable');
        $this->setSAjaxSource('recipe_draft_admin_results');

        $this->setTableHeaders(array(
            'Titel',
            'Autor',
            'Entwurf',
            'Privat',
            'Veröffentlicht',
            'Kommentierbar',
            '',
            ''
        ));

        $titleField = new Field('title');

        $authorField = new Field('createdBy_username');
        $authorField->setSWidth('100');

        $draftField = new Field('approved');
        $draftField->setBSearchable('false');
        $draftField->setSWidth('82');

        $privateField = new Field('private');
        $privateField->setBSearchable('false');
        $privateField->setSWidth('82');

        $publishedField = new Field('isPublished');
        $publishedField->setBSearchable('false');
        $publishedField->setSWidth('120');

        $commentableField = new Field('isCommentable');
        $commentableField->setBSearchable('false');
        $commentableField->setSWidth('120');

        $publishedActionField = new Field(null);
        $publishedActionField->setSWidth('28');
        $publishedActionField->setMRender("render_recipe_publish_icon(data, type, full)");

        $idField = new Field('id');
        $idField->setBSearchable('false');
        $idField->setBSortable('false');
        $idField->setMRender("render_actions_icons(data, type, full)");
        $idField->setSWidth('92');

        $this->addField($titleField);
        $this->addField($authorField);
        $this->addField($draftField);
        $this->addField($privateField);
        $this->addField($publishedField);
        $this->addField($commentableField);
        $this->addField($publishedActionField);
        $this->addField($idField);

        $this->setShowPath('recipe_show_admin');
        $this->setEditPath('recipe_edit_admin');
        $this->setDeletePath('recipe_delete_admin');
    }
}
```

### View

```twig
{{ datatable_render(datatable) }}
```
