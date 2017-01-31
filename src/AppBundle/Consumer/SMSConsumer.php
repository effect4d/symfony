<?php

namespace AppBundle\Consumer;

use AppBundle\Entity\User;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class SMSConsumer implements ConsumerInterface
{
    private $entityManager;
    private $rabbit;
    private $logger;
    
    public function __construct($entityManager, $rabbit, $logger)
    {
        $this->entityManager = $entityManager;
        $this->rabbit = $rabbit;
        $this->logger = $logger;
    }
    
    /**
     * php bin/console rabbitmq:consumer -w sms
     */
    public function execute(AMQPMessage $msg)
    {
        $data = json_decode($msg->body);
        $user = $this->entityManager->getRepository(User::class)->find($data->id);
        
        $this->logger->info('Sent sms to ' . $user->getUsername() . ' ' . $user->getPhone() . ' text: ' . $data->text);
        
        if (!$this->sendSms($user->getPhone(), $data->text)) {
            $this->logger->info('Resend sms to ' . $user->getUsername() . ' ' . $user->getPhone() . ' in 10 munites');
            
            $this->rabbit->publish(json_encode([
                'id' => $user->getId(),
                'text' => $data->text,
            ]), '', [], ['x-delay' => 3000]);
        }
        
        return true;
    }
    
    public function sendSms($number, $text)
    {
        return (bool)random_int(0, 1);
    }
}
