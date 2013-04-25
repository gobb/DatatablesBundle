# DatatablesBundle

## Installation

Neben dem DatatablesBundle werden noch
* jQuery,
* das DataTables-Plugin,
* Bootstrap und
* das JMSSerializerBundle benötigt.

Dazu wird die composer.json erweitert:

```js
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
        "jms/serializer-bundle": "*",
        "sg/datatablesbundle": "dev-master"
    },
}
```

Den Download starten mit:

``` bash
$ php composer.phar update
```

## Bundles aktivieren

```php
<?php

    // app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(

            new JMS\SerializerBundle\JMSSerializerBundle(),
            new Sg\DatatablesBundle\SgDatatablesBundle(),
        );
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

## Ein Anwendungsbeispiel

### Controller

```php
<?php

    // PostController.php

    /**
     * Lists all Post entities.
     *
     * @Route("/", name="post")
     * @Method("GET")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        /**
         * @var \Sg\DatatablesBundle\Factory\DatatableFactory $factory
         */
        $factory = $this->get('sg_datatables.factory');

        /**
         * @var \Sg\DatatablesBundle\Datatable\DatatableView $datatableView
         */
        $datatableView = $factory->getTable('Sg\AppBundle\Datatables\PostDatatable');

        return array(
            'datatable' => $datatableView
        );
    }

    /**
     * @Route("/results", name="post_results")
     *
     * @return array
     */
    public function resultsAction()
    {
        /**
         * @var \Sg\DatatablesBundle\Datatable\DatatableData $datatable
         */
        $datatable = $this->get('sg_datatables')->getDatatable('SgAppBundle:Post');

        return $datatable->getSearchResults();
    }
```

### DatatableView class

```php

// PostDatatable.php

<?php

    namespace Sg\AppBundle\Datatables;

    use Sg\DatatablesBundle\Datatable\DatatableView;
    use Sg\DatatablesBundle\Datatable\Field;

    /**
     * Post datatable view class.
     */
    class PostDatatable extends DatatableView
    {
        /**
         * {@inheritdoc}
         */
        public function build()
        {
            $this->setTableId('post_datatable');
            $this->setSAjaxSource('post_results');

            $this->setTableHeaders(array(
                    'Titel',
                    ''
                ));

            $titleField = new Field('title');

            $idField = new Field('id');
            $idField->setBSearchable('false');
            $idField->setBSortable('false');
            $idField->setMRender("render_actions_icons(data, type, full)");
            $idField->setSWidth('92');

            $this->addField($titleField);
            $this->addField($idField);

            $this->setShowPath('post_show');
            $this->setEditPath('post_edit');
            $this->setDeletePath('post_delete');
        }
    }
```

### View

```twig
{# index.html.twig #}

{% extends 'SgAppBundle::layout.html.twig' %}

{% block body %}

    {{ datatable_render(datatable) }}

{% endblock %}
```
