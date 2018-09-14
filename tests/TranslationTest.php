<?php

namespace Nilnice\Translate\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Mockery\Matcher\AnyArgs;
use Nilnice\Translate\Baidu;
use Nilnice\Translate\Exceptions\HttpException;
use Nilnice\Translate\Translation;
use PHPUnit\Framework\TestCase;

class TranslationTest extends TestCase
{
    public function testTranslateWithInvalidTranslator(): void
    {
        $translation = new Translation([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid translator: test');

        $translation->translate('中国', 'test');
        $this->fail('Faild to assert translate throw exception with invalid argument');
    }

    public function testTranslate(): void
    {
        $uri = 'https://fanyi-api.baidu.com/api/trans/vip/translate';
        $body = '{"from":"zh","to":"en","src":"中国","dst":""}';
        $response = new Response(200, [], $body);

        $client = \Mockery::mock(Client::class);
        $client->allows()->get($uri, [
            'query' => [
                'q' => '中国',
                'from' => 'zh',
                'to' => 'en',
                'appid' => 'mock-key',
                'salt' => time(),
                'sign' => md5('mock-key' . '中国' . time() . 'secret'),
            ],
        ])->andReturn($response);

        $translation = \Mockery::mock(Translation::class, [
            [
                'key' => 'mock-key',
                'secret' => 'secret',
                'uri' => $uri,
            ],
        ])->makePartial();
        $translation->allows()->getGuzzleClient()->andReturn($client);
        $this->assertSame(json_decode($body, true), $translation->translate('中国'));
    }

    public function testTranslateWithGuzzleRuntimeException(): void
    {
        $client = \Mockery::mock(Client::class);
        $client->allows()
            ->get(new AnyArgs())
            ->andThrow(new \Exception('Request http exception'));

        $translation = \Mockery::mock(Translation::class, ['key' => 'key'])->makePartial();
        $translation->allows()->getGuzzleClient()->andReturn($client);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Request http exception');
        $translation->translate('中国');
    }

    public function testGuzzleClient(): void
    {
        $translation = new Translation();

        self::assertInstanceOf(ClientInterface::class, $translation->getGuzzleClient());
    }

    public function testSetGuzzleClient(): void
    {
        $translate = new Translation(['key' => '', 'secret' => '']);
        self::assertNull($translate->getGuzzleClient()->getConfig('timeout'));

        $translate->setGuzzleOptions(['timeout' => 3000]);
        self::assertSame(3000, $translate->getGuzzleClient()->getConfig('timeout'));
    }
}
