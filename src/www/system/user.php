<?php

namespace ZippyERP\System;

use ZCL\DB\Entity;

/**
 *  Класс  инкапсулирующий   сущность  User
 * @table=system_users
 * @keyfield=user_id
 
 */
class User extends Entity
{

     

    /**
     * @see Entity
     *
     */
    protected function init()
    {
        $this->userlogin = "Гость";
        $this->user_id = 0;
        $this->createdon =  time();
        $this->erprole =  0;
        $this->shoporders =  0;
        $this->shopcontent =  0;
    }

    /**
     * Проверка  залогинивания
     *
     */
    public function isLogined()
    {
        return $this->user_id > 0;
    }

    /**
     * Выход из  системмы
     *
     */
    public function logout()
    {
        $this->init();
    }
  

    /**
     * @see Entity
     *
     */
    protected function afterLoad()
    {
       $this->createdon = strtotime($this->createdon);
 
   //распаковываем  данные из detail
        $xml = simplexml_load_string($this->acl);
        $this->erpacl = (int) ($xml->erpacl[0]);
        $this->shopcontent = (int) ($xml->shopcontent[0]);
        $this->shoporders = (int) ($xml->shoporders[0]);

        parent::afterLoad();              
    }

    /**
     * @see Entity
     *
     */
    protected function beforeSave()
    {
          parent::beforeSave();
        //упаковываем  данные в detail
        $this->acl = "<detail><erpacl>{$this->erpacl}</erpacl>";
        $this->acl .= "<shopcontent>{$this->shopcontent}</shopcontent>";
        $this->acl .= "<shoporders>{$this->shoporders}</shoporders>";
        $this->acl .= "</detail>";
            
        return true;       
    }  

    /**
     * @see Entity
     *
     */
    protected function beforeDelete()
    {
      $conn = \ZDB\DB::getConnect();
      $conn->Execute("delete from erp_metadata_access where user_id=" . $this->user_id);
    }

    /**
     * Возвращает  пользователя   по  логину
     *
     * @param mixed $login
     */
    public static function getByLogin($login)
    {
        $conn = \ZDB\DB::getConnect();
        return User::getFirst('userlogin = ' . $conn->qstr($login));
    }
    
    public static function getByEmail($email)
    {
        $conn = \ZDB\DB::getConnect();
        return User::getFirst('email = ' . $conn->qstr($email));
    }

 

    /**
     * Возвращает  пользователя   по  хешу
     *
     * @param mixed $md5hash
     */
    public static function getByHash($md5hash)
    {
        //$conn = \ZDB\DB::getConnect();
        $arr = User::find('md5hash=' . Entity::qstr($md5hash));
        if (count($arr) == 0) {
            return null;
        }
        $arr = array_values($arr);
        return $arr[0];
    }

    /**
     * Возвращает ID  пользователя
     *
     */
    public function getUserID()
    {
        return $this->user_id;
    }

     
}
