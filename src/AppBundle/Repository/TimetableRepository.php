<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Timetable;

class TimetableRepository extends EntityRepository
{
    public function findMyTimetables($userId)
    {
        $entityManager = $this->getEntityManager()->getRepository(Timetable::class);
        $query = $entityManager->createQueryBuilder('timetable')
            ->select('timetable.id, timetable.name, timetable.trainer, timetable.description, subscriptions.id as sid, subscriptions.type')
            ->leftJoin('timetable.subscriptions', 'subscriptions', 'WITH', 'subscriptions.user = :user')
            ->setParameter('user', $userId)
            ->groupBy('timetable.id')
            ->getQuery();

        return $query->getResult();
    }
}
