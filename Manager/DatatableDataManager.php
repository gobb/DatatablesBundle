<?php

namespace Sg\DatatablesBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Sg\DatatablesBundle\Datatable\DatatableData;

/**
 * Datatable data manager class.
 */
class DatatableDataManager
{
    /**
     * @var RegistryInterface
     */
    private $doctrine;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Ctor.
     *
     * @param RegistryInterface  $doctrine  A RegistryInterface instance
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function __construct(RegistryInterface $doctrine, ContainerInterface $container)
    {
        $this->doctrine = $doctrine;
        $this->container = $container;
    }

    /**
     * @param string $entity
     *
     * @return DatatableData A DatatableData instance
     */
    public function getDatatable($entity)
    {
        /**
         * Get all GET params
         *
         * @var \Symfony\Component\HttpFoundation\ParameterBag $parameterBag
         */
        $parameterBag = $this->container->get('request')->query;
        $params = $parameterBag->all();

        /**
         * @var \Doctrine\ORM\Mapping\ClassMetadata $metadata
         */
        $metadata = $this->doctrine->getManager()->getClassMetadata($entity);

        /**
         * @var \Doctrine\ORM\EntityManager $em
         */
        $em = $this->doctrine->getManager();

        return new DatatableData(
            $params,
            $metadata,
            $em,
            $this->container->get('jms_serializer'),
            $this->container->get('logger')
        );
    }
}

