<?php
/**
 * PowerDnsBundle
 *
 * @package SysEleven\PowerDnsBundle\Tests\Controller
 * @author Markus Seifert <m.seifert@syseleven.de>
 */

namespace SysEleven\PowerDnsBundle\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiSearchControllerTest extends WebTestCase
{

    public $urlPrefix = '';

    public function testHistory()
    {
        $client = static::createClient();
        $client->request('GET', $this->urlPrefix.'/api/history.json',['limit' => 1]);

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('success',$cnt['status']);
        $this->assertArrayHasKey('data', $cnt);
        $this->assertCount(1, $cnt['data']);

    }

}
