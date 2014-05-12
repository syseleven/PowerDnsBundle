<?php
/**
 * powerdns-api
 * 
 * @author Markus Seifert <m.seifert@syseleven.de>
 */

namespace SysEleven\PowerDnsBundle\Tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


/**
 * @author Markus Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Tests\Controller
 */
class ApiSoaControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $domainID = 1;
        $client = static::createClient();
        $client->request('GET', '/api/domains/'.$domainID.'/soa.json',array());

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('success',$cnt['status']);
        $this->assertArrayHasKey('data', $cnt);

        $domainID = 1;
        $client = static::createClient();
        $client->request('GET', '/api/domains/99999999/soa.json',array());

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('error',$cnt['status']);
        $this->assertArrayHasKey('errors',$cnt);

    }

    public function testDelete()
    {
        $domainID = 1;
        $client = static::createClient();
        $client->request('DELETE', '/api/domains/'.$domainID.'/soa.json',array());

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('success',$cnt['status']);
        $this->assertArrayHasKey('data', $cnt);

        $domainID = 1;
        $client = static::createClient();
        $client->request('GET', '/api/domains/1/soa.json',array());

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('error',$cnt['status']);
        $this->assertArrayHasKey('errors',$cnt);
    }


    public function testCreate()
    {
        $domainID = 1;
        $client = static::createClient();
        $client->followRedirects(true);
        $client->request('POST', '/api/domains/'.$domainID.'/soa.json',array('default_ttl' => 100));

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('success',$cnt['status']);
        $this->assertArrayHasKey('data', $cnt);
        $this->assertArrayHasKey('content', $cnt['data']);
        $this->assertArrayHasKey('name', $cnt['data']);
        $data = $cnt['data'];
        $this->assertEquals('foo.de', $data['name']);
        $this->assertArrayHasKey('default_ttl',$data['content']);
        $this->assertEquals('100', $data['content']['default_ttl']);
    }

    public function testUpdate()
    {
        $domainID = 1;
        $client = static::createClient();
        $client->followRedirects(true);
        $client->request('PUT', '/api/domains/'.$domainID.'/soa.json',array('default_ttl' => 3600));

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('success',$cnt['status']);
        $this->assertArrayHasKey('data', $cnt);
        $this->assertArrayHasKey('content', $cnt['data']);
        $this->assertArrayHasKey('name', $cnt['data']);
        $data = $cnt['data'];
        $this->assertEquals('foo.de', $data['name']);
        $this->assertArrayHasKey('default_ttl',$data['content']);
        $this->assertEquals('3600', $data['content']['default_ttl']);
    }



}