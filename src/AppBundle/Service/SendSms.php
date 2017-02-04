<?php

namespace AppBundle\Service;

use Httpful\Request;
use Httpful\Http;

class SendSms
{
    public function send($number, $text)
    {
        $data = http_build_query([
            'number' => $number,
            'text' => $text,
        ]);

        $result = Request::get("http://example.com/test/?$data")->send();

        if ($result->code == 200) {
            return true;
        }

        return false;
    }
}
