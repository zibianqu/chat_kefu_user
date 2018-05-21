<html>
<head>
<title>用户</title>
  <script type="text/javascript" src="js/swfobject.js"></script>
  <script type="text/javascript" src="js/web_socket.js"></script>
  <script type="text/javascript" src="js/jquery.min.js"></script>
  <script type="text/javascript" src="js/chatuser.js"></script>
</head>
<style>
.chat {
	width: 40%;
	margin: 230px auto;
	border: 0px solid red;
}

.chat_content {
	height: 250px;
	overflow-y: scroll;
	border:1px solid red;
}
.chat_content p{
	margin-top:20px;
}
.chat textarea {
	width: 100%;
	height: 80px;
	padding: 10px;
	border-top: 1.4px solid red;
}

.chat_my {
	text-align: right;
	margin: 5px;
}

.chat_my span {
	margin: 5px;
}

.chat_my b {
	margin: 5px;
	color:blue;
}
.chat_my a{
	color:blue;
	line-height:30px;
}
.chat_other{
	text-align: left;
	margin: 5px;
}
.chat_other span {
	margin: 5px;
}

.chat_other b {
	color:red;
	margin: 5px;
}
.chat_other a{
	color:red;
	margin:5px;
	line-height:30px;
}
</style>

<body onload="connect();">
	<div class="chat">
		<div class="chat_content">
			<p class="chat_other">
				<b>客服</b> <span>2018/05/09 15:40:30</span><br>
			<a>请问你要咨询什么？</a>
			</p>
		</div>
		<textarea id="sendmsg"></textarea>
		<button>發送</button>
	</div>
</body>
</html>