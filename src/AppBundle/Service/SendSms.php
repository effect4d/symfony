<?php 

namespace AppBundle\Service;

class SendSms
{    
    public function send()
    {
        return (bool)random_int(0, 1);
    }
}
