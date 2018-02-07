<?php
/**
 * 注意：本内容仅限于博也公司内部传阅,禁止外泄以及用于其他的商业目的
 * @author    hebidu<346551990@qq.com>
 * @copyright 2017 www.itboye.com Boye Inc. All rights reserved.
 * @link      http://www.itboye.com/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * Revision History Version
 ********1.0.0********************
 * file created @ 2017-12-27 17:01
 *********************************
 ********1.0.1********************
 *
 *********************************
 */

namespace by\component\umeng;

use by\component\umeng\entity\UMengPushDataEntity;
use by\infrastructure\helper\CallResultHelper;

/**
 * Class ByPushHelper
 *
 *
 * @package by\component\umeng
 */
class ByPushHelper
{

    /**
     * 单推 - 需要配置支持，该友盟推送从配置表获取配置信息
     * @param UMengPushDataEntity $mengPushDataEntity
     * @param array $cfgList 配置
     * @param string $msg_type
     * @return \by\infrastructure\base\CallResult
     */
    public static function push(UMengPushDataEntity $mengPushDataEntity, $cfgList, $msg_type = '001')
    {
        if (!empty($cfgList)) {
            return CallResultHelper::fail('友盟配置信息缺失');
        }
        if (empty($mengPushDataEntity->getProjectId())) {
            return CallResultHelper::fail('projectId is need');
        }

        $pushApi = new BoyePushApi();
        $after_open = [
            'type' => 'go_activity',
            'activity' => $msg_type,
            'param' => $msg_type, // 跳转的页面
            'extra' => [
                'uid'=>$mengPushDataEntity->getToUid(),
                'expire_time' => time() + 20*60
            ],
        ];
        $body = [
            'alert' => $mengPushDataEntity->getContent(),
            'ticker' => $mengPushDataEntity->getTitle(),
            'title' => $mengPushDataEntity->getTitle(),
            'text' => $mengPushDataEntity->getContent()
        ];

        $error = [];

        foreach ($cfgList as $vo) {
            $value = $vo['value'];
            $value = self::_parse($vo['type'], $value);
            if (is_array($value)) {
                $result = $pushApi->setConfig($value);
                if (!$result->isSuccess()) continue;
            }
            if ($mengPushDataEntity->getIsBroadcast()) {
                $result = $pushApi->sendAll($body, $after_open);
            } else {
                $result = $pushApi->send($mengPushDataEntity->getToUid(), $body, $after_open);
            }
            if (!$result->isSuccess()) {
                array_push($error, $result->getMsg());
            }
        }


        return CallResultHelper::success($error);
    }

    private static function _parse($type, $value)
    {
        switch ($type) {
            case 3 :
                $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
                if (strpos($value, ':')) {
                    $value = array();
                    foreach ($array as $val) {
                        list($k, $v) = explode(':', $val, 2);
                        $value[$k] = $v;
                    }
                } else {
                    $value = $array;
                }
                break;
        }
        return $value;
    }

}