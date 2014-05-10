<?php
/**
 * powerdns-api
 * @author   M. Seifert <m.seifert@syseleven.de>
  * @package SysEleven\PowerDnsBundle\Tests\Controller
 */

namespace SysEleven\PowerDnsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class ApiRecordsControllerTest extends WebTestCase
{

    public function testIndex()
    {
        $domainID = 1;
        $client = static::createClient();
        $client->request('GET', '/powerdns/api/domains/'.$domainID.'/records.json',array());

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('success',$cnt['status']);
        $this->assertArrayHasKey('data', $cnt);

        $domainID = 1;
        $client = static::createClient();
        $client->request('GET', '/powerdns/api/domains/99999999/records.json',array());

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('error',$cnt['status']);
        $this->assertArrayHasKey('errors',$cnt);

    }


    public function testCreate()
    {
        $domainID = 1;

        $data = array('name' => 'www.foo.de',
                      'type' => 'A',
                      'content' => '1.2.3.4');

        $client = static::createClient();
        $client->followRedirects(true);
        $client->request('POST', '/powerdns/api/domains/'.$domainID.'/records.json',$data);

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('success',$cnt['status']);
        $this->assertArrayHasKey('data', $cnt);
        $this->assertArrayHasKey('id', $cnt['data']);
        $this->assertNotEmpty($cnt['data']['id']);

        $recordID = $cnt['data']['id'];

        $data = array('name' => 'www.foo.com',
                      'type' => 'A',
                      'content' => '1.2.3.4');

        $client = static::createClient();
        $client->followRedirects(true);
        $client->request('POST', '/powerdns/api/domains/'.$domainID.'/records.json',$data);

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('error',$cnt['status']);
        $this->assertArrayHasKey('errors', $cnt);


        $data = array('name' => 'www.foo.com',
                      'type' => 'A',
                      'content' => '1.2.3.4',
                      'force' => 1);

        $client = static::createClient();
        $client->followRedirects(true);
        $client->request('POST', '/powerdns/api/domains/'.$domainID.'/records.json',$data);

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('success',$cnt['status']);
        $this->assertArrayHasKey('data', $cnt);


        $data = array('name' => 'www.foo.co.uk',
                      'type' => 'A',
                      'content' => '1.2.3.4');

        $client = static::createClient();
        $client->followRedirects(true);
        $client->request('POST', '/powerdns/api/domains/99999/records.json',$data);

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('error',$cnt['status']);
        $this->assertArrayHasKey('errors', $cnt);


        return $recordID;
    }

    /**
     * @param $recordID
     * @depends testCreate
     */
    public function testGet($recordID)
    {
        $domainID = 1;
        $client = static::createClient();
        $client->followRedirects(true);
        $client->request('GET', '/powerdns/api/domains/'.$domainID.'/records/'.$recordID.'.json',array());

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('success',$cnt['status']);
        $this->assertArrayHasKey('data', $cnt);


        $domainID = 1;
        $client = static::createClient();
        $client->followRedirects(true);
        $client->request('GET', '/powerdns/api/domains/'.$domainID.'/records/99999.json',array());

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('error',$cnt['status']);
        $this->assertArrayHasKey('errors', $cnt);

        $domainID = 1;
        $client = static::createClient();
        $client->followRedirects(true);
        $client->request('GET', '/powerdns/api/domains/'.$domainID.'/records/8.json',array());

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('error',$cnt['status']);
        $this->assertArrayHasKey('errors', $cnt);

        return $recordID;
    }

    /**
     * @depends testGet
     */
    public function testUpdate($recordID)
    {
        $data = array('name' => 'www.foo.de',
                      'type' => 'A',
                      'content' => '1.2.3.5',
                      'managed' => 1);

        $client = static::createClient();
        $client->followRedirects(true);
        $client->request('PUT', '/powerdns/api/domains/1/records/'.$recordID.'.json',$data);

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('success',$cnt['status']);
        $this->assertArrayHasKey('data', $cnt);
        $this->assertArrayHasKey('content', $cnt['data']);
        $this->assertEquals('1.2.3.5',$cnt['data']['content']);
        $this->assertArrayHasKey('managed',$cnt['data']);
        $this->assertEquals(1, $cnt['data']['managed']);


        $data = array('name' => 'www.foo.com',
                      'type' => 'A',
                      'content' => '1.2.3.5',
                      'managed' => 1);

        $client = static::createClient();
        $client->followRedirects(true);
        $client->request('PUT', '/powerdns/api/domains/1/records/'.$recordID.'.json',$data);

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('error',$cnt['status']);
        $this->assertArrayHasKey('errors', $cnt);

        $data = array('name' => 'www.foobar.com',
                      'type' => 'A',
                      'content' => '1.2.3.5',
                      'managed' => 1, 'force' => 1);

        $client = static::createClient();
        $client->followRedirects(true);
        $client->request('PUT', '/powerdns/api/domains/1/records/'.$recordID.'.json',$data);

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('success',$cnt['status']);
        $this->assertArrayHasKey('data', $cnt);


        $client = static::createClient();
        $client->followRedirects(true);
        $client->request('PUT', '/powerdns/api/domains/1/records/8.json', $data);

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('error',$cnt['status']);
        $this->assertArrayHasKey('errors', $cnt);

        $client = static::createClient();
        $client->followRedirects(true);
        $client->request('PUT', '/powerdns/api/domains/999999/records/8.json', $data);

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('error',$cnt['status']);
        $this->assertArrayHasKey('errors', $cnt);

        return $recordID;
    }

    /**
     * @param $recordID
     *
     * @return mixed
     * @depends testUpdate
     */
    public function testHistory($recordID)
    {

        $domainID = 1;
        $client = static::createClient();
        $client->followRedirects(true);
        $client->request('GET', '/powerdns/api/domains/'.$domainID.'/records/'.$recordID.'/history.json',array());

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('success',$cnt['status']);
        $this->assertArrayHasKey('data', $cnt);
        $this->assertCount(3 ,$cnt['data']);



        $domainID = 1;
        $client = static::createClient();
        $client->followRedirects(true);
        $client->request('GET', '/powerdns/api/domains/'.$domainID.'/records/99999/history.json',array());

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('error',$cnt['status']);
        $this->assertArrayHasKey('errors', $cnt);

        $domainID = 1;
        $client = static::createClient();
        $client->followRedirects(true);
        $client->request('GET', '/powerdns/api/domains/'.$domainID.'/records/8/history.json',array());

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('error',$cnt['status']);
        $this->assertArrayHasKey('errors', $cnt);

        return $recordID;
    }


    /**
     * @depends testHistory
     */
    public function testDelete($recordID)
    {
        $client = static::createClient();
        $client->followRedirects(true);
        $client->request('DELETE', '/powerdns/api/domains/1/records/8.json', array());

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('error',$cnt['status']);
        $this->assertArrayHasKey('errors', $cnt);

        $client = static::createClient();
        $client->followRedirects(true);
        $client->request('DELETE', '/powerdns/api/domains/999999/records/8.json', array());

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('error',$cnt['status']);
        $this->assertArrayHasKey('errors', $cnt);

        $client = static::createClient();
        $client->followRedirects(true);
        $client->request('DELETE', '/powerdns/api/domains/1/records/'.$recordID.'.json', array());

        $cnt = $client->getResponse()->getContent();
        $cnt = json_decode($cnt, true);

        $this->assertArrayHasKey('status', $cnt);
        $this->assertEquals('success',$cnt['status']);
        $this->assertArrayHasKey('data', $cnt);
    }
}
 