<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace GatewayWorker\Lib;


/**
 *
 */
class DataManager 
{
       static $db;
       
       public function __construct()
       {
           
          self::$db=new DbConnection('127.0.0.1', 3306, 'root', 'root', 'test');
       }
       
       public function Db()
       {
           return self::$db;
       }
       public static function insert($table,$data)
       {
             self::$db->insert($table)->cols($data)->query();
       }
       
       public static function saveExecute($table,$fileds,$where,$data)
       {
           self::$db->update($table)->bindValues($data)->cols($fileds)->where($where)->query();
       }
       
       public static function save($table,$data,$where=array())
       {
           if($table=="")return;
           if(empty($data))return;
           $fileds=array();
           foreach ($data as $key=>$val)
           {
               $fileds[]=$key;
           }
           if(empty($where))return;
           self::saveExecute($table, $fileds, $where, $data);
       }
       public static function select($table,$where)
       {
           
       }
       
       /**
        * 判斷用戶是否記錄過
        * @param unknown $user
        */
       public static function isExists($user)
       {
           return self::$db->query('SELECT * FROM `chat_user` where `user`=\''.$user.'\'');
       }
       
       /**
        * 獲取對接在綫客服
        */
       public static function getKefuOne()
       {
           $sql="select * from chat_kefu where isonline=1 order by usernum asc limit 1";//获取到对接用户最少当中的一个在线客服
//            $sql="SELECT * FROM `chat_kefu` where isonline=1  ORDER BY RAND() LIMIT 1";//随机获取一个在线客服
           $result=self::$db->query($sql);
           return !empty($result)?$result[0]:array();
       }
       /**
        * 獲取用戶
        * @param unknown $user
        */
       public static function getUserByUser($user)
       {
           $sql="select * from `chat_user` where `user`='{$user}'";
           $result=self::$db->query($sql);
           return !empty($result)?$result[0]:array();
       }
       
       /**
        * 獲取客服
        * @param unknown $kefu
        * @return mixed|NULL|string|number
        */
       public static function getKefuByKefu($kefu)
       {
           $sql="select * from `chat_kefu` where `kefu`='{$kefu}'";
           $result=self::$db->query($sql);
           return !empty($result)?$result[0]:array();
       }
       /**
        * 判斷在綫用戶的對接客服是否在綫
        * @param unknown $user
        */
       public function isHaveKefu($user)
       {
           $userinfo=self::getUserByUser($user);
           $flag=0;
           $kefuinfo=array();
           if(!empty($userinfo['kefu']))
           {
               $kefuinfo=self::getKefuByKefu($userinfo['kefu']);
               !empty($kefuinfo) && $kefuinfo['isonline'] && $flag=1;
           }
           !$flag && ($kefuinfo=self::getKefuOne());
           return empty($kefuinfo['kefu'])?"":$kefuinfo['kefu'];
       }
       /**
        * 設置客服對接用戶數
        * @param unknown $kefu
        */
       public static function setKefuUserNum($kefu)
       {
            $sql="select count(*) as usernum from `chat_user` where `kefu`='{$kefu}'";
            $info=self::$db->query($sql);
            self::save('chat_kefu', !empty($info)?$info[0]:array(),"kefu='{$kefu}'");
       }
       /**
        * 獲取客服所接待的用戶
        * @param unknown $kefu
        */
       public static function getUserByKefu($kefu)
       {
           $sql="select * from `chat_user` where `kefu`='{$kefu}' and isonline=1";
           return self::$db->query($sql);
       }
       
       /**
        * 獲取為接待用戶
        * @return mixed|NULL|string|number
        */
       public static function getOnlineUserByNoKefu()
       {
           $sql="select * from `chat_user` where `isonline`=1 and kefu='' order by lastlogin asc limit 5";
           return self::$db->query($sql);
       }
       
       /**
        * 給客服接待用戶
        * @param unknown $kefu
        */
       public function setOnlineUserKefu($kefu)
       {
           $res=self::getOnlineUserByNoKefu();
           if(!empty(self::getKefuByKefu($kefu)))
           {
               $data['kefu']=$kefu;
               foreach ($res as $key=>$val)
               {
                   self::save('chat_user', $data,"`kefu`='".$val['kefu']."'");
               }
           }
           self::setKefuUserNum($kefu);
           return $res;
       }
       
       /**
        * 設置客服的client_id,且设置在线
        * @param unknown $clients_list
        * @param unknown $kefu
        */
       public function saveKefuClientId($clients_list,$kefu)
       {
           $client_ids="";
           foreach ($clients_list as $key=>$val)
           {
               if($client_ids=="")
               {
                    $client_ids=$key;
               }else
               {
                   $client_ids.=",".$key;
               }
           }
           $sql="update chat_kefu set clients_id='{$client_ids}',isonline=1 where kefu='{$kefu}'";
           self::$db->query($sql);
       }
       //记录发送的消息
       public function recordMsg($user,$kefu,$msg,$to){
           $data['user']=$user;
           $data['kefu']=$kefu;
           $data['msg']=$msg;
           $data['to']=$to;
           $data['recordtime']=time();
           self::insert('chat_message', $data);
       }
       
       //离线client_id后的处理，判断是否离线
       public function setClientsIdAndIsOnline($client_id,$client_name,$iskefu){
           if($iskefu){
               $kefuinfo=self::getKefuByKefu($client_name);
               if(empty($kefuinfo))return ;
               $clients_id=explode(",", $kefuinfo['clients_id']);
               if(in_array($client_id, $clients_id)){
                   $clients_id_=array_flip($clients_id);
                   unset($clients_id[$clients_id_[$client_id]]);
                   $kefuinfo['clients_id']=(count($clients_id)>0)?implode(",", $clients_id):"";
                   empty($clients_id) && $kefuinfo['isonline']=0;
                   self::save('chat_kefu', $kefuinfo,"kefu='{$client_name}'");
               }
           }else{
               $useruinfo=self::getUserByUser($client_name);
               $clients_id=explode(",", $useruinfo['clients_id']);
               if(in_array($client_id, $clients_id)){
                   $clients_id_=array_flip($clients_id);
                   unset($clients_id[$clients_id_[$client_id]]);
                   $useruinfo['clients_id']=(count($clients_id)>0)?implode(",", $clients_id):"";
                   empty($clients_id) && $useruinfo['isonline']=0;
                   self::save('chat_user', $useruinfo,"`user`='{$client_name}'");
                }
            }
       }
       
       /**
        *   判断用户在7个小时内是否有操作
        */
       public function updateUserIsOline(){
           $time=time()-25200;
           $sql="update chat_user set clients_id='' ,isonline=0 where lastlogin<{$time}";
            self::$db->query($sql);
       }
       
}
