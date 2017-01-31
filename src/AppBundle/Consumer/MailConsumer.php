<?php

namespace AppBundle\Consumer;

use AppBundle\Entity\User;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class MailConsumer implements ConsumerInterface
{
    private $entityManager;
    
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    public function execute(AMQPMessage $msg)
    {
        //php bin/console rabbitmq:consumer -w email
        $data = json_decode($msg->body);
        $user = $this->entityManager->getRepository(User::class)->find($data->id);
        echo $user->getUsername() . ' ' . $data->text . PHP_EOL;
        return true;
    }
}
