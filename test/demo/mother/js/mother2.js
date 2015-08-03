(function(){
	window.requestAnimFrame = (function() {
		return window.requestAnimationFrame ||
			window.webkitRequestAnimationFrame ||
			window.mozRequestAnimationFrame ||
			function(callback) {
				window.setTimeout(callback, 1000 / 60);
			};
	})();

	document.body.ontouchmove = function(e) {
    	e.preventDefault();
    };

	var $el = document.querySelector('#J_pages');
	var $audio = document.createElement('audio');
	var OLDNUM = -1;

	function initPage() {
		share();
		initShare();
		initMusic("images/music.mp3");
		initEvent();
		initTransEvent();
	}

	function share() {
		var shareEle = $el.querySelectorAll('[data-share]');
		if (shareEle.length) {
			var shareDom = document.createElement('div');
			shareDom.className = 'share';
			shareDom.innerHTML = '<div class="content"></div>';
			document.body.appendChild(shareDom);
			shareDom.addEventListener('click', function(){
				shareDom.style.display = 'none';
			});
			Array.prototype.forEach.call(shareEle, function(ele){
				ele.addEventListener('click', function(){
					shareDom.style.display = 'block';
				});
			});
		}
	}

	/**
	 * 微信分享、关注公众号
	 */
	function initShare() {
		if (typeof WeixinJSBridge == "object" && typeof WeixinJSBridge.invoke == "function") {
		    initShareEvent();
		} else {
		    if (document.addEventListener) {
		        document.addEventListener("WeixinJSBridgeReady", initShareEvent, false);
		    } else if (document.attachEvent) {
		        document.attachEvent("WeixinJSBridgeReady", initShareEvent);
		        document.attachEvent("onWeixinJSBridgeReady", initShareEvent);
		    }
		}
		function initShareEvent(){
			var url = location.protocol + "//" + location.host + location.pathname.replace(/[^\/]+$/, "");
			WeixinJSBridge.on("menu:share:appmessage", function() {
				WeixinJSBridge.invoke("sendAppMessage", {
					img_url: url + "images/share.png",
					link: 'http://wqs.jd.com/promote/2015/mum/index.html?ptag=17005.2.9',
					desc: "看过太多鸡汤文，母亲节给你一碗酸辣汤，辣到飙泪哦",
					title: "妈妈再打我一次"
				}, function(data){
					
				});
			});
			WeixinJSBridge.on("menu:share:timeline", function(h) {
				WeixinJSBridge.invoke("shareTimeline", {
					img_url: url + "images/share.png",
					img_width: "80",
					img_height: "80",
					link: 'http://wqs.jd.com/promote/2015/mum/index.html?ptag=17005.2.9',
					desc: "看过太多鸡汤文，母亲节给你一碗酸辣汤，辣到飙泪哦", 
					title: "妈妈再打我一次"
				}, function(data){
					
				});
			});
		}
	}

	/**
	 * 音乐
	 */
	function initMusic(src) {
		var baseUrl = location.protocol + "//" + location.host + location.pathname.replace(/[^\/]+$/, "");
        var trigger = 'ontouchend' in document? 'touchstart': 'click';
        var $music = document.querySelector('#J_music');

        function start() {
			document.removeEventListener(trigger, start, false);
			if(!$audio.paused) return;
			$audio.play();
        }
        
        function toggle() {
			if(!$audio.paused) return $audio.pause();
			$audio.currentTime = 0;
			$audio.play();
        }

        function play(e) {
			$music.className = 'music ne playing';
        }

        function pause(e) {
			$music.className = 'music ne';
        }

        function closeToTime(t) {
        	return Math.abs($audio.currentTime - t) < 0.5;
        }

        $audio.src = baseUrl + src;
        $audio.loop = true;
        document.body.appendChild($audio);

        $audio.addEventListener('play', play);
        $audio.addEventListener('pause', pause);
        $audio.addEventListener('ended', pause);
        $music.addEventListener('click', toggle);
        $audio.play()

        document.addEventListener(trigger, start);

        setInterval(function() {
			// 循环播放背景音效
			if (closeToTime(88.5)) {
				$audio.currentTime = 0;
			}
			// 如来神掌
			else if (closeToTime(105.5)) {
				$audio.currentTime = 95;
			}
			// 打狗棍法
			else if (closeToTime(128.5)) {
				$audio.currentTime = 119;
			}
			// 无影脚
			else if (closeToTime(138.5)) {
				$audio.currentTime = 131;
			}
			// 召唤术
			else if (closeToTime(116.5)) {
				$audio.currentTime = 108;
			}
		}, 200);
	}

	function playActMusic(num) {
		if (num == 1) {
			$audio.currentTime = 95;
		} else if (num == 2) {
			$audio.currentTime = 119;
		} else if (num == 3) {
			$audio.currentTime = 131;
		} else if (num == 4) {
			$audio.currentTime = 108;
		}
	}

	function resetMusic() {
		$audio.currentTime = 0;
	}

	function addPlay(ele) {
		ele.classList.add('play');
		ele.addEventListener('webkitAnimationEnd', function(evt){
			evt.target.classList.remove('play');
		});
	}

	function addPlayOut(ele) {
		ele.classList.add('playout');
		ele.addEventListener('webkitAnimationEnd', function(evt){
			evt.target.classList.remove('playout');
			evt.target.classList.add('hide');
		});
	}

	function initEvent() {
		document.querySelector('.page-1 .btn').addEventListener('click', function(){
			showPage(2);
		});

		var p2ChoiceArr = document.querySelectorAll('[data-stage]');
		for (var i = 0; i < p2ChoiceArr.length; i++) {
			p2ChoiceArr[i].addEventListener('click', function(){
				var stage = this.getAttribute('data-stage') | 0;
				document.querySelector('#J_stage').className = 'stage';
				document.querySelector('#J_stage').classList.add('stage' + stage);
				document.querySelector('#J_stage').setAttribute('data-nstage', stage);
				showPage(3);
			});
		}

		document.querySelector('#J_end_btn').addEventListener('click', function(){
			resetMusic();
			showPage(5);
		});

		document.querySelector('#J_start_btn').addEventListener('click', function(){
			document.querySelector('#J_stage').className = 'stage';
			document.querySelector('#J_stage2').className = 'stage';
			document.querySelector('#J_frame').className = 'frame';
			resetMusic();
			showPage(2);
		});

		document.querySelector('#J_restart_btn').addEventListener('click', function(){
			document.querySelector('#J_stage').className = 'stage';
			document.querySelector('#J_stage2').className = 'stage';
			document.querySelector('#J_frame').className = 'frame';
			resetMusic();
			showPage(2);
		});
	}

	function initTransEvent() {
		addAnimationEnd(".page-3 .stage .mother", function(evt){
			evt.target.classList.add('end');
			if (document.querySelector('#J_stage').classList.contains('act1')) {
				document.querySelector('.page-3 .stage .hands').classList.remove('hide');
			}
			if (document.querySelector('#J_stage').classList.contains('act4')) {
				document.querySelector('.page-3 .stage .texts').classList.remove('hide');
				document.querySelector('.page-3 .stage .father').classList.remove('hide');
			}
		});
		addAnimationEnd(".page-3 .stage .father", function(evt){
			evt.target.classList.add('end');
		});
		addAnimationEnd(".page-3 .stage .son", function(evt){
			evt.target.classList.add('end');
			setTimeout(function(){
				var laterEle = document.querySelectorAll('.later');
				for (var i = 0; i < laterEle.length; i++) {
					laterEle[i].classList.remove('animating');
				}
				var stage = document.querySelector('#J_stage').getAttribute('data-nstage') | 0;
				document.querySelector('#J_tvshow').className = "tvshow";
				document.querySelector('#J_tvshow').classList.add('t' + stage);
				showPage(4);
				var endArr = document.querySelectorAll('.page-4 .later');
				for (var i = 0; i < endArr.length; i++) {
					endArr[i].classList.add('end');
				}
			}, 3000);
		});
		addAnimationEnd(".page-3 .dialogue", function(evt){
			setTimeout(function(){
				addPlay(document.querySelector('#J_tricks'));
				// document.querySelector('#J_tricks').classList.add('play');
				document.querySelector('#J_tricks').classList.remove('hide');
				marqueeEffect();
			}, 500);
		});
	}

	// 跑马灯效果
	function marqueeEffect() {
		var choices = document.querySelectorAll('.tricks>div'),
			len 	= choices.length,
			index 	= 0,
			flag 	= 0,
			// rNum	= Math.floor(Math.random()*4),
			rNum	= 3,
			timer	= 0;

		while (rNum == OLDNUM) {
			rNum = Math.floor(Math.random()*4);
		}
		OLDNUM = rNum;

		document.querySelector('#J_stage').classList.remove('act1,act2,act3,act4');
		document.querySelector('#J_stage').classList.add('act' + (rNum+1));
		document.querySelector('#J_stage').setAttribute('data-nact', (rNum+1));
		document.querySelector('#J_stage2').classList.remove('act1,act2,act3,act4');
		document.querySelector('#J_stage2').classList.add('act' + (rNum+1));
		document.querySelector('#J_frame').classList.remove('f1,f2,f3,f4');
		document.querySelector('#J_frame').classList.add('f' + (rNum+1));

		if (rNum == 1) {
			rNum = 0;
		} else if (rNum == 0) {
			rNum = 1;
		}

		function loop() {
			flag = index % 4;
			for (var i = 0; i < len; i++) {
				if (flag == i) {
					var oldClass = choices[i].className;
					choices[i].className = oldClass + ' light';
				} else {
					var classArr = choices[i].className.split(' ');
					choices[i].className = classArr[0];
				}
			}
			index++;
			if (index >= (24 + rNum) && timer) {
				clearTimeout(timer);
				setTimeout(function(){
					// addPlayOut(document.querySelector('#J_tricks'));
					// document.querySelector('#J_tricks').classList.add('playout');
					document.querySelector('#J_tricks').classList.add('hide');
					animateLater();
				}, 2000);
				return;
			}
			timer = setTimeout(function(){
				loop();
			}, 100);
		}

		loop();
	}

	function animateLater() {
		var act = document.querySelector('#J_stage').getAttribute('data-nact') | 0;
		playActMusic(act);
		var laterArr = document.querySelectorAll('.later');
		for (var i = 0; i < laterArr.length; i++) {
			laterArr[i].classList.add('animating');
		}
	}

	function addAnimationEnd(id, func) {
		var ele = document.querySelector(id);
		ele.addEventListener('webkitAnimationEnd', func);
	}

	function showPage(num) {
		// clean
		var endArr = document.querySelectorAll('.end');
		for (var i = 0; i < endArr.length; i++) {
			endArr[i].classList.remove('end');
		}
		document.querySelector('.hands').classList.add('hide');
		document.querySelector('.texts').classList.add('hide');
		document.querySelector('.father').classList.add('hide');

		var pageArr = document.querySelectorAll('.page');
		for (var i = 0; i < pageArr.length; i++) {
			pageArr[i].classList.add('hide');
		}
		// pageArr[num-1].classList.add('play');
		addPlay(pageArr[num-1]);
		pageArr[num-1].classList.remove('hide');
	}

	initPage();
})();