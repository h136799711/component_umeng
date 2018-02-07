<?php
/**
 * 注意：本内容仅限于博也公司内部传阅,禁止外泄以及用于其他的商业目的
 * @author    hebidu<346551990@qq.com>
 * @copyright 2017 www.itboye.com Boye Inc. All rights reserved.
 * @link      http://www.itboye.com/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * Revision History Version
 ********1.0.0********************
 * file created @ 2017-12-27 17:04
 *********************************
 ********1.0.1********************
 *
 *********************************
 */

namespace by\component\umeng\entity;


use by\infrastructure\base\BaseEntity;

class UMengPushDataEntity extends BaseEntity
{
    private $projectId;
    private $toUid;
    private $content;
    private $title;
    private $isBroadcast;

    public function __construct()
    {
        parent::__construct();
        $this->setIsBroadcast(false);
    }

    /**
     * @return mixed
     */
    public function getIsBroadcast()
    {
        return $this->isBroadcast;
    }

    /**
     * @param mixed $isBroadcast
     */
    public function setIsBroadcast($isBroadcast)
    {
        $this->isBroadcast = $isBroadcast;
    }

    /**
     * @return mixed
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * @param mixed $projectId
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * @return mixed
     */
    public function getToUid()
    {
        return $this->toUid;
    }

    /**
     * @param mixed $toUid
     */
    public function setToUid($toUid)
    {
        $this->toUid = $toUid;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
}