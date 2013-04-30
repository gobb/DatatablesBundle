<?php

namespace Sg\DatatablesBundle\Datatable;

/**
 * Field class.
 */
class Field
{
    /**
     * @var string
     */
    private $mData;

    /**
     * @var string
     */
    private $mRender;

    /**
     * @var string
     */
    private $sWidth;

    /**
     * @var string
     */
    private $bSearchable;

    /**
     * @var string
     */
    private $bSortable;


    //-------------------------------------------------
    // Ctor.
    //-------------------------------------------------

    /**
     * Ctor.
     *
     * @param string $mData
     */
    public function __construct($mData)
    {
        $this->mData       = $mData;
        $this->mRender     = '';
        $this->bSearchable = "true";
        $this->bSortable   = "true";
    }


    //-------------------------------------------------
    // Public
    //-------------------------------------------------

    /**
     * @param string $bSearchable
     *
     * @return Field
     */
    public function setBSearchable($bSearchable)
    {
        $this->bSearchable = $bSearchable;

        return $this;
    }

    /**
     * @return string
     */
    public function getBSearchable()
    {
        return $this->bSearchable;
    }

    /**
     * @param string $bSortable
     *
     * @return Field
     */
    public function setBSortable($bSortable)
    {
        $this->bSortable = $bSortable;

        return $this;
    }

    /**
     * @return string
     */
    public function getBSortable()
    {
        return $this->bSortable;
    }

    /**
     * @param string $mData
     *
     * @return Field
     */
    public function setMData($mData)
    {
        $this->mData = $mData;

        return $this;
    }

    /**
     * @return string
     */
    public function getMData()
    {
        return $this->mData;
    }

    /**
     * @param string $mRender
     *
     * @return Field
     */
    public function setMRender($mRender)
    {
        $this->mRender = $mRender;

        return $this;
    }

    /**
     * @return string
     */
    public function getMRender()
    {
        return $this->mRender;
    }

    /**
     * @param string $sWidth
     *
     * @return Field
     */
    public function setSWidth($sWidth)
    {
        $this->sWidth = $sWidth;

        return $this;
    }

    /**
     * @return string
     */
    public function getSWidth()
    {
        return $this->sWidth;
    }
}