<?php
/**
 * @link https://github.com/Locustv2/yii2-encode-url-rule
 * @copyright Copyright (c) 2017 locustv2
 * @license https://github.com/Locustv2/yii2-encode-url-rule/blob/master/LICENSE.md
 */

namespace locustv2\components;

use yii;
use yii\web\UrlRule;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * EncodeUrlRule enables the creation of urls with parameters that can contain array of data.
 * This is achieved by flatenning the array into a json string and then encoded and added as query string.
 * For example:
 * ```php
 * 'UrlManager' => [
 *     'ruleConfig' => [
 *         'class' => 'weblement\components\EncodeUrlRule,
 *         'paramName' => 'enc',
 *         'autoEncodeParams' => [
 *             'page',
 *             'userId'
 *         ],
 *     ],
 * ],
 * ```
 * Then test the followind scenarios:
 * ```php
 * // /site/url-test/?id=123&key1=value1&enc=a2V5Mj0lMjJ2YWx1ZTIlMjImdXNlcklkPTQ1NiZwYWdlPTI%253D
 * echo Url::to([
 *        '/site/url-test',
 *        'id' => 123,
 *        'key1' => 'value1',
 *        'userId' => 456,
 *        'page' => 2,
 *        'enc' => [
 *            'key2' => 'value2'
 *        ],
 *    ]);
 * ```
 * In your controller action you can get the query parameters as follows
 * ```php
 * public function actionUrlTest($id, $userId, $key2)
 * {
 *     var_dump($id); // 123
 *     var_dump($userId); // 456
 *     var_dump($key2); // value2
 *     var_dump(Yii::$app->request->get('key1')); // value1
 *     var_dump(Yii::$app->request->get('page')); // 2
 *     var_dump(Yii::$app->request->get()); // contains all get query parameters including `enc`
 * }
 * ```
 *
 * @author Yuv Joodhisty <locustv2@gmail.com>
 * @since 1.0
 */
class EncodeUrlRule extends UrlRule
{
    /**
     * @var string the parameter key to use in urls
     */
    public $paramName = '_pi';

    /**
     * @var array the parameters to be encoded automatically when creating urls
     */
    public $autoEncodeParams = [];

    /**
     * @inheritdoc
     */
    public function parseRequest($manager, $request)
    {
        if($parsedRequest = parent::parseRequest($manager, $request)) {
            list ($route, $params) = $parsedRequest;

            if($pi = $request->get($this->paramName)) {
                $pi = $this->urlDecode($pi);

                foreach ($pi as $key => &$value) {
                    $value = Json::decode($value);
                }

                $params = ArrayHelper::merge($params, $pi);
            }

            return [$route, $params];
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        if (isset($params[$this->paramName]) && !is_array($params[$this->paramName])){
            $params[$this->paramName] = $this->urlDecode($params[$this->paramName]);

            foreach ($params[$this->paramName] as $key => &$value) {
                $value = Json::decode($value);
            }
        }

        foreach ($params as $key => $param) {
            if(in_array($key, $this->autoEncodeParams) && !is_null($param)) {
                $params[$this->paramName][$key] = $param;
                ArrayHelper::remove($params, $key);
            }
        }

        if (isset($params[$this->paramName]) && is_array($params[$this->paramName])) {
            foreach ($params[$this->paramName] as $key => &$value) {
                $value = Json::encode($value);
            }

            $params[$this->paramName] = $this->urlEncode($params[$this->paramName]);
        }

        return parent::createUrl($manager, $route, $params);
    }

    /**
     * Encode an array of data to be used in urls
     * @param array $data the data to be encoded
     * @return string the encoded data
     */
    private function urlEncode(array $data)
    {
        $query = http_build_query($data);
        $query = base64_encode($query);
        $query = rawurlencode($query);

        return $query;
    }

    /**
     * Decode a string of data from url query
     * @param string $query the query string to be decoded
     * @return array the decoded query
     */
    private function urlDecode($query)
    {
        $query = rawurldecode($query);
        $query = base64_decode($query);

        $data = [];
        parse_str($query, $data);

        return $data;
    }
}
