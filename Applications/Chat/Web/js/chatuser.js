if (typeof console == "undefined") {    this.console = { log: function (msg) {  } };}
//如果浏览器不支持websocket，会使用这个flash自动模拟websocket协议，此过程对开发者透明
WEB_SOCKET_SWF_LOCATION = "/swf/WebSocketMain.swf";
//开启flash的websocket debug
WEB_SOCKET_DEBUG = true;

var ws,  client_list={},client_name,isconnect=0;

//连接服务端
function connect() {
	//目前是刷新就要连接一次，有的是放在客服按钮上所以要用到isconnect，我这里用不上我注释了
//	console.log(" 是否连过一次  "+isconnect);
//	if(isconnect==1){
//		return ;
//	}
//	isconnect=1;
	
    // 创建websocket
    ws = new WebSocket("ws://"+document.domain+":7272");
    // 当socket连接打开时，输入用户名
    ws.onopen = onopen;
    // 当有消息时根据消息类型显示不同信息
    ws.onmessage = onmessage; 
    ws.onclose = function() {
    	console.log("连接关闭，定时重连");
    	isconnect=0;
    	connect();
    };
    ws.onerror = function() {
    	console.log("出现错误");
    };
   
    
}

function getRandName(num){
	var arr=['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','1','2','3','4','5','6','7','8','9','0'];
	var str="";
	for(var i=0;i<num;i++){
		 var id = Math.ceil(Math.random()*35);
		 str += arr[id];
	}
	return str;
}
//连接建立时发送登录信息
function onopen()
{
	var lsname=localStorage.getItem("client_name"+getDate());
	console.log(lsname);
    // 登录
    if(lsname!="" && lsname!=undefined && lsname!=null){//判斷是否記錄在客服端
    	client_name=lsname;
	}else{
		client_name=getRandName(6);
	}
//    client_name=getRandName(6);
    var login_data = '{"type":"login","client_name":"'+client_name+'","room_id":"'+client_name+'"}';
    console.log("websocket握手成功，发送登录数据:"+login_data);
    ws.send(login_data);
}


//服务端发来消息时
function onmessage(e)
{
    console.log(e.data);
    var data = eval("("+e.data+")");
    switch(data['type']){
        // 服务端ping客户端
        case 'ping':
            ws.send('{"type":"pong"}');
            break;;
        // 登录 更新用户列表
        case 'login':
        	var kefu_client_name=data['kefu_client_name'];
        	if(kefu_client_name!="" && kefu_client_name!=null && kefu_client_name!=undefined){
        		//$(".kefu_client_name").text("客服: "+kefu_client_name");
        	}
            client_name=data['client_name'];
            localStorage.setItem('client_name'+getDate(),client_name);
            var saycontent=localStorage.getItem("say"+getDate());
            if(saycontent!="" && saycontent!=null && saycontent!=undefined ){
            	$(".chat_content").html("");
                appendmsg(saycontent);
            }
            break;
        // 发言
        case 'say':
        	var str='';
        	if(client_name!=data['from_client_name']){//用户发送过来的
        		str+='<p class="chat_other">';
        		str+='<b>客服：'+data['from_client_name']+'</b> <span>'+data['time']+'</span><br> <a>'+data['content']+'</a>';
        		str+='</p>';
        	}else{
        		str+='<p class="chat_my">';
        		str+='<span>'+data['time']+'</span><b>用户：（'+data['from_client_name']+'）</b><br> <a>'+data['content']+'</a>';
        		str+='</p>';
        	}
        	var saycontent=localStorage.getItem("say"+getDate());
        	if(saycontent!="" && saycontent!=null &&  saycontent!=undefined ){
        		saycontent+=str;
        	}else{
        		saycontent=str;
        	}
        	localStorage.setItem("say"+getDate(),saycontent);
        	appendmsg(str);
            break;
        // 用户退出 更新用户列表
        case 'logout':
    }
}


$(function(){
$('.chat button').click(function(){
	var content=$('.chat textarea').val();
	$('.chat textarea').val("");
	if(content==""){
		return;
	}
	ws.send('{"type":"say","client_name":"'+client_name+'","content":"'+content+'"}');
})
})

function appendmsg(str){
	$(".chat_content").append(str);
	setTimeout(function(){
		$(".chat_content").scrollTop($(".chat_content")[0].scrollHeight);
	}, 100);
	
}
/**
 * 
 * 获取当前时间
 */
function p(s) {
    return s < 10 ? '0' + s: s;
}
function getNowTime(){
	var myDate = new Date();
	//获取当前年
	var year=myDate.getFullYear();
	//获取当前月
	var month=myDate.getMonth()+1;
	//获取当前日
	var date=myDate.getDate(); 
	var h=myDate.getHours();       //获取当前小时数(0-23)
	var m=myDate.getMinutes();     //获取当前分钟数(0-59)
	var s=myDate.getSeconds();  
	return year+'/'+p(month)+"/"+p(date)+" "+p(h)+':'+p(m)+":"+p(s);
}
//获取日期
function getDate(){
	var myDate = new Date();
	//获取当前年
	var year=myDate.getFullYear();
	//获取当前月
	var month=myDate.getMonth()+1;
	//获取当前日	
	var date=myDate.getDate(); 
	return year+""+p(month)+""+p(date);
}