<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DefaultTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Homepage', $crawler->filter('h1')->text());
    }
    
    /**
     * @dataProvider getUrlsForRegularUsers
     */
    public function testRegularUsers($httpMethod, $url, $statusCode)
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'demo@example.com',
            'PHP_AUTH_PW' => '123456',
        ]);

        $client->request($httpMethod, $url);
        $this->assertEquals($statusCode, $client->getResponse()->getStatusCode());
    }

    public function getUrlsForRegularUsers()
    {
        yield ['GET', '/admin', Response::HTTP_FORBIDDEN];
        yield ['GET', '/admin/timetable/create', Response::HTTP_FORBIDDEN];
        yield ['GET', '/admin/user/create', Response::HTTP_FORBIDDEN];
    }
    
    /**
     * @dataProvider getUrlsForAdminUsers
     */
    public function testAdminUsers($httpMethod, $url, $statusCode)
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'admin@example.com',
            'PHP_AUTH_PW' => '123456',
        ]);

        $client->request($httpMethod, $url);
        $this->assertEquals($statusCode, $client->getResponse()->getStatusCode());
    }

    public function getUrlsForAdminUsers()
    {
        yield ['GET', '/admin', Response::HTTP_OK];
        yield ['GET', '/admin/timetable/create', Response::HTTP_OK];
        yield ['GET', '/admin/user/create', Response::HTTP_OK];
    }
    
    /**
     * @dataProvider getPublicUrls
     */
    public function testPublicUrls($url)
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertTrue(
            $client->getResponse()->isSuccessful(),
            sprintf('The %s public URL loads correctly.', $url)
        );
    }

    /**
     * @dataProvider getSecureUrls
     */
    public function testSecureUrls($url)
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isRedirect());

        $this->assertEquals(
            'http://localhost/login',
            $client->getResponse()->getTargetUrl(),
            sprintf('The %s secure URL redirects to the login form.', $url)
        );
    }

    public function getPublicUrls()
    {
        yield ['/'];
        yield ['/login'];
    }

    public function getSecureUrls()
    {
        yield ['/admin'];
        yield ['/timetable'];
    }
}
