<?php

/**
 * Created by PhpStorm.
 * User: zhoujinda
 * Date: 2015/12/19
 * Time: 14:27
 */
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidBroadcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidFilecast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidGroupcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidUnicast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidCustomizedcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSBroadcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSFilecast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSGroupcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSUnicast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSCustomizedcast.php');

class UmengPushApi
{

    protected $appkey = NULL;
    protected $appMasterSecret = NULL;
    protected $timestamp = NULL;
    protected $validation_token = NULL;

    function __construct($key, $secret)
    {
        $this->appkey = $key;
        $this->appMasterSecret = $secret;
        $this->timestamp = strval(time());
    }

    /**
     * @param $content
     * @return null
     * @throws Exception
     */
    function getFileId($content)
    {
        $client = new AndroidFilecast();
        $client->setAppMasterSecret($this->appMasterSecret);
        $client->setPredefinedKeyValue("appkey", $this->appkey);
        $client->setPredefinedKeyValue("timestamp", $this->timestamp);
        $client->uploadContents($content);
        return $client->getFileId();
    }

    /**
     * Android-广播
     * @param $entity
     * @return array
     */

    /*$entity = array(
                'ticker'=>'测试广播提示文字',
                'title'=>'测试广播标题',
                'text'=>'测试广播文字描述',
                'after_open'=>'go_url',
                'url'=>'https://www.baidu.com',
                'production_mode'=>'false'
    );*/
    function sendAndroidBroadcast($entity)
    {
        try {

            if (empty($entity)) {
                return $this->apiReturnErr('entity参数缺失');
            }

            $brocast = new AndroidBroadcast();
            $brocast->setAppMasterSecret($this->appMasterSecret);
            $brocast->setPredefinedKeyValue("appkey", $this->appkey);
            $brocast->setPredefinedKeyValue("timestamp", $this->timestamp);

            foreach ($entity as $key => $val) {
                if ($key == 'payload') continue;
                $brocast->setPredefinedKeyValue($key, $val);
            }

            //设置额外信息
            if (isset($entity['payload']['extra'])) {
                foreach ($entity['payload']['extra'] as $key => $val) {
                    $brocast->setExtraField($key, $val);
                }
            }

            //print("Sending broadcast notification, please wait...\r\n");
            $result = $brocast->send();
            return $this->apiReturnSuc($result);
        } catch (Exception $e) {
            return $this->apiReturnErr($e->getMessage());
        }
    }



    /**
     * Android-单播
     * @param $entity
     */
    /*$entity = array(
                'device_tokens'=>'AjJUusw_AU3m1gNnqzWoQq-fAckB8yJjw-1zdCA0sJDL',
                'ticker'=>'测试单播提示文字',
                'title'=>'测试单播标题',
                'text'=>'测试单播文字描述',
                'after_open'=>'go_url',
                'url'=>'https://www.baidu.com',
                'production_mode'=>'false'
            );*/
    /**
     * 返回错误结构
     * @return array('status'=>boolean,'info'=>Object)
     */
    function apiReturnErr($info)
    {
        return array('status' => false, 'info' => $info);
    }

    /**
     * 返回成功结构
     * @return array('status'=>boolean,'info'=>Object)
     */
    function apiReturnSuc($info)
    {
        return array('status' => true, 'info' => $info);
    }


    public function sendAndroidUnicast($entity)
    {
        try {

            if (empty($entity)) {
                return $this->apiReturnErr('entity参数缺失');
            }

            $unicast = new \AndroidUnicast();
            $unicast->setAppMasterSecret($this->appMasterSecret);
            $unicast->setPredefinedKeyValue("appkey", $this->appkey);
            $unicast->setPredefinedKeyValue("timestamp", $this->timestamp);

            foreach ($entity as $key => $val) {
                $unicast->setPredefinedKeyValue($key, $val);
            }

            //print("Sending unicast notification, please wait...\r\n");
            $result = $unicast->send();
            return $this->apiReturnSuc($result);
        } catch (Exception $e) {
            return $this->apiReturnErr($e->getMessage());
        }


    }

    /**
     * Android-文件播
     * @param $entity
     */
    function sendAndroidFilecast($entity, $uploadContents)
    {
        try {

            if (empty($entity)) {
                return $this->apiReturnErr('entity参数缺失');
            }

            $filecast = new AndroidFilecast();
            $filecast->setAppMasterSecret($this->appMasterSecret);
            $filecast->setPredefinedKeyValue("appkey", $this->appkey);
            $filecast->setPredefinedKeyValue("timestamp", $this->timestamp);

            foreach ($entity as $key => $val) {
                $filecast->setPredefinedKeyValue($key, $val);
            }

            //print("Uploading file contents, please wait...\r\n");


            if (!empty($uploadContents)) {
                // Upload your device tokens, and use '\n' to split them if there are multiple tokens
                $filecast->uploadContents($uploadContents);
            }

            print("Sending filecast notification, please wait...\r\n");
            $result = $filecast->send();
            return $this->apiReturnSuc($result);
        } catch (Exception $e) {
            return $this->apiReturnErr($e->getMessage());
        }
    }

    /**
     * IOS-单播
     * @param $entity
     */

    /*$entity = array(
                'device_tokens'=>'996974cc8f24bd1b239c8369347085d3be30df452d91819420ec57e685dbe21c',
                'alert'=>'IOS 单播测试',
                'badge'=>0,
                'sound'=>'chime',
                'production_mode'=>'false'

    );*/
    /**
     * Android-组播
     * @param $entity
     */
    function sendAndroidGroupcast($entity)
    {
        try {
            /*
              *  Construct the filter condition:
              *  "where":
              *	{
              *		"and":
              *		[
                *			{"tag":"test"},
                *			{"tag":"Test"}
              *		]
              *	}
              */

            if (empty($entity)) {
                return $this->apiReturnErr('entity参数缺失');
            }

            $groupcast = new AndroidGroupcast();
            $groupcast->setAppMasterSecret($this->appMasterSecret);
            $groupcast->setPredefinedKeyValue("appkey", $this->appkey);
            $groupcast->setPredefinedKeyValue("timestamp", $this->timestamp);

            foreach ($entity as $key => $val) {
                $groupcast->setPredefinedKeyValue($key, $val);
            }

            //print("Sending groupcast notification, please wait...\r\n");
            $result = $groupcast->send();
            return $this->apiReturnSuc($result);
        } catch (Exception $e) {
            return $this->apiReturnErr($e->getMessage());
        }
    }

    /**
     * IOS-广播
     * @param $entity
     */
    /*$entity = array(
                'alert'=>'IOS 广播测试',
                'badge'=>0,
                'sound'=>'chime',
                'production_mode'=>'false'
    );*/
    /**
     * Android-通过开发者自有的alias进行推送
     * @param $entity
     * @return array
     */
    function sendAndroidCustomizedcast($entity)
    {
        try {

            if (empty($entity)) {
                return $this->apiReturnErr('entity参数缺失');
            }

            $customizedcast = new AndroidCustomizedcast();
            $customizedcast->setAppMasterSecret($this->appMasterSecret);
            $customizedcast->setPredefinedKeyValue("appkey", $this->appkey);
            $customizedcast->setPredefinedKeyValue("timestamp", $this->timestamp);
            foreach ($entity as $key => $val) {
                if ($key == 'payload') continue;
                $customizedcast->setPredefinedKeyValue($key, $val);
            }
            //设置额外信息
            if (isset($entity['payload']['extra'])) {
                foreach ($entity['payload']['extra'] as $key => $val) {
                    $customizedcast->setExtraField($key, $val);
                }
            }

            //print("Sending customizedcast notification, please wait...\r\n");
            $result = $customizedcast->send();
            return $this->apiReturnSuc($result);
        } catch (Exception $e) {
            return $this->apiReturnErr($e->getMessage());
        }
    }


    function sendIOSUnicast($entity)
    {
        try {

            if (empty($entity)) {
                return $this->apiReturnErr('entity参数缺失');
            }

            $unicast = new IOSUnicast();
            $unicast->setAppMasterSecret($this->appMasterSecret);
            $unicast->setPredefinedKeyValue("appkey", $this->appkey);
            $unicast->setPredefinedKeyValue("timestamp", $this->timestamp);

            foreach ($entity as $key => $val) {
                if ($key == 'payload') continue;
                $unicast->setPredefinedKeyValue($key, $val);
            }
            //添加payload 自定义字段
            if (isset($entity['payload'])) {
                foreach ($entity['payload'] as $key => $val) {
                    if (in_array($key, array('aps', 'd', 'p'))) continue;
                    $unicast->setCustomizedField($key, $val);
                }
            }

            //print("Sending unicast notification, please wait...\r\n");
            $result = $unicast->send();
            return $this->apiReturnSuc($result);
        } catch (Exception $e) {
            return $this->apiReturnErr($e->getMessage());
        }
    }


    function sendIOSBroadcast($entity)
    {
        try {

            if (empty($entity)) {
                return $this->apiReturnErr('entity参数缺失');
            }

            $brocast = new IOSBroadcast();
            $brocast->setAppMasterSecret($this->appMasterSecret);
            $brocast->setPredefinedKeyValue("appkey", $this->appkey);
            $brocast->setPredefinedKeyValue("timestamp", $this->timestamp);

            foreach ($entity as $key => $val) {
                if ($key == 'payload') continue;
                $brocast->setPredefinedKeyValue($key, $val);
            }
            //添加payload 自定义字段
            if (isset($entity['payload'])) {
                foreach ($entity['payload'] as $key => $val) {
                    if (in_array($key, array('aps', 'd', 'p'))) continue;
                    $brocast->setCustomizedField($key, $val);
                }
            }

            //print("Sending broadcast notification, please wait...\r\n");
            $result = $brocast->send();
            return $this->apiReturnSuc($result);
        } catch (Exception $e) {
            return $this->apiReturnErr($e->getMessage());
        }
    }

    /**
     * IOS-文件播
     * @param $entity
     * @return array
     */
    function sendIOSFilecast($entity, $uploadContents)
    {
        try {

            if (empty($entity)) {
                return $this->apiReturnErr('entity参数缺失');
            }

            $filecast = new IOSFilecast();
            $filecast->setAppMasterSecret($this->appMasterSecret);
            $filecast->setPredefinedKeyValue("appkey", $this->appkey);
            $filecast->setPredefinedKeyValue("timestamp", $this->timestamp);

            foreach ($entity as $key => $val) {
                if ($key == 'payload') continue;
                $filecast->setPredefinedKeyValue($key, $val);
            }
            //添加payload 自定义字段
            if (isset($entity['payload'])) {
                foreach ($entity['payload'] as $key => $val) {
                    if (in_array($key, array('aps', 'd', 'p'))) continue;
                    $filecast->setCustomizedField($key, $val);
                }
            }

            if (!empty($uploadContents)) {
                // Upload your device tokens, and use '\n' to split them if there are multiple tokens
                $filecast->uploadContents($uploadContents);
            }

            //print("Sending filecast notification, please wait...\r\n");
            $result = $filecast->send();
            return $this->apiReturnSuc($result);
        } catch (Exception $e) {
            return $this->apiReturnErr($e->getMessage());
        }
    }

    /**
     * IOS-组播
     * @param $entity
     * @return array
     */
    function sendIOSGroupcast($entity)
    {
        try {
            /*
              *  Construct the filter condition:
              *  "where":
              *	{
              *		"and":
              *		[
                *			{"tag":"iostest"}
              *		]
              *	}
              */
            if (empty($entity)) {
                return $this->apiReturnErr('entity参数缺失');
            }

            $groupcast = new IOSGroupcast();
            $groupcast->setAppMasterSecret($this->appMasterSecret);
            $groupcast->setPredefinedKeyValue("appkey", $this->appkey);
            $groupcast->setPredefinedKeyValue("timestamp", $this->timestamp);
            // Set the filter condition

            foreach ($entity as $key => $val) {
                if ($key == 'payload') continue;
                $groupcast->setPredefinedKeyValue($key, $val);
            }
            //添加payload 自定义字段
            if (isset($entity['payload'])) {
                foreach ($entity['payload'] as $key => $val) {
                    if (in_array($key, array('aps', 'd', 'p'))) continue;
                    $groupcast->setCustomizedField($key, $val);
                }
            }

            //print("Sending groupcast notification, please wait...\r\n");
            $result = $groupcast->send();
            return $this->apiReturnSuc($result);
        } catch (Exception $e) {
            return $this->apiReturnErr($e->getMessage());
        }
    }

    /**
     * IOS-通过开发者自有的alias进行推送
     * @param $entity
     * @return array
     */
    function sendIOSCustomizedcast($entity)
    {
        try {

            if (empty($entity)) {
                return $this->apiReturnErr('entity参数缺失');
            }

            $customizedcast = new IOSCustomizedcast();
            $customizedcast->setAppMasterSecret($this->appMasterSecret);
            $customizedcast->setPredefinedKeyValue("appkey", $this->appkey);
            $customizedcast->setPredefinedKeyValue("timestamp", $this->timestamp);

            foreach ($entity as $key => $val) {
                if ($key == 'payload') continue;
                $customizedcast->setPredefinedKeyValue($key, $val);
            }

            //添加payload 自定义字段
            if (isset($entity['payload'])) {
                foreach ($entity['payload'] as $key => $val) {
                    if (in_array($key, array('aps', 'd', 'p'))) continue;
                    $customizedcast->setCustomizedField($key, $val);
                }
            }

            //print("Sending customizedcast notification, please wait...\r\n");
            $result = $customizedcast->send();
            return $this->apiReturnSuc($result);
        } catch (Exception $e) {
            return $this->apiReturnErr($e->getMessage());
        }
    }
}
