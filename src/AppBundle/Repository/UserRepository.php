<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\User;

/**
 * This custom Doctrine repository is empty because so far we don't need any custom
 * method to query for application user information. But it's always a good practice
 * to define a custom repository that will be used when the application grows.
 *
 * See http://symfony.com/doc/current/book/doctrine.html#custom-repository-classes
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class UserRepository extends EntityRepository
{
    public function findUsersNotice($timetableId)
    {
        $entityManager = $this->getEntityManager()->getRepository(User::class);
        $query = $entityManager->createQueryBuilder('user')
            ->select('user.id, user.username, user.email, user.phone, subscriptions.type')
            ->innerJoin('user.subscriptions', 'subscriptions')
            ->where('subscriptions.timetable = :timetable and user.isActive = 1')
            ->setParameter('timetable', $timetableId)
            ->getQuery();

        return $query->getResult();
    }
}
