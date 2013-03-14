# DatatablesBundle


## Config layout.html.twig

- [Add twitter/bootstrap to your layout](https://github.com/twitter/bootstrap).

- Add DataTables v1.9.4 plug-in to your layout:

```twig
{% block javascripts %}

    <script src="{{ asset('bundles/sgdatatables/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('bundles/sgdatatables/js/dataTables_bootstrap.js') }}" type="text/javascript"></script>
    <script src="{{ asset('bundles/sgdatatables/js/strtr.js') }}" type="text/javascript"></script>

{% endblock %}

{% block stylesheets %}

    <link href="{{ asset('bundles/sgdatatables/css/dataTables_bootstrap.css') }}" rel="stylesheet" type="text/css" />

{% endblock %}
```


## Example

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
