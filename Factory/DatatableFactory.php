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
     * Creates a new instance of the datagridViewClass object.
     *
     * @param string $datagridViewClass
     *
     * @return \Sg\DatatablesBundle\Datatable\DatatableView
     * @throws \Exception
     */
    public function getTable($datagridViewClass)
    {
        if (!class_exists($datagridViewClass)) {
            throw new \Exception("Class {$datagridViewClass} not found.");
        }

        return new $datagridViewClass($this->twig);
    }
}
