<?php
/**
 * Released under the MIT License.
 *
 * Copyright (c) 2012 - 2014 Miha Vrhovnik <miha.vrhovnik@cordia.si>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace mvrhov\Bundle\ResourceBundle\Doctrine\ORM;

use Doctrine\ORM\EntityRepository as BaseEntityRepository;
use Doctrine\ORM\QueryBuilder;
use mvrhov\Bundle\ResourceBundle\Doctrine\Comparison;
use mvrhov\Bundle\ResourceBundle\Model\RepositoryInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * short description
 *
 * @author Miha Vrhovnik <miha.vrhovnik@cordia.si>
 */
class EntityRepository extends BaseEntityRepository implements RepositoryInterface
{

    /** @var  string */
    private $entityNameSpace;

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        $class = $this->getClassName();

        return new $class;
    }

    /**
     * {@inheritdoc}
     */
    public function save($instance, $andFlush = true)
    {
        $this->_em->persist($instance);

        if ($andFlush) {
            $this->_em->flush();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($instance, $andFlush = true)
    {
        $this->_em->remove($instance);

        if ($andFlush) {
            $this->_em->flush();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id, $lockMode = NULL, $lockVersion = NULL)
    {
        return $this
            ->getQueryBuilder()
            ->andWhere($this->getAlias() . '.id = ' . intval($id))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this
            ->getCollectionQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, ?array $orderBy = NULL)
    {
        $queryBuilder = $this->getQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);

        return $queryBuilder
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $queryBuilder = $this->getCollectionQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);
        $this->applySorting($queryBuilder, $orderBy);

        if (null !== $limit) {
            $queryBuilder->setMaxResults($limit);
        }

        if (null !== $offset) {
            $queryBuilder->setFirstResult($offset);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findPaginated(array $criteria, array $orderBy = null)
    {
        $queryBuilder = $this->getCollectionQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);
        $this->applySorting($queryBuilder, $orderBy);

        return $this->getPaginator($queryBuilder);
    }

    /**
    * @param QueryBuilder $queryBuilder
    *
    * @return Pagerfanta
    */
    protected function getPaginator(QueryBuilder $queryBuilder)
    {
        return new Pagerfanta(new DoctrineORMAdapter($queryBuilder, true, true));
    }

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilder()
    {
        return $this->createQueryBuilder($this->getAlias());
    }

    /**
     * Alias for this model
     *
     * @return string
     */
    protected function getAlias()
    {
        return 'o';
    }

    /**
     * @return QueryBuilder
     */
    protected function getCollectionQueryBuilder()
    {
        return $this->createQueryBuilder($this->getAlias());
    }

    /**
     * Add criteria to the query
     *
     * @param QueryBuilder $queryBuilder
     * @param array        $criteria
     */
    protected function applyCriteria(QueryBuilder $queryBuilder, array $criteria = null)
    {
        if (null === $criteria) {
            return;
        }

        $isComparison = function (array $value) {
            if (count($value) <> 1) {
                return false;
            }

            return Comparison::isComparison(current(array_keys($value)));
        };

        $alias = $this->getAlias();

        foreach ($criteria as $property => $value) {
            if (is_array($value)) {
                if ($isComparison($value)) {
                    $operator = current(array_keys($value));
                    $value    = $value[$operator];

                    switch ($operator) {
                        case Comparison::CONTAINS:
                            $queryBuilder
                                ->andWhere($alias . '.' . $property . ' LIKE :' . $property);
                            break;
                        case Comparison::IN:
                        case Comparison::NOTIN:
                            $queryBuilder
                                ->andWhere($alias . '.' . $property . ' ' . $operator . ' (:' . $property . ')');
                            break;
                        default:
                            // special case null value handling
                            if (($operator === Comparison::EQ || $operator === Comparison::IS) && (null === $value)) {
                                $queryBuilder
                                    ->andWhere($alias . '.' . $property . ' IS NULL');
                            } else if (($operator === Comparison::NEQ) && (null === $value)) {
                                $queryBuilder
                                    ->andWhere($alias . '.' . $property . ' IS NOT NULL');
                            } else {
                                $queryBuilder
                                    ->andWhere($alias . '.' . $property . ' ' . $operator . ' :' . $property);
                            }
                    }
                } else {
                    $queryBuilder
                        ->andWhere($alias . '.' . $property . ' IN (:' . $property . ')');
                }
            } else {
                if (null === $value) {
                    $queryBuilder
                        ->andWhere($alias . '.' . $property . ' IS NULL');
                } else {
                    $queryBuilder
                        ->andWhere($alias . '.' . $property . ' = :' . $property);
                }
            }

            if (null !== $value) {
                $queryBuilder
                    ->setParameter($property, $value);
            }
        }
    }

    /**
     * Add sorting to the query
     *
     * @param QueryBuilder $queryBuilder
     * @param array        $sorting
     */
    protected function applySorting(QueryBuilder $queryBuilder, array $sorting = null)
    {
        if (null === $sorting) {
            return;
        }

        $alias = $this->getAlias();

        foreach ($sorting as $property => $order) {
            $queryBuilder->orderBy($alias . '.' . $property, $order);
        }
    }

}
