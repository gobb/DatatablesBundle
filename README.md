# DatatablesBundle

Das DatatablesBundle soll die Zusammenarbeit mit Symfony2 vereinfachen.
Es verarbeitet die Daten serverseitig, nutzt eine eigene Klasse und Twig bzw. eine Twig-Erweiterung für deren Darstellung.

## Installation

Neben dem DatatablesBundle werden noch
* jQuery,
* Bootstrap und
* das JMSSerializerBundle benötigt.

Die Einbindung kann dann z.B. über die layout.html.twig (ohne Assetic) erfolgen:

```twig
{# layout.html.twig #}

{% extends '::base.html.twig' %}

{% block title %}AppBundle{% endblock %}

{% block javascripts %}
    <script src="{{ asset('bundles/sgapp/js/jquery-2.0.0.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('bundles/sgapp/js/bootstrap.min.js') }}" type="text/javascript"></script>

    <script src="{{ asset('bundles/sgdatatables/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('bundles/sgdatatables/js/dataTables_bootstrap.js') }}" type="text/javascript"></script>
    <script src="{{ asset('bundles/sgdatatables/js/strtr.js') }}" type="text/javascript"></script>
{% endblock %}

{% block stylesheets %}
    <link href="{{ asset('bundles/sgapp/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />

    <link href="{{ asset('bundles/sgdatatables/css/dataTables_bootstrap.css') }}" rel="stylesheet" type="text/css" />
{% endblock %}

{% block body%}
    <div class="container">
        {% block content %}
        {% endblock %}
    </div>
{% endblock %}
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
         * @var \Sg\DatatablesBundle\Datatable\AbstractDatatableView $datatableView
         */
        $datatableView = $factory->getDatatableView('Sg\AppBundle\Datatables\PostDatatable');

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
<?php

    // PostDatatable.php

    namespace Sg\AppBundle\Datatables;

    use Sg\DatatablesBundle\Datatable\AbstractDatatableView;
    use Sg\DatatablesBundle\Datatable\Field;

    /**
     * Post datatable view class.
     */
    class PostDatatable extends AbstractDatatableView
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

{% block content %}

    {{ datatable_render(datatable) }}

{% endblock %}
```
