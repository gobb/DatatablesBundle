# DatatablesBundle

## Installation

Neben dem DatatablesBundle werden noch
* jQuery,
* das DataTables-Plugin,
* Bootstrap und
* das JMSSerializerBundle benötigt.

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
