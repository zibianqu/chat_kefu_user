<html>
<head>
<title>客服</title>
<script type="text/javascript" src="js/swfobject.js"></script>
<script type="text/javascript" src="js/web_socket.js"></script>
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/chatkefu.js"></script>
</head>
<style>
.content {
	width: 45%;
	margin: 230px auto;
	border: 1px solid black;
	overflow: hidden;
}

.kehulist {
	float: left;
	overflow: hidden;
}

.kehulist li {
	list-style-type: none;
	cursor: pointer;
}

.chat {
	float: right;
	width: 70%;
	margin: 0;
	border: 0px solid red;
	display: none;
}

.chat_content {
	height: 250px;
	overflow-y: scroll;
	border: 1px solid red;
}

.chat_content p {
	margin-top: 20px;
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
	color: blue;
}

.chat_my a {
	color: blue;
	line-height: 30px;
}

.chat_other {
	text-align: left;
	margin: 5px;
}

.chat_other span {
	margin: 5px;
}

.chat_other b {
	color: red;
	margin: 5px;
}

.chat_other a {
	color: red;
	margin: 5px;
	line-height: 30px;
}

.chat button {
	float: right;
}

.circle {
	padding:0 3px;
	background-color: red;
	border-radius: 50%;
	-moz-border-radius: 50%;
	-webkit-border-radius: 50%;
	color:#fff;
	display:none;
}
</style>

<body onload="connect();">
	<div class="content">
		<div class="kehulist">
			<ul>
				<li attr="aaaaaaaaaaaa"><b></b></li>
			</ul>
		</div>
		<div class="chat" id="000000">
			<div class="chat_content"></div>
			<textarea id="sendmsg"></textarea>
			<button>發送</button>
		</div>

		<div class="chat" id="aaaaaaaaaaaa">
			<div class="chat_content">
				<p class="chat_other">
					<b>aaaaaaaaaaaa</b> <span>2018/05/09 15:40:30</span><br> <a>请问你要咨询什么？</a>
				</p>

				<p class="chat_my">
					<span>2018/05/09 15:40:30</span><b>我</b> <br> <a>我想买个盒子</a>
				</p>
				<p class="chat_other">
					<b>aaaaaaaaaaaa</b> <span>2018/05/09 15:40:30</span><br> <a>请问你要咨询什么？</a>
				</p>
				<p class="chat_my">
					<span>2018/05/09 15:40:30</span><b>我</b> <br> <a>多少钱</a>
				</p>
				<p class="chat_my">
					<span>2018/05/09 15:40:30</span><b>我</b> <br> <a>多少钱</a>
				</p>
				<p class="chat_other">
					<b>aaaaaaaaaaaa</b> <span>2018/05/09 15:40:30</span><br> <a>看你选什么款的</a>
				</p>
				<p class="chat_my">
					<span>2018/05/09 15:40:30</span><b>我</b> <br> <a>多少钱</a>
				</p>
			</div>
			<textarea id="sendmsg"></textarea>
			<button>發送</button>
		</div>

	</div>
</body>
</html>