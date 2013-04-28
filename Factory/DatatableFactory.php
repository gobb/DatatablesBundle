<?php

namespace Sg\DatatablesBundle\Factory;

use Twig_Environment as Twig;

/**
 * Datatable factory class.
 */
class DatatableFactory
{
    /**
     * @var Twig
     */
    protected $twig;

    /**
     * Ctor.
     *
     * @param Twig $twig A Twig instance
     */
    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Returns a instance of the datatableViewClass.
     *
     * @param string $datatableViewClass The class name
     *
     * @return \Sg\DatatablesBundle\Datatable\DatatableView
     * @throws \Exception
     */
    public function getDatatableView($datatableViewClass)
    {
        if (!class_exists($datatableViewClass)) {
            throw new \Exception("Class {$datatableViewClass} not found.");
        }

        return new $datatableViewClass($this->twig);
    }
}
