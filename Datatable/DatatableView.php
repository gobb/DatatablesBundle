<?php

namespace Sg\DatatablesBundle\Datatable;

use Twig_Environment as Twig;

/**
 * Datatable view class.
 */
abstract class DatatableView
{
    /**
     * @var Twig
     */
    private $twig;

    /**
     * @var string
     */
    private $template;

    /**
     * @var string
     */
    private $tableId;

    /**
     * @var array
     */
    private $tableHeaders;

    /**
     * @var array
     */
    private $fields;

    /**
     * @var string
     */
    private $sAjaxSource;

    /**
     * @var boolean
     */
    private $actions;

    /**
     * @var string
     */
    private $showPath;

    /*
     * @var string
     */
    private $editPath;

    /**
     * @var string
     */
    private $deletePath;

    /**
     * @var array
     */
    private $customizeOptions;


    //-------------------------------------------------
    // Ctor
    //-------------------------------------------------

    /**
     * Ctor.
     *
     * @param Twig $twig A Twig instance
     */
    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
        $this->template = 'SgDatatablesBundle::default.html.twig';

        $this->tableId = 'sg_datatable';
        $this->tableHeaders = array();
        $this->fields = array();
        $this->sAjaxSource = '';
        $this->actions = false;
        $this->customizeOptions = array();

        $this->build();
    }


    //-------------------------------------------------
    // Build view
    //-------------------------------------------------

    /**
     * @return mixed
     */
    abstract public function build();

    /**
     * @return string
     */
    public function createView()
    {
        $options = array();
        $options['id']               = $this->getTableId();
        $options['sAjaxSource']      = $this->getSAjaxSource();
        $options['tableHeaders']     = $this->getTableHeaders();
        $options['fields']           = $this->getFieldsProperty();
        $options['actions']          = $this->getActions();
        $options['showPath']         = $this->getShowPath();
        $options['editPath']         = $this->getEditPath();
        $options['deletePath']       = $this->getDeletePath();
        $options['customizeOptions'] = $this->getCustomizeOptions();

        return $this->twig->render($this->getTemplate(), $options);
    }


    //-------------------------------------------------
    // Field functions
    //-------------------------------------------------

    /**
     * @param Field $field
     *
     * @return DatatableView
     */
    public function addField($field)
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Get fields property.
     *
     * @return array
     */
    private function getFieldsProperty()
    {
        $mData = array();

        /**
         * @var \Sg\DatatablesBundle\Datatable\Field $field
         */
        foreach ($this->fields as $field) {

            $property = array(
                'mData' => $field->getMData(),
                'mRender' => $field->getMRender(),
                'sWidth' => $field->getSWidth(),
                'bSearchable' => $field->getBSearchable(),
                'bSortable' => $field->getBSortable()
            );

            array_push($mData, $property);
        }

        return $mData;
    }


    //-------------------------------------------------
    // Getters && Setters
    //-------------------------------------------------

    /**
     * @param string $sAjaxSource
     */
    public function setSAjaxSource($sAjaxSource)
    {
        $this->sAjaxSource = $sAjaxSource;
    }

    /**
     * @return string
     */
    public function getSAjaxSource()
    {
        return $this->sAjaxSource;
    }

    /**
     * @param array $tableHeaders
     */
    public function setTableHeaders($tableHeaders)
    {
        $this->tableHeaders = $tableHeaders;
    }

    /**
     * @return array
     */
    public function getTableHeaders()
    {
        return $this->tableHeaders;
    }

    /**
     * @param string $tableId
     */
    public function setTableId($tableId)
    {
        $this->tableId = $tableId;
    }

    /**
     * @return string
     */
    public function getTableId()
    {
        return $this->tableId;
    }

    /**
     * @param boolean $actions
     */
    public function setActions($actions)
    {
        $this->actions = $actions;
    }

    /**
     * @return boolean
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param string $deletePath
     */
    public function setDeletePath($deletePath)
    {
        $this->deletePath = $deletePath;
    }

    /**
     * @return string
     */
    public function getDeletePath()
    {
        return $this->deletePath;
    }

    /**
     * @param string $editPath
     */
    public function setEditPath($editPath)
    {
        $this->editPath = $editPath;
    }

    /**
     * @return string
     */
    public function getEditPath()
    {
        return $this->editPath;
    }

    /**
     * @param string $showPath
     */
    public function setShowPath($showPath)
    {
        $this->showPath = $showPath;
    }

    /**
     * @return string
     */
    public function getShowPath()
    {
        return $this->showPath;
    }

    /**
     * @param array $customizeOptions
     */
    public function setCustomizeOptions($customizeOptions)
    {
        $this->customizeOptions = $customizeOptions;
    }

    /**
     * @return array
     */
    public function getCustomizeOptions()
    {
        return $this->customizeOptions;
    }

    /**
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }
}

