<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

/**
 * 聊天主逻辑
 * 主要是处理 onMessage onClose 
 */
use \GatewayWorker\Lib\Gateway;
use GatewayWorker\Lib\DataManager;
use Workerman\Lib\Timer;

class Events
{
   /**
    * 有消息时
    * @param int $client_id
    * @param mixed $message
    */
   public static function onMessage($client_id, $message)
   { 
        // debug
        echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id session:".json_encode($_SESSION)." onMessage:".$message."\n";
        
        // 客户端传递的是json数据
        $message_data = json_decode($message, true);
        if(!$message_data)
        {
            return ;
        }
        
        // 根据类型执行不同的业务
        switch($message_data['type'])
        {
            // 客户端回应服务端的心跳
            case 'pong':
                return;
            // 客户端登录 message格式: {type:login, name:xx, room_id:1} ，添加到客户端，广播给所有客户端xx进入聊天室
            case 'login':
                // 判断是否有房间号
                //************注意默認設置將用戶設置成房間號***************************//
                // 把房间号昵称放到session中
                $room_id = $message_data['room_id'];
                $iskefu = isset($message_data['iskefu'])?intval($message_data['iskefu']):0;//客服標識
                $client_name = htmlspecialchars($message_data['client_name']);//默認為房間
                $_SESSION['room_id'] = $room_id;
                $_SESSION['client_name'] = $client_name;
                
                $dm=new DataManager();
                $dm->Db()->beginTrans();
                try{
                    if(!$iskefu){//判斷是否是客服，以下為用戶的操作
                       $_SESSION['client_room'][$client_id]=$client_name;//将所有用户的$client_id和client_name放入房间好统一获取及分辨client_id是那个用户的
                        $data=array();
                        $data['ip']=$_SERVER['REMOTE_ADDR'];
                        $data['lastlogin']=time();
                        $data['isonline']=1;
                        if(empty($dm->isExists($client_name))){//判斷用戶是否記錄過
                            $data['user']=$client_name;
                            $data['recordtime']=$data['lastlogin'];
                            $kefuinfo=$dm->getKefuOne();
                            $data['kefu']=(!empty($kefuinfo))?$kefuinfo['kefu']:"";
                            $data['clients_id']=$client_id;//记录用户client_id
                            $dm->insert('chat_user', $data);//將用戶記錄到數據庫
                            $dm->setKefuUserNum($data['kefu']);//设置客服对接的用户数，为方便获取对接少的用户的客服
                        }else{
                            $userinfo=$dm->getUserByUser($client_name);
                            $data['kefu']=$dm->isHaveKefu($client_name);//判断是否有客服且是否在线有则返回无则重新获取一个在线客服并返回
                            $data['clients_id']=(($userinfo['clients_id']!=="")?$userinfo['clients_id'].',':'').$client_id;//记录用户client_id
                            $dm->save('chat_user', $data,"`user`='{$client_name}'");//修改用戶數據
                            $dm->setKefuUserNum($data['kefu']);//设置客服对接的用户数，为方便获取对接少的用户的客服
                        }
                        $dm->Db()->commitTrans();
                        $new_message = array('type'=>$message_data['type'], 'client_id'=>htmlspecialchars($client_name), 'client_name'=>htmlspecialchars($client_name), 'time'=>date('Y-m-d H:i:s'));
                        Gateway::joinGroup($client_id, $client_name);//这里以$client_name为房间
                        $kefuinfo=$dm->getKefuByKefu($data['kefu']);
                        if(!empty($kefuinfo)){
                            $clients_id=(strpos($kefuinfo['clients_id'], ","))?explode(",", $kefuinfo['clients_id']):array($kefuinfo['clients_id']);//获取客服client_id
                            foreach ($clients_id as $key=>$val){
                                if($val){
                                    Gateway::joinGroup($val, $client_name);//将客服的client_id加入用户 房间
                                }
                            }
                            $new_message['kefu_client_name']=$data['kefu'];
                        }
                        Gateway::sendToGroup($client_name, json_encode($new_message),$client_id);
                        Gateway::sendToCurrentClient(json_encode($new_message));
                        
                        
                    }else{//一下為客服的操作
                        $_SESSION['kefu_room'][$client_id]=$client_name;//将所有客服的$client_id和client_name放入Session好统一获取及分辨client_id是那个客服的
                        Gateway::joinGroup($client_id, $client_name);//将client_id加入进客服本身的房间，而不是用户的房间，用于后面获取客服所有的client_id
                        $res=$dm->getUserByKefu($client_name);
                        //一下為客服下的用戶列表
                        $user_list=array();
                        foreach ($res as $key=>$val){
                            $user_list[$val['user']]=$val['user'];//我這裏默認用戶為房間號
                            Gateway::joinGroup($client_id, $val['user']);//將客服客戶端加入房間//這裏需要重新加入到房間因爲client_id已經刷新了
                            $new_message = array('type'=>$message_data['type'], 'kefu_client_name'=>htmlspecialchars($client_name), 'client_name'=>htmlspecialchars($val['user']), 'time'=>date('Y-m-d H:i:s'));
                            Gateway::sendToGroup($val['user'], json_encode($new_message),$client_id);
                        }
                        
                        //獲取未接待用戶並設置
                        $res=$dm->setOnlineUserKefu($client_name);//获取了5条并设置
                        foreach ($res as $key=>$val){
                            if($val){
                                Gateway::joinGroup($client_id, $val['user']);//將客服客戶端加入房間
                            }
                        }
                        
                        // 获取房间内所有用户列表，這裏我將默認客服有一個房間，切所有客服的client_id房間該客服的房間
                        //為方便用戶進來時，將客服的client_id 加入到房間進去
                        $clients_list = Gateway::getClientSessionsByGroup($client_name);
                        $clients_id=array();
                        foreach($clients_list as $tmp_client_id=>$item)
                        {
                            if(isset($item['client_name'])){
                                $clients_id[$tmp_client_id] = $item['client_name'];
                            }
                        }
                        $clients_id[$client_id]=$client_name;
                        $dm->saveKefuClientId($clients_id,$client_name);
                        $dm->Db()->commitTrans();
                        //獲取客服客戶端用戶列表
                       
                        // 给当前用户发送用户列表
                        $new_message = array('type'=>$message_data['type'], 'client_id'=>htmlspecialchars($client_name), 'client_name'=>htmlspecialchars($client_name), 'time'=>date('Y-m-d H:i:s'));
                        $new_message['client_list'] = $user_list;
                        Gateway::sendToCurrentClient(json_encode($new_message));
                    }
                    
                }catch (Exception $e){
                    echo "錯誤異常".$e;
                    $dm->Db()->rollBackTrans();
                }
                // 转播给当前房间的所有客户端，xx进入聊天室 message {type:login, client_id:xx, name:xx} 
               
                return;
                
            // 客户端发言 message: {type:say, to_client_id:xx, content:xx}
            case 'say':
                // 非法请求
                $room_id = $_SESSION['room_id'];
                $client_name =htmlspecialchars($message_data['client_name']);
                $iskefu = isset($message_data['iskefu'])?intval($message_data['iskefu']):0;//客服標識                                   
                $new_message = array(
                    'type'=>'say',
                    'from_client_id'=>$client_name,//当前用户或者客服在发消息
                    'from_client_name' =>$client_name,
                    'to_client_id'=>'all',
                    'content'=>nl2br(htmlspecialchars($message_data['content'])),
                    'time'=>date('Y-m-d H:i:s'),
                );
                $dm=new DataManager();
                if($iskefu){//一下為客服操作
                    $to_client_name=htmlspecialchars($message_data['to_client_name']);//客服对用户说或者用户发消息也是要发给自己，在自己的浏览器上记录消息，
                                                                                      //所以这里是不管是谁在发，都是要发给用户的，所以这里写死
                    $new_message['to_client_name']=$to_client_name;
                    //記錄message
                    $dm->recordMsg($to_client_name, $client_name, nl2br(htmlspecialchars($message_data['content'])), 1);//1為給用戶，2為給客服 發送消息
                    return Gateway::sendToGroup($to_client_name ,json_encode($new_message));
                }else{

                    $to_client_name=$client_name;//这里是不管是谁在发，都是要发给用户的，所以这里写死
                    $new_message['to_client_name']=$to_client_name;
                    //記錄message
                    $userinfo=$dm->getUserByUser($client_name);
                    $dm->recordMsg($client_name, $userinfo['kefu'], nl2br(htmlspecialchars($message_data['content'])), 2);//1為給用戶，2為給客服 發送消息
                    return Gateway::sendToGroup($client_name ,json_encode($new_message));
                }
               
                
        }
   }
   
   /**
    * 当客户端断开连接时
    * @param integer $client_id 客户端id
    */
   public static function onClose($client_id)
   {
       // debug
       echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id onClose:''\n";
       $iskefu=0;
       $clients_list = isset($_SESSION['client_room'])?$_SESSION['client_room']:((isset($_SESSION['kefu_room'])&& $iskefu=1)?$_SESSION['kefu_room']:array());
       $dm=new DataManager();
       if(empty($clients_list[$client_id]))return;
       $dm->setClientsIdAndIsOnline($client_id, $clients_list[$client_id], $iskefu);
    }
    
    public static function onWorkerStart(){
        //设置一个定时器，3个小时执行一次
        $dm=new DataManager();
        //判断用户是否10800
        Timer::add(1800, array($dm,'updateUserIsOline'));
    }
  
}
