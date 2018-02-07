<?php
/**
 * 虎头奔 友盟推送 广播&别名单播
 * User  : hebidu
 * Editor: rainbow
 * Date  : 2016-12-30 14:31:21
 */

namespace by\component\umeng;

use by\infrastructure\helper\CallResultHelper;

require_once('UmengPushApi.php');

class BoyePushApi
{

    protected $config = [
        'alias_type' => 'sunsun_xiaoli',
        'device_type' => 'ios',
        'appkey' => '',
        'secret' => '',
        'production_mode' => true
    ];

    public function setConfig($config)
    {
        if (!array_key_exists("alias_type", $config)
            || (!array_key_exists("device_type", $config))
            || (!array_key_exists("appkey", $config))
            || (!array_key_exists("secret", $config))
            || !array_key_exists("production_mode", $config)) {
            return CallResultHelper::fail();
        }
        $this->config['production_mode'] = $config['production_mode'];
        $this->config['secret'] = $config['secret'];
        $this->config['appkey'] = $config['appkey'];
        $this->config['alias_type'] = $config['alias_type'];
        $this->config['device_type'] = $config['device_type'];
        return CallResultHelper::success();
    }

    /**
     * 推送给全部用户
     * @param $param
     * @param array $after_open
     * @return \by\infrastructure\base\CallResult
     */
    public function sendAll($param, $after_open = ['type' => 'go_app', 'param' => '', 'extra' => ''])
    {
        $r = new \UmengPushApi($this->getAppkey(), $this->getSecret());
        if (strtolower($this->getCfgDeviceType()) == 'android') {
            $entity = [
                'alias_type' => $this->getAliasType(),
                'ticker' => $param['ticker'],
                'title' => $param['title'],
                'text' => $param['text'],
                'after_open' => $after_open['type'],
                'production_mode' => $this->getProductionMode(),
            ];

            if ($after_open['type'] == 'go_url') {
                $entity['url'] = $after_open['url'];
            } elseif ($after_open['type'] == 'go_activity') {
                $entity['activity'] = $after_open['activity'];
            }

            $result = $r->sendAndroidBroadcast($entity);
        } else {
            $entity = [
                'alias_type' => $this->getAliasType(),
                'alert' => $param['text'],
                'badge' => 1, // 角标
                'sound' => 'default',
                'production_mode' => $this->getProductionMode(),
            ];
            $result = $r->sendIOSBroadcast($entity);
        }
        if (array_key_exists('status', $result) && $result['status']) {
            return CallResultHelper::success($result['info']);
        }
        return CallResultHelper::fail($result['info']);
    }

    public function getAppkey()
    {
        return $this->config['appkey'];
    }

    public function getSecret()
    {
        return $this->config['secret'];
    }

    public function getCfgDeviceType()
    {
        return $this->config['device_type'];
    }

    public function getAliasType()
    {
        return $this->config['alias_type'];
    }

    public function getProductionMode()
    {
        return $this->config['production_mode'];
    }

    /**
     * 别名 - 单播(1)/多播(50-)/文件博(50+)
     * @param $uid   string/int uids(逗号分隔<=50)
     * @param array $param client     客户端[worker,driver,其他任意字符]
     * @param array $after_open
     * @internal param string $client
     * @return \by\infrastructure\base\CallResult
     */
    public function send($uid = '', $param, $after_open = ['type' => 'go_app', 'param' => '', 'extra' => ''])
    {

        //检查 uid
        $file = false;

        $uid_arr = array_unique(explode(',', $uid));
        $size = count($uid_arr);
        if ($size > 50) $file = true;
        $uid_arr = implode("\n", $uid_arr);//一定要 "\n"
        $file_id = '';
        if ($file) {
            //获取file_id
            $r = new \UmengPushApi($this->getAppkey(), $this->getSecret());
            $file_id = $r->getFileId($uid_arr);
        }
        //检查消息主题
        $r = $this->checkMsgBody($param);
        if (!$r->isSuccess()) return $r;
        if (strtolower($this->getCfgDeviceType()) == 'android') {
            $entity = [
                'alias_type' => $this->getAliasType(),
                'ticker' => $param['ticker'],
                'title' => $param['title'],
                'text' => $param['text'],
                'after_open' => $after_open['type'],
                'production_mode' => $this->getProductionMode(),
                'payload'=>[
                    'expire_time'=> date('Y-m-d H:i:s', time() + 8*3600)
                ]
            ];
        } else {
            $entity = [
                'alias_type' => $this->getAliasType(),
                'alert' => [
                    'title'=>$param['title'],
                    'body'=>$param['text']
                ],
                'badge' => 1, //角标
                'sound' => 'default',
                'production_mode' => $this->getProductionMode(),
                'payload'=>[
                    'expire_time'=> date('Y-m-d H:i:s', time() + 8*3600)
                ]
            ];
        }
        if ($file) $entity['file_id'] = $file_id;
        else $entity['alias'] = $uid;


        $pusher = new \UmengPushApi($this->getAppkey(), $this->getSecret());
        if ($this->getCfgDeviceType() == 'ios') {
            if (!empty($after_open['extra'])) {
                if (isset($after_open['extra']['sound'])) {
                    //自定义sound
                    $entity['sound'] = $after_open['extra']['sound'] . '.caf';
                }
            }

            $entity['payload']['aps'] = ['alert' => $param['text']];
            $entity['payload']['extra'] = $after_open['extra'];
            $result = $pusher->sendIOSCustomizedcast($entity);
        } else {
            if (!empty($after_open['extra'])) {
                if (isset($after_open['extra']['sound'])) {
                    //自定义sound
                    $entity['sound'] = $after_open['extra']['sound'];
                }
            }
            //自定义打开指定页面
            if ($after_open['type'] == 'go_url') {
                $entity['url'] = $after_open['param'];
            } elseif ($after_open['type'] == 'go_activity') {
                $entity['activity'] = $after_open['param'];
            }
            $result = $pusher->sendAndroidCustomizedcast($entity);
        }
        if ($result['status']) return CallResultHelper::success($result['info']);

        return CallResultHelper::fail($result['info']);
    }

    /**
     * 检查消息体
     * @Author
     * @DateTime 2016-12-30T14:08:36+0800
     * @param array $param
     * @return \by\infrastructure\base\CallResult
     */
    private function checkMsgBody(array $param)
    {
        if (empty($param)) {
            return CallResultHelper::fail('param');
        }
        if (empty($param['ticker'])) {
            return CallResultHelper::fail('ticker must set');
        }
        if (empty($param['title'])) {
            return CallResultHelper::fail('title must set');
        }
        if (empty($param['text'])) {
            return CallResultHelper::fail('text must set');
        }
        if (empty($param['alert'])) {
            return CallResultHelper::fail('alert must set');
        }
        return CallResultHelper::success();
    }
}