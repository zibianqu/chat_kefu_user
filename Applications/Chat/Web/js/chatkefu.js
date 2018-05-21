if (typeof console == "undefined") {    this.console = { log: function (msg) {  } };}
//如果浏览器不支持websocket，会使用这个flash自动模拟websocket协议，此过程对开发者透明
WEB_SOCKET_SWF_LOCATION = "SWF/WebSocketMain.swf";
//开启flash的websocket debug
WEB_SOCKET_DEBUG = true;
var ws, client_name, client_list={};

//连接服务端
function connect() {
    // 创建websocket
    ws = new WebSocket("ws://"+document.domain+":7272");
    // 当socket连接打开时，输入用户名
    ws.onopen = onopen;
    // 当有消息时根据消息类型显示不同信息
    ws.onmessage = onmessage; 
    ws.onclose = function() {
    	console.log("连接关闭，定时重连");
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
//	client_name=$("#kefu_client_name").val();//这里可以设置client_name 客服名称
	var kefu_client_name=localStorage.getItem("kefu_client_name"+getDate());
	if(kefu_client_name!="" && kefu_client_name!=null &&  kefu_client_name!=undefined ){
		client_name=kefu_client_name;
	}else{
		client_name="客服小姐姐";//这里可以设置 客服名称，由后端传入前端
	}
//	client_name="客服小姐姐";//直接写死
    // 登录
    var login_data = '{"type":"login","client_name":"'+client_name+'","room_id":"1","iskefu":1}';
    console.log("websocket握手成功，发送登录数据:"+login_data);
    ws.send(login_data);
}

//服务端发来消息时
function onmessage(e)
{
    console.log("onmessage  "+e.data);
    var data = eval("("+e.data+")");
    switch(data['type']){
        // 服务端ping客户端
        case 'ping':
            ws.send('{"type":"pong"}');
            break;;
        // 登录 更新用户列表
        case 'login':
            if(data['client_list']){
           		client_list = data['client_list'];
         		flush_client_list();
              	bindEvent();
              	localStorage.setItem("kefu_client_name"+getDate(),data['client_name']);
            }else{
                //这里是新的用户加入客服端聊天列表
            	if(client_list[data['client_name']]=="" || client_list[data['client_name']]==undefined){
                	client_list[data['client_name']] = data['client_name']; 
            		add_client_list(data['client_name'],data['client_name']);
            		bindEvent();
            	}
            }
            break;
        // 发言
        case 'say':
        	var str="",chat=$('#'+data['to_client_name']);
        	if(client_name!=data['from_client_name']){//用户发送过来的
        		str+='<p class="chat_other">';
        		str+='<b>用户：'+data['from_client_name']+'</b> <span>'+data['time']+'</span><br> <a>'+data['content']+'</a>';
        		str+='</p>';
        		setmsgnum(data['to_client_name']);
        	}else{
        		str+='<p class="chat_my">';
        		str+='<span>'+data['time']+'</span><b>客服：（'+data['from_client_name']+'）</b><br> <a>'+data['content']+'</a>';
        		str+='</p>';
        	}
        	appendmsg(chat.find(".chat_content"),str);
        	var saycontent=localStorage.getItem(data['to_client_name']+"say"+getDate());
        	if(saycontent!="" && saycontent!=null &&  saycontent!=undefined ){
        		saycontent+=str;
        	}else{
        		saycontent=str;
        	}
        	localStorage.setItem(data['to_client_name']+"say"+getDate(),saycontent);
            break;
        // 用户退出 更新用户列表
        case 'logout':
            delete client_list[data['from_client_name']];
            del_client_list(data['from_client_name']);
    }
}
function bindEvent(){
	//解除事件
    $(".kehulist li").each(function(i,obj){
   	 		$(obj).unbind('click');
   	   	 	var id=$(obj).attr('attr'),chat=$('#'+id),button=chat.find("button"),msg=chat.find("textarea");
   	   	   	button.unbind('click');
   	   	   	msg.unbind('focus');
    })
    //绑定事件
    $(".kehulist li").each(function(i,obj){
    	var id=$(obj).attr('attr'),chat=$('#'+id),button=chat.find("button"),msg=chat.find("textarea");
    	$(obj).click(function(){//切换客户
    		$('#000000').hide();
       		$(".kehulist li").each(function(j,objc){
       			var id1=$(objc).attr('attr');
       			$(objc).removeClass('current');
            	$('#'+id1).hide();
       	    })
    		chat.show();
       		$(obj).addClass('current');
       		$(obj).find(".circle").text(0)
			$(obj).find(".circle").hide();
       })
       button.on("click",null, function() {//点击发送信息
    		var content=msg.val(),time =getNowTime();
        	msg.val("");
        	if(content==""){
        		return ;
        	}
//         	console.log("button say"+'{"type":"say","to_client_id":"'+name+'","to_client_name":"'+$(obj).text()+'","content":"'+content+'","iskefu":"1"}')
        	ws.send('{"type":"say","to_client_id":"'+$(obj).attr("attr")+'","to_client_name":"'+$(obj).attr("attr")+'","client_name":"'+client_name+'","content":"'+content+'","iskefu":"1"}');
       	});
    	msg.focus(function(){
    		$(obj).find(".circle").text(0)
			$(obj).find(".circle").hide();
        });
           	
    });
}


//登陆后刷新客户
function flush_client_list(){
	var content=$(".content"),list=$('.kehulist ul'),chat=$('#000000'),str="";
	list.html("");
	for(var p in client_list){
		list.append('<li attr="'+p+'">'+client_list[p]+'<span class="circle">0</span></li>');
		str='<div class="chat" id="'+p+'">'
		str+=chat.html();
		str+='</div>'
		content.append(str);
    }
	var saycontent="",chat;
	for(var i in client_list){
		chat=$('#'+i);
		saycontent=localStorage.getItem(i+"say"+getDate());
      	if(saycontent!="" && saycontent!=null &&  saycontent!=undefined ){
      		chat.find(".chat_content").html("");
        	appendmsg(chat.find(".chat_content"),saycontent);
        }
	}
}
//添加新的客户
function add_client_list(id,value){
	var content=$(".content"),list=$('.kehulist ul'),chat=$('#000000'),str="";
	list.append('<li attr="'+id+'">'+value+'<span class="circle">0</span></li>');
	str='<div class="chat" id="'+id+'">'
	str+=chat.html();
	str+='</div>'
	content.append(str);
}

//断开链接时操作
function del_client_list(id){
	var content=$(".content"),list=$('.kehulist ul');
	$('#'+id).remove();
	$(".kehulist li").each(function(i,obj){
		if($(obj).attr('attr')==id){
			$(obj).remove();
		}
	});
}
//发送信息将信息添加到框内且将滚动条设置到底部
function appendmsg(obj,str){
	$(obj).append(str);
	setTimeout(function(){
		$(obj).scrollTop($(obj)[0].scrollHeight);
	}, 100);
}

function setmsgnum(attr){
	var text=$('#'+attr).find("textarea");
	$(".kehulist li").each(function(i,obj){
		if($(obj).attr("attr")==attr){
			if(true!=text.is(":focus")){  
				$(obj).find(".circle").text(Number($(obj).find(".circle").text())+1)
				$(obj).find(".circle").show();
			}else{
				$(obj).find(".circle").text(0)
				$(obj).find(".circle").hide();
			}
		}
	})
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