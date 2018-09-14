<?php

namespace Nilnice\Translate;

use Nilnice\Translate\Contracts\Translation as TranslationInterface;
use Nilnice\Translate\Exceptions\HttpException;
use Nilnice\Translate\Traits\ClientTrait;

class Translation implements TranslationInterface
{
    use ClientTrait;

    private const TRANSLATE_BAIDU = 'baidu';
    private const TRANSLATE_YOUDAO = 'youdao';

    /**
     * @var array
     */
    private $config;

    /**
     * Translation constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Chinese text translate to english.
     *
     * @param string $text
     * @param string $translator
     *
     * @return array
     *
     * @throws \Nilnice\Translate\Exceptions\HttpException
     */
    public function translate(string $text, string $translator = self::TRANSLATE_BAIDU): array
    {
        if (! \in_array($translator, self::getTranslators(), true)) {
            throw new \InvalidArgumentException('Invalid translator: ' . $translator);
        }

        $isBaiduTranslator = self::TRANSLATE_BAIDU === $translator;
        $salt = time();
        $sign = md5($this->config['key'] . $text . $salt . $this->config['secret']);
        $query = array_filter([
            'q' => $text,
            'from' => $isBaiduTranslator ? 'zh' : 'zh-CHS',
            'to' => $isBaiduTranslator ? 'en' : 'EN',
            $isBaiduTranslator ? 'appid' : 'appKey' => $this->config['key'],
            'salt' => $salt,
            'sign' => $sign,
        ]);

        try {
            $response = $this->getGuzzleClient()->get($this->config['uri'], [
                'query' => $query,
            ]);
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }

        $contents = json_decode($response->getBody(), true);
        $result = ['from' => 'zh', 'to' => 'en', 'src' => $text, 'dst' => '',];

        if ($isBaiduTranslator) {
            $result['dst'] = $contents['trans_result'][0]['dst'] ?? '';
        } else {
            $result['dst'] = $contents['translation'][0] ?? '';
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function getTranslators(): array
    {
        return [
            self::TRANSLATE_BAIDU,
            self::TRANSLATE_YOUDAO,
        ];
    }
}
