<?php

/*
 * The authors of the original implementation:
 *     Félix-Antoine Paradis (https://gist.github.com/reel/1638094) and
 *     Chad Sikorra (https://github.com/LanKit/DatatablesBundle)
 */

namespace Sg\DatatablesBundle\Datatable;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;

/**
 * Datatable data class.
 */
class DatatableData
{
    /**
     * @var array
     */
    private $requestParams;

    /**
     * @var ClassMetadata
     */
    private $metadata;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var object
     */
    private $serializer;

    /**
     * @var object
     */
    private $logger;

    /**
     * Information for DataTables to use for rendering
     *
     * @var int
     */
    private $sEcho;

    /**
     * Global search field
     *
     * @var string
     */
    private $sSearch;

    /**
     * Display start point in the current data set
     *
     * @var int
     */
    private $iDisplayStart;

    /**
     * Number of records that the table can display in the current draw
     *
     * @var int
     */
    private $iDisplayLength;

    /**
     * True if the global filter should be treated as a regular expression for advanced filtering, false if not
     *
     * @var boolean
     */
    private $bRegex;

    /**
     * Number of columns being displayed
     *
     * @var int
     */
    private $iColumns;

    /**
     * Number of columns to sort on
     *
     * @var int
     */
    private $iSortingCols;

    /**
     * Column being sorted on
     *
     * @var int
     */
    private $iSortCol0;

    /**
     * Direction to be sorted - "desc" or "asc"
     *
     * @var string
     */
    private $sSortDir0;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var array
     */
    private $selectFields;

    /**
     * @var array
     */
    private $joins;

    /**
     * @var QueryBuilder
     */
    private $qb;

    /**
     * @var mixed
     */
    private $rootEntityIdentifier;

    /**
     * @var array
     */
    private $callbacks;

    /**
     * @var array
     */
    private $response;


    //-------------------------------------------------
    // Ctor.
    //-------------------------------------------------

    /**
     * Ctor.
     *
     * @param array         $requestParams All GET params
     * @param ClassMetadata $metadata      A ClassMetadata instance
     * @param EntityManager $em            A EntityManager instance
     * @param object        $serializer    The jms_serializer service
     * @param object        $logger        The logger
     */
    public function __construct($requestParams, ClassMetadata $metadata, EntityManager $em, $serializer, $logger)
    {
        $this->requestParams  = $requestParams;
        $this->metadata       = $metadata;
        $this->em             = $em;
        $this->serializer     = $serializer;
        $this->logger         = $logger;

        $this->sEcho          = (int) $this->requestParams['sEcho'];
        $this->sSearch        = $this->requestParams['sSearch'];
        $this->iDisplayStart  = $this->requestParams['iDisplayStart'];
        $this->iDisplayLength = $this->requestParams['iDisplayLength'];
        $this->bRegex         = $this->requestParams['bRegex'];
        $this->iColumns       = $this->requestParams['iColumns'];
        $this->iSortingCols   = $this->requestParams['iSortingCols'];
        $this->iSortCol0      = $this->requestParams['iSortCol_0'];
        $this->sSortDir0      = $this->requestParams['sSortDir_0'];

        $this->tableName      = $metadata->getTableName();
        $this->selectFields   = array();
        $this->joins          = array();
        $this->qb             = $this->em->createQueryBuilder();

        $identifiers                = $this->metadata->getIdentifierFieldNames();
        $this->rootEntityIdentifier = array_shift($identifiers);

        $this->callbacks = array(
            'WhereBuilder' => array(),
            );

        $this->response = array();

        $this->prepareFields();
    }


    //-------------------------------------------------
    // Private
    //-------------------------------------------------

    /**
     * Prepare fields from mDataProp_ for createQueryBuilder.
     *
     * @return DatatableData
     */
    private function prepareFields()
    {
        $selectFields = array();
        $joins = array();

        for ($i = 0; $i < $this->iColumns; $i++) {
            if ($this->requestParams['mDataProp_' . $i] != null) {

                $field = $this->requestParams['mDataProp_' . $i];

                // found association?
                if (strstr($field, '_') !== false) {

                    // separate fields
                    $twoFieldsArray = explode('_', $field, 2);
                    $targetEntity = $twoFieldsArray[0];
                    $targetField = $twoFieldsArray[1];

                    // check association
                    if ($this->metadata->hasAssociation($targetEntity) === true) {

                        $targetClass = $this->metadata->getAssociationTargetClass($targetEntity);
                        $targetMeta = $this->em->getClassMetadata($targetClass);
                        $targetTableName = $targetMeta->getTableName();

                        $selectFields[] = $targetTableName . '.' . $targetField . ' AS ' . $field;
                        $joins[] = array(
                            'source' => $this->tableName . '.' . $targetEntity,
                            'target' => $targetTableName
                        );
                    }

                } else {

                    $selectFields[] = $this->tableName . '.' . $field;

                }

            }
        }

        $this->selectFields = $selectFields;
        $this->joins = $joins;

        return $this;
    }

    /**
     * Count all results.
     *
     * @return int
     */
    private function getCountAllResults()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('count(' . $this->tableName . '.' . $this->rootEntityIdentifier . ')');
        $qb->from($this->metadata->getName(), $this->tableName);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Set select statement.
     *
     * @return DatatableData
     */
    private function setSelect()
    {
        $this->qb->select(implode(',', $this->selectFields));
        $this->qb->from($this->metadata->getName(), $this->tableName);

        return $this;
    }

    /**
     * Set joins.
     *
     * @return DatatableData
     */
    private function setJoins()
    {
        foreach ($this->joins as $join) {
            $this->qb->leftJoin($join['source'], $join['target']);
        }

        return $this;
    }

    /**
     * Set where statement.
     *
     * @return DatatableData
     */
    private function setFilter()
    {
        // global
        if (isset($this->requestParams['sSearch']) && $this->sSearch != '') {

            $orExpr = $this->qb->expr()->orX();

            for ($i = 0; $i < $this->iColumns; $i++) {

                if (isset($this->requestParams['bSearchable_' . $i]) && $this->requestParams['bSearchable_' . $i] === 'true') {

                    // delete "AS" from selectFields[]
                    $string = $this->selectFields[$i];
                    $pos = strpos($string, 'AS');
                    $searchField = substr($string, 0, $pos);

                    if ($pos === false) {
                        $searchField = $this->selectFields[$i];
                    }

                    $orExpr->add($this->qb->expr()->like(
                        $searchField,
                        "?$i"
                    ));

                    $this->qb->setParameter($i, "%" . $this->sSearch . "%");
                }

            }

            $this->qb->where($orExpr);
        }

        return $this;
    }

    /**
     * Set Where callback functions.
     *
     * @return DatatableData
     */
    private function setWhereCallbacks()
    {
        if (!empty($this->callbacks['WhereBuilder'])) {
            foreach ($this->callbacks['WhereBuilder'] as $callback) {
                $callback($this->qb);
            }
        }

        return $this;
    }

    /**
     * Set order statement.
     *
     * @return DatatableData
     */
    private function setOrder()
    {
        if (isset($this->iSortCol0)) {
            for ($i = 0; $i < intval($this->requestParams['iSortingCols']); $i++) {
                if ($this->requestParams['bSortable_'.intval($this->requestParams['iSortCol_'. $i])] == 'true') {
                    $this->qb->addOrderBy(
                        $this->selectFields[$this->requestParams['iSortCol_'.$i]],
                        $this->requestParams['sSortDir_'.$i]
                    );
                }
            }
        }

        return $this;
    }

    /**
     * Set the scope of the resultset (Paging).
     *
     * @return DatatableData
     */
    private function setLimit()
    {
        if (isset($this->iDisplayStart) && $this->iDisplayLength != '-1') {
            $this->qb->setFirstResult($this->iDisplayStart)->setMaxResults($this->iDisplayLength);
        }

        return $this;
    }

    /**
     * Set all statements.
     *
     * @return DatatableData
     */
    private function buildQuery()
    {
        $this->setSelect();
        $this->setJoins();
        $this->setFilter();
        $this->setWhereCallbacks();
        $this->setOrder();
        $this->setLimit();

        return $this;
    }

    /**
     * Execute query and build output array.
     *
     * @return DatatableData
     */
    private function executeQuery()
    {
        $fresults = $this->qb->getQuery()->getResult(Query::HYDRATE_ARRAY);

        var_dump($this->qb->getQuery()->getDQL());

        $output = array("aaData" => array());

        foreach ($fresults as $item) {
            $output['aaData'][] = $item;
        }

        $outputHeader = array(
            "sEcho" => $this->sEcho,
            "iTotalRecords" => $this->getCountAllResults(),
            "iTotalDisplayRecords" => $this->getCountAllResults()
        );

        $this->response = array_merge($outputHeader, $output);

        return $this;
    }


    //-------------------------------------------------
    // Public
    //-------------------------------------------------

    /**
     * Get results.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getSearchResults()
    {
        $this->buildQuery();
        $this->executeQuery();

        $this->serializer->setSerializeNull(true);

        $response = new Response($this->serializer->serialize($this->response, 'json'));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Add callback function.
     *
     * @param string $callback
     *
     * @return DatatableData
     * @throws \Exception
     */
    public function addWhereBuilderCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new \Exception("The callback argument must be callable.");
        }

        $this->callbacks['WhereBuilder'][] = $callback;

        return $this;
    }
}
