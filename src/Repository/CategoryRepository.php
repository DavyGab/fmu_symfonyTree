<?php

namespace App\Repository;

use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class CategoryRepository extends NestedTreeRepository
{

    public function updateAllChildrenPath($oldPath, $newPath, $left, $right)
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata('App\Entity\Category', 'c');

        $em = $this->getEntityManager();
        $tableName = $em->getClassMetadata('App\Entity\Category')->getTableName();

        $sql = 'UPDATE '. $tableName .' c
            SET c.path = REPLACE(c.path, :oldPath, :newPath)
            WHERE c.lft > :left AND c.rgt < :right';

        $stmt = $em->getConnection()->prepare($sql);
        $params = array(
            'oldPath'=> $oldPath,
            'newPath'=> $newPath,
            'left'=> $left,
            'right'=> $right
        );
        $stmt->execute($params);

    }
}