<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use AppBundle\Entity\User;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170131170252 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $user = new User;
        $password = $this->container->get('security.password_encoder')->encodePassword($user, '123456');
        
        $this->addSql("INSERT INTO `users` (`username`, `password`, `email`, `phone`, `is_active`, `role`) 
            VALUES ('admin', '$password', 'admin@example.com', '+79876543210', '1', 'ROLE_ADMIN');");
        
        $this->addSql("INSERT INTO `users` (`username`, `password`, `email`, `phone`, `is_active`, `role`) 
            VALUES ('demo', '$password', 'demo@example.com', '+79876543210', '1', 'ROLE_USER');");


    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
