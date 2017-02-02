<?php

namespace AppBundle\Consumer;

use AppBundle\Entity\User;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class MailConsumer implements ConsumerInterface
{
    private $entityManager;
    private $mailer;
    private $logger;
    
    public function __construct($entityManager, $mailer, $logger)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->logger = $logger;
    }
    
    /**
     * php bin/console rabbitmq:consumer -w email
     */
    public function execute(AMQPMessage $msg)
    {
        $data = json_decode($msg->body);
        $user = $this->entityManager->getRepository(User::class)->find($data->id);
        
        $this->logger->info('Sent mail to ' . $user->getUsername() . ' ' . $user->getEmail() . ' text: ' . $data->text);
        
        $message = \Swift_Message::newInstance()
            ->setSubject('Hello Email')
            ->setFrom('send@example.com')
            ->setTo($user->getEmail())
            ->setBody($data->text, 'text/html');

        $this->mailer->send($message);
    
        return true;
    }
}
