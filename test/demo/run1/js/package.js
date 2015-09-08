this.createjs=this.createjs||{},createjs.extend=function(t,e){"use strict";function i(){this.constructor=t}return i.prototype=e.prototype,t.prototype=new i},this.createjs=this.createjs||{},createjs.promote=function(t,e){"use strict";var i=t.prototype,s=Object.getPrototypeOf&&Object.getPrototypeOf(i)||i.__proto__;if(s){i[(e+="_")+"constructor"]=s.constructor;for(var r in s)i.hasOwnProperty(r)&&"function"==typeof s[r]&&(i[e+r]=s[r])}return t},this.createjs=this.createjs||{},createjs.indexOf=function(t,e){"use strict";for(var i=0,s=t.length;s>i;i++)if(e===t[i])return i;return-1},this.createjs=this.createjs||{},function(){"use strict";function t(t,e,i){this.type=t,this.target=null,this.currentTarget=null,this.eventPhase=0,this.bubbles=!!e,this.cancelable=!!i,this.timeStamp=(new Date).getTime(),this.defaultPrevented=!1,this.propagationStopped=!1,this.immediatePropagationStopped=!1,this.removed=!1}var e=t.prototype;e.preventDefault=function(){this.defaultPrevented=this.cancelable&&!0},e.stopPropagation=function(){this.propagationStopped=!0},e.stopImmediatePropagation=function(){this.immediatePropagationStopped=this.propagationStopped=!0},e.remove=function(){this.removed=!0},e.clone=function(){return new t(this.type,this.bubbles,this.cancelable)},e.set=function(t){for(var e in t)this[e]=t[e];return this},e.toString=function(){return"[Event (type="+this.type+")]"},createjs.Event=t}(),this.createjs=this.createjs||{},function(){"use strict";function t(){this._listeners=null,this._captureListeners=null}var e=t.prototype;t.initialize=function(t){t.addEventListener=e.addEventListener,t.on=e.on,t.removeEventListener=t.off=e.removeEventListener,t.removeAllEventListeners=e.removeAllEventListeners,t.hasEventListener=e.hasEventListener,t.dispatchEvent=e.dispatchEvent,t._dispatchEvent=e._dispatchEvent,t.willTrigger=e.willTrigger},e.addEventListener=function(t,e,i){var s;s=i?this._captureListeners=this._captureListeners||{}:this._listeners=this._listeners||{};var r=s[t];return r&&this.removeEventListener(t,e,i),r=s[t],r?r.push(e):s[t]=[e],e},e.on=function(t,e,i,s,r,n){return e.handleEvent&&(i=i||e,e=e.handleEvent),i=i||this,this.addEventListener(t,function(t){e.call(i,t,r),s&&t.remove()},n)},e.removeEventListener=function(t,e,i){var s=i?this._captureListeners:this._listeners;if(s){var r=s[t];if(r)for(var n=0,a=r.length;a>n;n++)if(r[n]==e){1==a?delete s[t]:r.splice(n,1);break}}},e.off=e.removeEventListener,e.removeAllEventListeners=function(t){t?(this._listeners&&delete this._listeners[t],this._captureListeners&&delete this._captureListeners[t]):this._listeners=this._captureListeners=null},e.dispatchEvent=function(t){if("string"==typeof t){var e=this._listeners;if(!e||!e[t])return!1;t=new createjs.Event(t)}else t.target&&t.clone&&(t=t.clone());try{t.target=this}catch(i){}if(t.bubbles&&this.parent){for(var s=this,r=[s];s.parent;)r.push(s=s.parent);var n,a=r.length;for(n=a-1;n>=0&&!t.propagationStopped;n--)r[n]._dispatchEvent(t,1+(0==n));for(n=1;a>n&&!t.propagationStopped;n++)r[n]._dispatchEvent(t,3)}else this._dispatchEvent(t,2);return t.defaultPrevented},e.hasEventListener=function(t){var e=this._listeners,i=this._captureListeners;return!!(e&&e[t]||i&&i[t])},e.willTrigger=function(t){for(var e=this;e;){if(e.hasEventListener(t))return!0;e=e.parent}return!1},e.toString=function(){return"[EventDispatcher]"},e._dispatchEvent=function(t,e){var i,s=1==e?this._captureListeners:this._listeners;if(t&&s){var r=s[t.type];if(!r||!(i=r.length))return;try{t.currentTarget=this}catch(n){}try{t.eventPhase=e}catch(n){}t.removed=!1,r=r.slice();for(var a=0;i>a&&!t.immediatePropagationStopped;a++){var o=r[a];o.handleEvent?o.handleEvent(t):o(t),t.removed&&(this.off(t.type,o,1==e),t.removed=!1)}}},createjs.EventDispatcher=t}(),this.createjs=this.createjs||{},function(){"use strict";function t(){throw"Ticker cannot be instantiated."}t.RAF_SYNCHED="synched",t.RAF="raf",t.TIMEOUT="timeout",t.useRAF=!1,t.timingMode=null,t.maxDelta=0,t.paused=!1,t.removeEventListener=null,t.removeAllEventListeners=null,t.dispatchEvent=null,t.hasEventListener=null,t._listeners=null,createjs.EventDispatcher.initialize(t),t._addEventListener=t.addEventListener,t.addEventListener=function(){return!t._inited&&t.init(),t._addEventListener.apply(t,arguments)},t._inited=!1,t._startTime=0,t._pausedTime=0,t._ticks=0,t._pausedTicks=0,t._interval=50,t._lastTime=0,t._times=null,t._tickTimes=null,t._timerId=null,t._raf=!0,t.setInterval=function(e){t._interval=e,t._inited&&t._setupTick()},t.getInterval=function(){return t._interval},t.setFPS=function(e){t.setInterval(1e3/e)},t.getFPS=function(){return 1e3/t._interval};try{Object.defineProperties(t,{interval:{get:t.getInterval,set:t.setInterval},framerate:{get:t.getFPS,set:t.setFPS}})}catch(e){console.log(e)}t.init=function(){t._inited||(t._inited=!0,t._times=[],t._tickTimes=[],t._startTime=t._getTime(),t._times.push(t._lastTime=0),t.interval=t._interval)},t.reset=function(){if(t._raf){var e=window.cancelAnimationFrame||window.webkitCancelAnimationFrame||window.mozCancelAnimationFrame||window.oCancelAnimationFrame||window.msCancelAnimationFrame;e&&e(t._timerId)}else clearTimeout(t._timerId);t.removeAllEventListeners("tick"),t._timerId=t._times=t._tickTimes=null,t._startTime=t._lastTime=t._ticks=0,t._inited=!1},t.getMeasuredTickTime=function(e){var i=0,s=t._tickTimes;if(!s||s.length<1)return-1;e=Math.min(s.length,e||0|t.getFPS());for(var r=0;e>r;r++)i+=s[r];return i/e},t.getMeasuredFPS=function(e){var i=t._times;return!i||i.length<2?-1:(e=Math.min(i.length-1,e||0|t.getFPS()),1e3/((i[0]-i[e])/e))},t.setPaused=function(e){t.paused=e},t.getPaused=function(){return t.paused},t.getTime=function(e){return t._startTime?t._getTime()-(e?t._pausedTime:0):-1},t.getEventTime=function(e){return t._startTime?(t._lastTime||t._startTime)-(e?t._pausedTime:0):-1},t.getTicks=function(e){return t._ticks-(e?t._pausedTicks:0)},t._handleSynch=function(){t._timerId=null,t._setupTick(),t._getTime()-t._lastTime>=.97*(t._interval-1)&&t._tick()},t._handleRAF=function(){t._timerId=null,t._setupTick(),t._tick()},t._handleTimeout=function(){t._timerId=null,t._setupTick(),t._tick()},t._setupTick=function(){if(null==t._timerId){var e=t.timingMode||t.useRAF&&t.RAF_SYNCHED;if(e==t.RAF_SYNCHED||e==t.RAF){var i=window.requestAnimationFrame||window.webkitRequestAnimationFrame||window.mozRequestAnimationFrame||window.oRequestAnimationFrame||window.msRequestAnimationFrame;if(i)return t._timerId=i(e==t.RAF?t._handleRAF:t._handleSynch),void(t._raf=!0)}t._raf=!1,t._timerId=setTimeout(t._handleTimeout,t._interval)}},t._tick=function(){var e=t.paused,i=t._getTime(),s=i-t._lastTime;if(t._lastTime=i,t._ticks++,e&&(t._pausedTicks++,t._pausedTime+=s),t.hasEventListener("tick")){var r=new createjs.Event("tick"),n=t.maxDelta;r.delta=n&&s>n?n:s,r.paused=e,r.time=i,r.runTime=i-t._pausedTime,t.dispatchEvent(r)}for(t._tickTimes.unshift(t._getTime()-i);t._tickTimes.length>100;)t._tickTimes.pop();for(t._times.unshift(i);t._times.length>100;)t._times.pop()};var i=window.performance&&(performance.now||performance.mozNow||performance.msNow||performance.oNow||performance.webkitNow);t._getTime=function(){return(i&&i.call(performance)||(new Date).getTime())-t._startTime},createjs.Ticker=t}(),this.createjs=this.createjs||{},function(){"use strict";function t(){throw"UID cannot be instantiated"}t._nextID=0,t.get=function(){return t._nextID++},createjs.UID=t}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e,i,s,r,n,a,o,h,c){this.Event_constructor(t,e,i),this.stageX=s,this.stageY=r,this.rawX=null==h?s:h,this.rawY=null==c?r:c,this.nativeEvent=n,this.pointerID=a,this.primary=!!o}var e=createjs.extend(t,createjs.Event);e._get_localX=function(){return this.currentTarget.globalToLocal(this.rawX,this.rawY).x},e._get_localY=function(){return this.currentTarget.globalToLocal(this.rawX,this.rawY).y},e._get_isTouch=function(){return-1!==this.pointerID};try{Object.defineProperties(e,{localX:{get:e._get_localX},localY:{get:e._get_localY},isTouch:{get:e._get_isTouch}})}catch(i){}e.clone=function(){return new t(this.type,this.bubbles,this.cancelable,this.stageX,this.stageY,this.nativeEvent,this.pointerID,this.primary,this.rawX,this.rawY)},e.toString=function(){return"[MouseEvent (type="+this.type+" stageX="+this.stageX+" stageY="+this.stageY+")]"},createjs.MouseEvent=createjs.promote(t,"Event")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e,i,s,r,n){this.setValues(t,e,i,s,r,n)}var e=t.prototype;t.DEG_TO_RAD=Math.PI/180,t.identity=null,e.setValues=function(t,e,i,s,r,n){return this.a=null==t?1:t,this.b=e||0,this.c=i||0,this.d=null==s?1:s,this.tx=r||0,this.ty=n||0,this},e.append=function(t,e,i,s,r,n){var a=this.a,o=this.b,h=this.c,c=this.d;return(1!=t||0!=e||0!=i||1!=s)&&(this.a=a*t+h*e,this.b=o*t+c*e,this.c=a*i+h*s,this.d=o*i+c*s),this.tx=a*r+h*n+this.tx,this.ty=o*r+c*n+this.ty,this},e.prepend=function(t,e,i,s,r,n){var a=this.a,o=this.c,h=this.tx;return this.a=t*a+i*this.b,this.b=e*a+s*this.b,this.c=t*o+i*this.d,this.d=e*o+s*this.d,this.tx=t*h+i*this.ty+r,this.ty=e*h+s*this.ty+n,this},e.appendMatrix=function(t){return this.append(t.a,t.b,t.c,t.d,t.tx,t.ty)},e.prependMatrix=function(t){return this.prepend(t.a,t.b,t.c,t.d,t.tx,t.ty)},e.appendTransform=function(e,i,s,r,n,a,o,h,c){if(n%360)var u=n*t.DEG_TO_RAD,l=Math.cos(u),d=Math.sin(u);else l=1,d=0;return a||o?(a*=t.DEG_TO_RAD,o*=t.DEG_TO_RAD,this.append(Math.cos(o),Math.sin(o),-Math.sin(a),Math.cos(a),e,i),this.append(l*s,d*s,-d*r,l*r,0,0)):this.append(l*s,d*s,-d*r,l*r,e,i),(h||c)&&(this.tx-=h*this.a+c*this.c,this.ty-=h*this.b+c*this.d),this},e.prependTransform=function(e,i,s,r,n,a,o,h,c){if(n%360)var u=n*t.DEG_TO_RAD,l=Math.cos(u),d=Math.sin(u);else l=1,d=0;return(h||c)&&(this.tx-=h,this.ty-=c),a||o?(a*=t.DEG_TO_RAD,o*=t.DEG_TO_RAD,this.prepend(l*s,d*s,-d*r,l*r,0,0),this.prepend(Math.cos(o),Math.sin(o),-Math.sin(a),Math.cos(a),e,i)):this.prepend(l*s,d*s,-d*r,l*r,e,i),this},e.rotate=function(e){e*=t.DEG_TO_RAD;var i=Math.cos(e),s=Math.sin(e),r=this.a,n=this.b;return this.a=r*i+this.c*s,this.b=n*i+this.d*s,this.c=-r*s+this.c*i,this.d=-n*s+this.d*i,this},e.skew=function(e,i){return e*=t.DEG_TO_RAD,i*=t.DEG_TO_RAD,this.append(Math.cos(i),Math.sin(i),-Math.sin(e),Math.cos(e),0,0),this},e.scale=function(t,e){return this.a*=t,this.b*=t,this.c*=e,this.d*=e,this},e.translate=function(t,e){return this.tx+=this.a*t+this.c*e,this.ty+=this.b*t+this.d*e,this},e.identity=function(){return this.a=this.d=1,this.b=this.c=this.tx=this.ty=0,this},e.invert=function(){var t=this.a,e=this.b,i=this.c,s=this.d,r=this.tx,n=t*s-e*i;return this.a=s/n,this.b=-e/n,this.c=-i/n,this.d=t/n,this.tx=(i*this.ty-s*r)/n,this.ty=-(t*this.ty-e*r)/n,this},e.isIdentity=function(){return 0===this.tx&&0===this.ty&&1===this.a&&0===this.b&&0===this.c&&1===this.d},e.equals=function(t){return this.tx===t.tx&&this.ty===t.ty&&this.a===t.a&&this.b===t.b&&this.c===t.c&&this.d===t.d},e.transformPoint=function(t,e,i){return i=i||{},i.x=t*this.a+e*this.c+this.tx,i.y=t*this.b+e*this.d+this.ty,i},e.decompose=function(e){null==e&&(e={}),e.x=this.tx,e.y=this.ty,e.scaleX=Math.sqrt(this.a*this.a+this.b*this.b),e.scaleY=Math.sqrt(this.c*this.c+this.d*this.d);var i=Math.atan2(-this.c,this.d),s=Math.atan2(this.b,this.a),r=Math.abs(1-i/s);return 1e-5>r?(e.rotation=s/t.DEG_TO_RAD,this.a<0&&this.d>=0&&(e.rotation+=e.rotation<=0?180:-180),e.skewX=e.skewY=0):(e.skewX=i/t.DEG_TO_RAD,e.skewY=s/t.DEG_TO_RAD),e},e.copy=function(t){return this.setValues(t.a,t.b,t.c,t.d,t.tx,t.ty)},e.clone=function(){return new t(this.a,this.b,this.c,this.d,this.tx,this.ty)},e.toString=function(){return"[Matrix2D (a="+this.a+" b="+this.b+" c="+this.c+" d="+this.d+" tx="+this.tx+" ty="+this.ty+")]"},t.identity=new t,createjs.Matrix2D=t}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e,i,s,r){this.setValues(t,e,i,s,r)}var e=t.prototype;e.setValues=function(t,e,i,s,r){return this.visible=null==t?!0:!!t,this.alpha=null==e?1:e,this.shadow=i,this.compositeOperation=i,this.matrix=r||this.matrix&&this.matrix.identity()||new createjs.Matrix2D,this},e.append=function(t,e,i,s,r){return this.alpha*=e,this.shadow=i||this.shadow,this.compositeOperation=s||this.compositeOperation,this.visible=this.visible&&t,r&&this.matrix.appendMatrix(r),this},e.prepend=function(t,e,i,s,r){return this.alpha*=e,this.shadow=this.shadow||i,this.compositeOperation=this.compositeOperation||s,this.visible=this.visible&&t,r&&this.matrix.prependMatrix(r),this},e.identity=function(){return this.visible=!0,this.alpha=1,this.shadow=this.compositeOperation=null,this.matrix.identity(),this},e.clone=function(){return new t(this.alpha,this.shadow,this.compositeOperation,this.visible,this.matrix.clone())},createjs.DisplayProps=t}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e){this.setValues(t,e)}var e=t.prototype;e.setValues=function(t,e){return this.x=t||0,this.y=e||0,this},e.copy=function(t){return this.x=t.x,this.y=t.y,this},e.clone=function(){return new t(this.x,this.y)},e.toString=function(){return"[Point (x="+this.x+" y="+this.y+")]"},createjs.Point=t}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e,i,s){this.setValues(t,e,i,s)}var e=t.prototype;e.setValues=function(t,e,i,s){return this.x=t||0,this.y=e||0,this.width=i||0,this.height=s||0,this},e.extend=function(t,e,i,s){return i=i||0,s=s||0,t+i>this.x+this.width&&(this.width=t+i-this.x),e+s>this.y+this.height&&(this.height=e+s-this.y),t<this.x&&(this.width+=this.x-t,this.x=t),e<this.y&&(this.height+=this.y-e,this.y=e),this},e.pad=function(t,e,i,s){return this.x-=t,this.y-=e,this.width+=t+i,this.height+=e+s,this},e.copy=function(t){return this.setValues(t.x,t.y,t.width,t.height)},e.contains=function(t,e,i,s){return i=i||0,s=s||0,t>=this.x&&t+i<=this.x+this.width&&e>=this.y&&e+s<=this.y+this.height},e.union=function(t){return this.clone().extend(t.x,t.y,t.width,t.height)},e.intersection=function(e){var i=e.x,s=e.y,r=i+e.width,n=s+e.height;return this.x>i&&(i=this.x),this.y>s&&(s=this.y),this.x+this.width<r&&(r=this.x+this.width),this.y+this.height<n&&(n=this.y+this.height),i>=r||s>=n?null:new t(i,s,r-i,n-s)},e.intersects=function(t){return t.x<=this.x+this.width&&this.x<=t.x+t.width&&t.y<=this.y+this.height&&this.y<=t.y+t.height},e.isEmpty=function(){return this.width<=0||this.height<=0},e.clone=function(){return new t(this.x,this.y,this.width,this.height)},e.toString=function(){return"[Rectangle (x="+this.x+" y="+this.y+" width="+this.width+" height="+this.height+")]"},createjs.Rectangle=t}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e,i,s,r,n,a){t.addEventListener&&(this.target=t,this.overLabel=null==i?"over":i,this.outLabel=null==e?"out":e,this.downLabel=null==s?"down":s,this.play=r,this._isPressed=!1,this._isOver=!1,this._enabled=!1,t.mouseChildren=!1,this.enabled=!0,this.handleEvent({}),n&&(a&&(n.actionsEnabled=!1,n.gotoAndStop&&n.gotoAndStop(a)),t.hitArea=n))}var e=t.prototype;e.setEnabled=function(t){if(t!=this._enabled){var e=this.target;this._enabled=t,t?(e.cursor="pointer",e.addEventListener("rollover",this),e.addEventListener("rollout",this),e.addEventListener("mousedown",this),e.addEventListener("pressup",this)):(e.cursor=null,e.removeEventListener("rollover",this),e.removeEventListener("rollout",this),e.removeEventListener("mousedown",this),e.removeEventListener("pressup",this))}},e.getEnabled=function(){return this._enabled};try{Object.defineProperties(e,{enabled:{get:e.getEnabled,set:e.setEnabled}})}catch(i){}e.toString=function(){return"[ButtonHelper]"},e.handleEvent=function(t){var e,i=this.target,s=t.type;"mousedown"==s?(this._isPressed=!0,e=this.downLabel):"pressup"==s?(this._isPressed=!1,e=this._isOver?this.overLabel:this.outLabel):"rollover"==s?(this._isOver=!0,e=this._isPressed?this.downLabel:this.overLabel):(this._isOver=!1,e=this._isPressed?this.overLabel:this.outLabel),this.play?i.gotoAndPlay&&i.gotoAndPlay(e):i.gotoAndStop&&i.gotoAndStop(e)},createjs.ButtonHelper=t}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e,i,s){this.color=t||"black",this.offsetX=e||0,this.offsetY=i||0,this.blur=s||0}var e=t.prototype;t.identity=new t("transparent",0,0,0),e.toString=function(){return"[Shadow]"},e.clone=function(){return new t(this.color,this.offsetX,this.offsetY,this.blur)},createjs.Shadow=t}(),this.createjs=this.createjs||{},function(){"use strict";function t(t){this.EventDispatcher_constructor(),this.complete=!0,this.framerate=0,this._animations=null,this._frames=null,this._images=null,this._data=null,this._loadCount=0,this._frameHeight=0,this._frameWidth=0,this._numFrames=0,this._regX=0,this._regY=0,this._spacing=0,this._margin=0,this._parseData(t)}var e=createjs.extend(t,createjs.EventDispatcher);e.getAnimations=function(){return this._animations.slice()};try{Object.defineProperties(e,{animations:{get:e.getAnimations}})}catch(i){}e.getNumFrames=function(t){if(null==t)return this._frames?this._frames.length:this._numFrames||0;var e=this._data[t];return null==e?0:e.frames.length},e.getAnimation=function(t){return this._data[t]},e.getFrame=function(t){var e;return this._frames&&(e=this._frames[t])?e:null},e.getFrameBounds=function(t,e){var i=this.getFrame(t);return i?(e||new createjs.Rectangle).setValues(-i.regX,-i.regY,i.rect.width,i.rect.height):null},e.toString=function(){return"[SpriteSheet]"},e.clone=function(){throw"SpriteSheet cannot be cloned."},e._parseData=function(t){var e,i,s,r;if(null!=t){if(this.framerate=t.framerate||0,t.images&&(i=t.images.length)>0)for(r=this._images=[],e=0;i>e;e++){var n=t.images[e];if("string"==typeof n){var a=n;n=document.createElement("img"),n.src=a}r.push(n),n.getContext||n.complete||(this._loadCount++,this.complete=!1,function(t){n.onload=function(){t._handleImageLoad()}}(this))}if(null==t.frames);else if(t.frames instanceof Array)for(this._frames=[],r=t.frames,e=0,i=r.length;i>e;e++){var o=r[e];this._frames.push({image:this._images[o[4]?o[4]:0],rect:new createjs.Rectangle(o[0],o[1],o[2],o[3]),regX:o[5]||0,regY:o[6]||0})}else s=t.frames,this._frameWidth=s.width,this._frameHeight=s.height,this._regX=s.regX||0,this._regY=s.regY||0,this._spacing=s.spacing||0,this._margin=s.margin||0,this._numFrames=s.count,0==this._loadCount&&this._calculateFrames();if(this._animations=[],null!=(s=t.animations)){this._data={};var h;for(h in s){var c={name:h},u=s[h];if("number"==typeof u)r=c.frames=[u];else if(u instanceof Array)if(1==u.length)c.frames=[u[0]];else for(c.speed=u[3],c.next=u[2],r=c.frames=[],e=u[0];e<=u[1];e++)r.push(e);else{c.speed=u.speed,c.next=u.next;var l=u.frames;r=c.frames="number"==typeof l?[l]:l.slice(0)}(c.next===!0||void 0===c.next)&&(c.next=h),(c.next===!1||r.length<2&&c.next==h)&&(c.next=null),c.speed||(c.speed=1),this._animations.push(h),this._data[h]=c}}}},e._handleImageLoad=function(){0==--this._loadCount&&(this._calculateFrames(),this.complete=!0,this.dispatchEvent("complete"))},e._calculateFrames=function(){if(!this._frames&&0!=this._frameWidth){this._frames=[];var t=this._numFrames||1e5,e=0,i=this._frameWidth,s=this._frameHeight,r=this._spacing,n=this._margin;t:for(var a=0,o=this._images;a<o.length;a++)for(var h=o[a],c=h.width,u=h.height,l=n;u-n-s>=l;){for(var d=n;c-n-i>=d;){if(e>=t)break t;e++,this._frames.push({image:h,rect:new createjs.Rectangle(d,l,i,s),regX:this._regX,regY:this._regY}),d+=i+r}l+=s+r}this._numFrames=e}},createjs.SpriteSheet=createjs.promote(t,"EventDispatcher")}(),this.createjs=this.createjs||{},function(){"use strict";function t(){this.command=null,this._stroke=null,this._strokeStyle=null,this._strokeIgnoreScale=!1,this._fill=null,this._instructions=[],this._commitIndex=0,this._activeInstructions=[],this._dirty=!1,this._storeIndex=0,this.clear()}var e=t.prototype,i=t;t.getRGB=function(t,e,i,s){return null!=t&&null==i&&(s=e,i=255&t,e=t>>8&255,t=t>>16&255),null==s?"rgb("+t+","+e+","+i+")":"rgba("+t+","+e+","+i+","+s+")"},t.getHSL=function(t,e,i,s){return null==s?"hsl("+t%360+","+e+"%,"+i+"%)":"hsla("+t%360+","+e+"%,"+i+"%,"+s+")"},t.BASE_64={A:0,B:1,C:2,D:3,E:4,F:5,G:6,H:7,I:8,J:9,K:10,L:11,M:12,N:13,O:14,P:15,Q:16,R:17,S:18,T:19,U:20,V:21,W:22,X:23,Y:24,Z:25,a:26,b:27,c:28,d:29,e:30,f:31,g:32,h:33,i:34,j:35,k:36,l:37,m:38,n:39,o:40,p:41,q:42,r:43,s:44,t:45,u:46,v:47,w:48,x:49,y:50,z:51,0:52,1:53,2:54,3:55,4:56,5:57,6:58,7:59,8:60,9:61,"+":62,"/":63},t.STROKE_CAPS_MAP=["butt","round","square"],t.STROKE_JOINTS_MAP=["miter","round","bevel"];var s=createjs.createCanvas?createjs.createCanvas():document.createElement("canvas");s.getContext&&(t._ctx=s.getContext("2d"),s.width=s.height=1),e.getInstructions=function(){return this._updateInstructions(),this._instructions};try{Object.defineProperties(e,{instructions:{get:e.getInstructions}})}catch(r){}e.isEmpty=function(){return!(this._instructions.length||this._activeInstructions.length)},e.draw=function(t,e){this._updateInstructions();for(var i=this._instructions,s=this._storeIndex,r=i.length;r>s;s++)i[s].exec(t,e)},e.drawAsPath=function(t){this._updateInstructions();for(var e,i=this._instructions,s=this._storeIndex,r=i.length;r>s;s++)(e=i[s]).path!==!1&&e.exec(t)},e.moveTo=function(t,e){return this.append(new i.MoveTo(t,e),!0)},e.lineTo=function(t,e){return this.append(new i.LineTo(t,e))},e.arcTo=function(t,e,s,r,n){return this.append(new i.ArcTo(t,e,s,r,n))},e.arc=function(t,e,s,r,n,a){return this.append(new i.Arc(t,e,s,r,n,a))},e.quadraticCurveTo=function(t,e,s,r){return this.append(new i.QuadraticCurveTo(t,e,s,r))},e.bezierCurveTo=function(t,e,s,r,n,a){return this.append(new i.BezierCurveTo(t,e,s,r,n,a))},e.rect=function(t,e,s,r){return this.append(new i.Rect(t,e,s,r))},e.closePath=function(){return this._activeInstructions.length?this.append(new i.ClosePath):this},e.clear=function(){return this._instructions.length=this._activeInstructions.length=this._commitIndex=0,this._strokeStyle=this._stroke=this._fill=null,this._dirty=this._strokeIgnoreScale=!1,this},e.beginFill=function(t){return this._setFill(t?new i.Fill(t):null)},e.beginLinearGradientFill=function(t,e,s,r,n,a){return this._setFill((new i.Fill).linearGradient(t,e,s,r,n,a))},e.beginRadialGradientFill=function(t,e,s,r,n,a,o,h){return this._setFill((new i.Fill).radialGradient(t,e,s,r,n,a,o,h))},e.beginBitmapFill=function(t,e,s){return this._setFill(new i.Fill(null,s).bitmap(t,e))},e.endFill=function(){return this.beginFill()},e.setStrokeStyle=function(t,e,s,r,n){return this._updateInstructions(!0),this._strokeStyle=this.command=new i.StrokeStyle(t,e,s,r,n),this._stroke&&(this._stroke.ignoreScale=n),this._strokeIgnoreScale=n,this},e.beginStroke=function(t){return this._setStroke(t?new i.Stroke(t):null)},e.beginLinearGradientStroke=function(t,e,s,r,n,a){return this._setStroke((new i.Stroke).linearGradient(t,e,s,r,n,a))},e.beginRadialGradientStroke=function(t,e,s,r,n,a,o,h){return this._setStroke((new i.Stroke).radialGradient(t,e,s,r,n,a,o,h))},e.beginBitmapStroke=function(t,e){return this._setStroke((new i.Stroke).bitmap(t,e))},e.endStroke=function(){return this.beginStroke()},e.curveTo=e.quadraticCurveTo,e.drawRect=e.rect,e.drawRoundRect=function(t,e,i,s,r){return this.drawRoundRectComplex(t,e,i,s,r,r,r,r)},e.drawRoundRectComplex=function(t,e,s,r,n,a,o,h){return this.append(new i.RoundRect(t,e,s,r,n,a,o,h))},e.drawCircle=function(t,e,s){return this.append(new i.Circle(t,e,s))},e.drawEllipse=function(t,e,s,r){return this.append(new i.Ellipse(t,e,s,r))},e.drawPolyStar=function(t,e,s,r,n,a){return this.append(new i.PolyStar(t,e,s,r,n,a))},e.append=function(t,e){return this._activeInstructions.push(t),this.command=t,e||(this._dirty=!0),this},e.decodePath=function(e){for(var i=[this.moveTo,this.lineTo,this.quadraticCurveTo,this.bezierCurveTo,this.closePath],s=[2,2,4,6,0],r=0,n=e.length,a=[],o=0,h=0,c=t.BASE_64;n>r;){var u=e.charAt(r),l=c[u],d=l>>3,_=i[d];if(!_||3&l)throw"bad path data (@"+r+"): "+u;var p=s[d];d||(o=h=0),a.length=0,r++;for(var f=(l>>2&1)+2,g=0;p>g;g++){var m=c[e.charAt(r)],v=m>>5?-1:1;m=(31&m)<<6|c[e.charAt(r+1)],3==f&&(m=m<<6|c[e.charAt(r+2)]),m=v*m/10,g%2?o=m+=o:h=m+=h,a[g]=m,r+=f}_.apply(this,a)}return this},e.store=function(){return this._updateInstructions(!0),this._storeIndex=this._instructions.length,this},e.unstore=function(){return this._storeIndex=0,this},e.clone=function(){var e=new t;return e.command=this.command,e._stroke=this._stroke,e._strokeStyle=this._strokeStyle,e._strokeIgnoreScale=this._strokeIgnoreScale,e._fill=this._fill,e._instructions=this._instructions.slice(),e._commitIndex=this._commitIndex,e._activeInstructions=this._activeInstructions.slice(),e._dirty=this._dirty,e._storeIndex=this._storeIndex,e},e.toString=function(){return"[Graphics]"},e.mt=e.moveTo,e.lt=e.lineTo,e.at=e.arcTo,e.bt=e.bezierCurveTo,e.qt=e.quadraticCurveTo,e.a=e.arc,e.r=e.rect,e.cp=e.closePath,e.c=e.clear,e.f=e.beginFill,e.lf=e.beginLinearGradientFill,e.rf=e.beginRadialGradientFill,e.bf=e.beginBitmapFill,e.ef=e.endFill,e.ss=e.setStrokeStyle,e.s=e.beginStroke,e.ls=e.beginLinearGradientStroke,e.rs=e.beginRadialGradientStroke,e.bs=e.beginBitmapStroke,e.es=e.endStroke,e.dr=e.drawRect,e.rr=e.drawRoundRect,e.rc=e.drawRoundRectComplex,e.dc=e.drawCircle,e.de=e.drawEllipse,e.dp=e.drawPolyStar,e.p=e.decodePath,e._updateInstructions=function(e){var i=this._instructions,s=this._activeInstructions,r=this._commitIndex;if(this._dirty&&s.length){i.length=r,i.push(t.beginCmd);var n=s.length,a=i.length;i.length=a+n;for(var o=0;n>o;o++)i[o+a]=s[o];this._fill&&i.push(this._fill),this._stroke&&this._strokeStyle&&i.push(this._strokeStyle),this._stroke&&i.push(this._stroke),this._dirty=!1}e&&(s.length=0,this._commitIndex=i.length)},e._setFill=function(t){return this._updateInstructions(!0),(this._fill=t)&&(this.command=t),this},e._setStroke=function(t){return this._updateInstructions(!0),(this._stroke=t)&&(this.command=t,t.ignoreScale=this._strokeIgnoreScale),this},(i.LineTo=function(t,e){this.x=t,this.y=e}).prototype.exec=function(t){t.lineTo(this.x,this.y)},(i.MoveTo=function(t,e){this.x=t,this.y=e}).prototype.exec=function(t){t.moveTo(this.x,this.y)},(i.ArcTo=function(t,e,i,s,r){this.x1=t,this.y1=e,this.x2=i,this.y2=s,this.radius=r}).prototype.exec=function(t){t.arcTo(this.x1,this.y1,this.x2,this.y2,this.radius)},(i.Arc=function(t,e,i,s,r,n){this.x=t,this.y=e,this.radius=i,this.startAngle=s,this.endAngle=r,this.anticlockwise=!!n}).prototype.exec=function(t){t.arc(this.x,this.y,this.radius,this.startAngle,this.endAngle,this.anticlockwise)},(i.QuadraticCurveTo=function(t,e,i,s){this.cpx=t,this.cpy=e,this.x=i,this.y=s}).prototype.exec=function(t){t.quadraticCurveTo(this.cpx,this.cpy,this.x,this.y)},(i.BezierCurveTo=function(t,e,i,s,r,n){this.cp1x=t,this.cp1y=e,this.cp2x=i,this.cp2y=s,this.x=r,this.y=n}).prototype.exec=function(t){t.bezierCurveTo(this.cp1x,this.cp1y,this.cp2x,this.cp2y,this.x,this.y)},(i.Rect=function(t,e,i,s){this.x=t,this.y=e,this.w=i,this.h=s}).prototype.exec=function(t){t.rect(this.x,this.y,this.w,this.h)},(i.ClosePath=function(){}).prototype.exec=function(t){t.closePath()},(i.BeginPath=function(){}).prototype.exec=function(t){t.beginPath()},e=(i.Fill=function(t,e){this.style=t,this.matrix=e}).prototype,e.exec=function(t){if(this.style){t.fillStyle=this.style;var e=this.matrix;e&&(t.save(),t.transform(e.a,e.b,e.c,e.d,e.tx,e.ty)),t.fill(),e&&t.restore()}},e.linearGradient=function(e,i,s,r,n,a){for(var o=this.style=t._ctx.createLinearGradient(s,r,n,a),h=0,c=e.length;c>h;h++)o.addColorStop(i[h],e[h]);return o.props={colors:e,ratios:i,x0:s,y0:r,x1:n,y1:a,type:"linear"},this},e.radialGradient=function(e,i,s,r,n,a,o,h){for(var c=this.style=t._ctx.createRadialGradient(s,r,n,a,o,h),u=0,l=e.length;l>u;u++)c.addColorStop(i[u],e[u]);return c.props={colors:e,ratios:i,x0:s,y0:r,r0:n,x1:a,y1:o,r1:h,type:"radial"},this},e.bitmap=function(e,i){var s=this.style=t._ctx.createPattern(e,i||"");return s.props={image:e,repetition:i,type:"bitmap"},this},e.path=!1,e=(i.Stroke=function(t,e){this.style=t,this.ignoreScale=e}).prototype,e.exec=function(t){this.style&&(t.strokeStyle=this.style,this.ignoreScale&&(t.save(),t.setTransform(1,0,0,1,0,0)),t.stroke(),this.ignoreScale&&t.restore())},e.linearGradient=i.Fill.prototype.linearGradient,e.radialGradient=i.Fill.prototype.radialGradient,e.bitmap=i.Fill.prototype.bitmap,e.path=!1,e=(i.StrokeStyle=function(t,e,i,s){this.width=t,this.caps=e,this.joints=i,this.miterLimit=s}).prototype,e.exec=function(e){e.lineWidth=null==this.width?"1":this.width,e.lineCap=null==this.caps?"butt":isNaN(this.caps)?this.caps:t.STROKE_CAPS_MAP[this.caps],e.lineJoin=null==this.joints?"miter":isNaN(this.joints)?this.joints:t.STROKE_JOINTS_MAP[this.joints],e.miterLimit=null==this.miterLimit?"10":this.miterLimit},e.path=!1,(i.RoundRect=function(t,e,i,s,r,n,a,o){this.x=t,this.y=e,this.w=i,this.h=s,this.radiusTL=r,this.radiusTR=n,this.radiusBR=a,this.radiusBL=o}).prototype.exec=function(t){var e=(c>h?h:c)/2,i=0,s=0,r=0,n=0,a=this.x,o=this.y,h=this.w,c=this.h,u=this.radiusTL,l=this.radiusTR,d=this.radiusBR,_=this.radiusBL;0>u&&(u*=i=-1),u>e&&(u=e),0>l&&(l*=s=-1),l>e&&(l=e),0>d&&(d*=r=-1),d>e&&(d=e),0>_&&(_*=n=-1),_>e&&(_=e),t.moveTo(a+h-l,o),t.arcTo(a+h+l*s,o-l*s,a+h,o+l,l),t.lineTo(a+h,o+c-d),t.arcTo(a+h+d*r,o+c+d*r,a+h-d,o+c,d),t.lineTo(a+_,o+c),t.arcTo(a-_*n,o+c+_*n,a,o+c-_,_),t.lineTo(a,o+u),t.arcTo(a-u*i,o-u*i,a+u,o,u),t.closePath()},(i.Circle=function(t,e,i){this.x=t,this.y=e,this.radius=i}).prototype.exec=function(t){t.arc(this.x,this.y,this.radius,0,2*Math.PI)},(i.Ellipse=function(t,e,i,s){this.x=t,this.y=e,this.w=i,this.h=s}).prototype.exec=function(t){var e=this.x,i=this.y,s=this.w,r=this.h,n=.5522848,a=s/2*n,o=r/2*n,h=e+s,c=i+r,u=e+s/2,l=i+r/2;t.moveTo(e,l),t.bezierCurveTo(e,l-o,u-a,i,u,i),t.bezierCurveTo(u+a,i,h,l-o,h,l),t.bezierCurveTo(h,l+o,u+a,c,u,c),t.bezierCurveTo(u-a,c,e,l+o,e,l)},(i.PolyStar=function(t,e,i,s,r,n){this.x=t,this.y=e,this.radius=i,this.sides=s,this.pointSize=r,this.angle=n}).prototype.exec=function(t){var e=this.x,i=this.y,s=this.radius,r=(this.angle||0)/180*Math.PI,n=this.sides,a=1-(this.pointSize||0),o=Math.PI/n;t.moveTo(e+Math.cos(r)*s,i+Math.sin(r)*s);for(var h=0;n>h;h++)r+=o,1!=a&&t.lineTo(e+Math.cos(r)*s*a,i+Math.sin(r)*s*a),r+=o,t.lineTo(e+Math.cos(r)*s,i+Math.sin(r)*s);t.closePath()},t.beginCmd=new i.BeginPath,createjs.Graphics=t}(),this.createjs=this.createjs||{},function(){"use strict";function t(){this.EventDispatcher_constructor(),this.alpha=1,this.cacheCanvas=null,this.cacheID=0,this.id=createjs.UID.get(),this.mouseEnabled=!0,this.tickEnabled=!0,this.name=null,this.parent=null,this.regX=0,this.regY=0,this.rotation=0,this.scaleX=1,this.scaleY=1,this.skewX=0,this.skewY=0,this.shadow=null,this.visible=!0,this.x=0,this.y=0,this.transformMatrix=null,this.compositeOperation=null,this.snapToPixel=!0,this.filters=null,this.mask=null,this.hitArea=null,this.cursor=null,this._cacheOffsetX=0,this._cacheOffsetY=0,this._filterOffsetX=0,this._filterOffsetY=0,this._cacheScale=1,this._cacheDataURLID=0,this._cacheDataURL=null,this._props=new createjs.DisplayProps,this._rectangle=new createjs.Rectangle,this._bounds=null}var e=createjs.extend(t,createjs.EventDispatcher);t._MOUSE_EVENTS=["click","dblclick","mousedown","mouseout","mouseover","pressmove","pressup","rollout","rollover"],t.suppressCrossDomainErrors=!1,t._snapToPixelEnabled=!1;var i=createjs.createCanvas?createjs.createCanvas():document.createElement("canvas");i.getContext&&(t._hitTestCanvas=i,t._hitTestContext=i.getContext("2d"),i.width=i.height=1),t._nextCacheID=1,e.getStage=function(){for(var t=this,e=createjs.Stage;t.parent;)t=t.parent;return t instanceof e?t:null};try{Object.defineProperties(e,{stage:{get:e.getStage}})}catch(s){}e.isVisible=function(){return!!(this.visible&&this.alpha>0&&0!=this.scaleX&&0!=this.scaleY)},e.draw=function(t,e){var i=this.cacheCanvas;if(e||!i)return!1;var s=this._cacheScale;return t.drawImage(i,this._cacheOffsetX+this._filterOffsetX,this._cacheOffsetY+this._filterOffsetY,i.width/s,i.height/s),!0},e.updateContext=function(e){var i=this,s=i.mask,r=i._props.matrix;

s&&s.graphics&&!s.graphics.isEmpty()&&(s.getMatrix(r),e.transform(r.a,r.b,r.c,r.d,r.tx,r.ty),s.graphics.drawAsPath(e),e.clip(),r.invert(),e.transform(r.a,r.b,r.c,r.d,r.tx,r.ty)),this.getMatrix(r);var n=r.tx,a=r.ty;t._snapToPixelEnabled&&i.snapToPixel&&(n=n+(0>n?-.5:.5)|0,a=a+(0>a?-.5:.5)|0),e.transform(r.a,r.b,r.c,r.d,n,a),e.globalAlpha*=i.alpha,i.compositeOperation&&(e.globalCompositeOperation=i.compositeOperation),i.shadow&&this._applyShadow(e,i.shadow)},e.cache=function(t,e,i,s,r){r=r||1,this.cacheCanvas||(this.cacheCanvas=createjs.createCanvas?createjs.createCanvas():document.createElement("canvas")),this._cacheWidth=i,this._cacheHeight=s,this._cacheOffsetX=t,this._cacheOffsetY=e,this._cacheScale=r,this.updateCache()},e.updateCache=function(e){var i=this.cacheCanvas;if(!i)throw"cache() must be called before updateCache()";var s=this._cacheScale,r=this._cacheOffsetX*s,n=this._cacheOffsetY*s,a=this._cacheWidth,o=this._cacheHeight,h=i.getContext("2d"),c=this._getFilterBounds();r+=this._filterOffsetX=c.x,n+=this._filterOffsetY=c.y,a=Math.ceil(a*s)+c.width,o=Math.ceil(o*s)+c.height,a!=i.width||o!=i.height?(i.width=a,i.height=o):e||h.clearRect(0,0,a+1,o+1),h.save(),h.globalCompositeOperation=e,h.setTransform(s,0,0,s,-r,-n),this.draw(h,!0),this._applyFilters(),h.restore(),this.cacheID=t._nextCacheID++},e.uncache=function(){this._cacheDataURL=this.cacheCanvas=null,this.cacheID=this._cacheOffsetX=this._cacheOffsetY=this._filterOffsetX=this._filterOffsetY=0,this._cacheScale=1},e.getCacheDataURL=function(){return this.cacheCanvas?(this.cacheID!=this._cacheDataURLID&&(this._cacheDataURL=this.cacheCanvas.toDataURL()),this._cacheDataURL):null},e.localToGlobal=function(t,e,i){return this.getConcatenatedMatrix(this._props.matrix).transformPoint(t,e,i||new createjs.Point)},e.globalToLocal=function(t,e,i){return this.getConcatenatedMatrix(this._props.matrix).invert().transformPoint(t,e,i||new createjs.Point)},e.localToLocal=function(t,e,i,s){return s=this.localToGlobal(t,e,s),i.globalToLocal(s.x,s.y,s)},e.setTransform=function(t,e,i,s,r,n,a,o,h){return this.x=t||0,this.y=e||0,this.scaleX=null==i?1:i,this.scaleY=null==s?1:s,this.rotation=r||0,this.skewX=n||0,this.skewY=a||0,this.regX=o||0,this.regY=h||0,this},e.getMatrix=function(t){var e=this,i=t&&t.identity()||new createjs.Matrix2D;return e.transformMatrix?i.copy(e.transformMatrix):i.appendTransform(e.x,e.y,e.scaleX,e.scaleY,e.rotation,e.skewX,e.skewY,e.regX,e.regY)},e.getConcatenatedMatrix=function(t){for(var e=this,i=this.getMatrix(t);e=e.parent;)i.prependMatrix(e.getMatrix(e._props.matrix));return i},e.getConcatenatedDisplayProps=function(t){t=t?t.identity():new createjs.DisplayProps;var e=this,i=e.getMatrix(t.matrix);do t.prepend(e.visible,e.alpha,e.shadow,e.compositeOperation),e!=this&&i.prependMatrix(e.getMatrix(e._props.matrix));while(e=e.parent);return t},e.hitTest=function(e,i){var s=t._hitTestContext;s.setTransform(1,0,0,1,-e,-i),this.draw(s);var r=this._testHit(s);return s.setTransform(1,0,0,1,0,0),s.clearRect(0,0,2,2),r},e.set=function(t){for(var e in t)this[e]=t[e];return this},e.getBounds=function(){if(this._bounds)return this._rectangle.copy(this._bounds);var t=this.cacheCanvas;if(t){var e=this._cacheScale;return this._rectangle.setValues(this._cacheOffsetX,this._cacheOffsetY,t.width/e,t.height/e)}return null},e.getTransformedBounds=function(){return this._getBounds()},e.setBounds=function(t,e,i,s){null==t&&(this._bounds=t),this._bounds=(this._bounds||new createjs.Rectangle).setValues(t,e,i,s)},e.clone=function(){return this._cloneProps(new t)},e.toString=function(){return"[DisplayObject (name="+this.name+")]"},e._cloneProps=function(t){return t.alpha=this.alpha,t.mouseEnabled=this.mouseEnabled,t.tickEnabled=this.tickEnabled,t.name=this.name,t.regX=this.regX,t.regY=this.regY,t.rotation=this.rotation,t.scaleX=this.scaleX,t.scaleY=this.scaleY,t.shadow=this.shadow,t.skewX=this.skewX,t.skewY=this.skewY,t.visible=this.visible,t.x=this.x,t.y=this.y,t.compositeOperation=this.compositeOperation,t.snapToPixel=this.snapToPixel,t.filters=null==this.filters?null:this.filters.slice(0),t.mask=this.mask,t.hitArea=this.hitArea,t.cursor=this.cursor,t._bounds=this._bounds,t},e._applyShadow=function(t,e){e=e||Shadow.identity,t.shadowColor=e.color,t.shadowOffsetX=e.offsetX,t.shadowOffsetY=e.offsetY,t.shadowBlur=e.blur},e._tick=function(t){var e=this._listeners;e&&e.tick&&(t.target=null,t.propagationStopped=t.immediatePropagationStopped=!1,this.dispatchEvent(t))},e._testHit=function(e){try{var i=e.getImageData(0,0,1,1).data[3]>1}catch(s){if(!t.suppressCrossDomainErrors)throw"An error has occurred. This is most likely due to security restrictions on reading canvas pixel data with local or cross-domain images."}return i},e._applyFilters=function(){if(this.filters&&0!=this.filters.length&&this.cacheCanvas)for(var t=this.filters.length,e=this.cacheCanvas.getContext("2d"),i=this.cacheCanvas.width,s=this.cacheCanvas.height,r=0;t>r;r++)this.filters[r].applyFilter(e,0,0,i,s)},e._getFilterBounds=function(){var t,e=this.filters,i=this._rectangle.setValues(0,0,0,0);if(!e||!(t=e.length))return i;for(var s=0;t>s;s++){var r=this.filters[s];r.getBounds&&r.getBounds(i)}return i},e._getBounds=function(t,e){return this._transformBounds(this.getBounds(),t,e)},e._transformBounds=function(t,e,i){if(!t)return t;var s=t.x,r=t.y,n=t.width,a=t.height,o=this._props.matrix;o=i?o.identity():this.getMatrix(o),(s||r)&&o.appendTransform(0,0,1,1,0,0,0,-s,-r),e&&o.prependMatrix(e);var h=n*o.a,c=n*o.b,u=a*o.c,l=a*o.d,d=o.tx,_=o.ty,p=d,f=d,g=_,m=_;return(s=h+d)<p?p=s:s>f&&(f=s),(s=h+u+d)<p?p=s:s>f&&(f=s),(s=u+d)<p?p=s:s>f&&(f=s),(r=c+_)<g?g=r:r>m&&(m=r),(r=c+l+_)<g?g=r:r>m&&(m=r),(r=l+_)<g?g=r:r>m&&(m=r),t.setValues(p,g,f-p,m-g)},e._hasMouseEventListener=function(){for(var e=t._MOUSE_EVENTS,i=0,s=e.length;s>i;i++)if(this.hasEventListener(e[i]))return!0;return!!this.cursor},createjs.DisplayObject=createjs.promote(t,"EventDispatcher")}(),this.createjs=this.createjs||{},function(){"use strict";function t(){this.DisplayObject_constructor(),this.children=[],this.mouseChildren=!0,this.tickChildren=!0}var e=createjs.extend(t,createjs.DisplayObject);e.getNumChildren=function(){return this.children.length};try{Object.defineProperties(e,{numChildren:{get:e.getNumChildren}})}catch(i){}e.initialize=t,e.isVisible=function(){var t=this.cacheCanvas||this.children.length;return!!(this.visible&&this.alpha>0&&0!=this.scaleX&&0!=this.scaleY&&t)},e.draw=function(t,e){if(this.DisplayObject_draw(t,e))return!0;for(var i=this.children.slice(),s=0,r=i.length;r>s;s++){var n=i[s];n.isVisible()&&(t.save(),n.updateContext(t),n.draw(t),t.restore())}return!0},e.addChild=function(t){if(null==t)return t;var e=arguments.length;if(e>1){for(var i=0;e>i;i++)this.addChild(arguments[i]);return arguments[e-1]}return t.parent&&t.parent.removeChild(t),t.parent=this,this.children.push(t),t.dispatchEvent("added"),t},e.addChildAt=function(t,e){var i=arguments.length,s=arguments[i-1];if(0>s||s>this.children.length)return arguments[i-2];if(i>2){for(var r=0;i-1>r;r++)this.addChildAt(arguments[r],s+r);return arguments[i-2]}return t.parent&&t.parent.removeChild(t),t.parent=this,this.children.splice(e,0,t),t.dispatchEvent("added"),t},e.removeChild=function(t){var e=arguments.length;if(e>1){for(var i=!0,s=0;e>s;s++)i=i&&this.removeChild(arguments[s]);return i}return this.removeChildAt(createjs.indexOf(this.children,t))},e.removeChildAt=function(t){var e=arguments.length;if(e>1){for(var i=[],s=0;e>s;s++)i[s]=arguments[s];i.sort(function(t,e){return e-t});for(var r=!0,s=0;e>s;s++)r=r&&this.removeChildAt(i[s]);return r}if(0>t||t>this.children.length-1)return!1;var n=this.children[t];return n&&(n.parent=null),this.children.splice(t,1),n.dispatchEvent("removed"),!0},e.removeAllChildren=function(){for(var t=this.children;t.length;)this.removeChildAt(0)},e.getChildAt=function(t){return this.children[t]},e.getChildByName=function(t){for(var e=this.children,i=0,s=e.length;s>i;i++)if(e[i].name==t)return e[i];return null},e.sortChildren=function(t){this.children.sort(t)},e.getChildIndex=function(t){return createjs.indexOf(this.children,t)},e.swapChildrenAt=function(t,e){var i=this.children,s=i[t],r=i[e];s&&r&&(i[t]=r,i[e]=s)},e.swapChildren=function(t,e){for(var i,s,r=this.children,n=0,a=r.length;a>n&&(r[n]==t&&(i=n),r[n]==e&&(s=n),null==i||null==s);n++);n!=a&&(r[i]=e,r[s]=t)},e.setChildIndex=function(t,e){var i=this.children,s=i.length;if(!(t.parent!=this||0>e||e>=s)){for(var r=0;s>r&&i[r]!=t;r++);r!=s&&r!=e&&(i.splice(r,1),i.splice(e,0,t))}},e.contains=function(t){for(;t;){if(t==this)return!0;t=t.parent}return!1},e.hitTest=function(t,e){return null!=this.getObjectUnderPoint(t,e)},e.getObjectsUnderPoint=function(t,e,i){var s=[],r=this.localToGlobal(t,e);return this._getObjectsUnderPoint(r.x,r.y,s,i>0,1==i),s},e.getObjectUnderPoint=function(t,e,i){var s=this.localToGlobal(t,e);return this._getObjectsUnderPoint(s.x,s.y,null,i>0,1==i)},e.getBounds=function(){return this._getBounds(null,!0)},e.getTransformedBounds=function(){return this._getBounds()},e.clone=function(e){var i=this._cloneProps(new t);return e&&this._cloneChildren(i),i},e.toString=function(){return"[Container (name="+this.name+")]"},e._tick=function(t){if(this.tickChildren)for(var e=this.children.length-1;e>=0;e--){var i=this.children[e];i.tickEnabled&&i._tick&&i._tick(t)}this.DisplayObject__tick(t)},e._cloneChildren=function(t){t.children.length&&t.removeAllChildren();for(var e=t.children,i=0,s=this.children.length;s>i;i++){var r=this.children[i].clone(!0);r.parent=t,e.push(r)}},e._getObjectsUnderPoint=function(e,i,s,r,n,a){if(a=a||0,!a&&!this._testMask(this,e,i))return null;var o,h=createjs.DisplayObject._hitTestContext;n=n||r&&this._hasMouseEventListener();for(var c=this.children,u=c.length,l=u-1;l>=0;l--){var d=c[l],_=d.hitArea;if(d.visible&&(_||d.isVisible())&&(!r||d.mouseEnabled)&&(_||this._testMask(d,e,i)))if(!_&&d instanceof t){var p=d._getObjectsUnderPoint(e,i,s,r,n,a+1);if(!s&&p)return r&&!this.mouseChildren?this:p}else{if(r&&!n&&!d._hasMouseEventListener())continue;var f=d.getConcatenatedDisplayProps(d._props);if(o=f.matrix,_&&(o.appendMatrix(_.getMatrix(_._props.matrix)),f.alpha=_.alpha),h.globalAlpha=f.alpha,h.setTransform(o.a,o.b,o.c,o.d,o.tx-e,o.ty-i),(_||d).draw(h),!this._testHit(h))continue;if(h.setTransform(1,0,0,1,0,0),h.clearRect(0,0,2,2),!s)return r&&!this.mouseChildren?this:d;s.push(d)}}return null},e._testMask=function(t,e,i){var s=t.mask;if(!s||!s.graphics||s.graphics.isEmpty())return!0;var r=this._props.matrix,n=t.parent;r=n?n.getConcatenatedMatrix(r):r.identity(),r=s.getMatrix(s._props.matrix).prependMatrix(r);var a=createjs.DisplayObject._hitTestContext;return a.setTransform(r.a,r.b,r.c,r.d,r.tx-e,r.ty-i),s.graphics.drawAsPath(a),a.fillStyle="#000",a.fill(),this._testHit(a)?(a.setTransform(1,0,0,1,0,0),a.clearRect(0,0,2,2),!0):!1},e._getBounds=function(t,e){var i=this.DisplayObject_getBounds();if(i)return this._transformBounds(i,t,e);var s=this._props.matrix;s=e?s.identity():this.getMatrix(s),t&&s.prependMatrix(t);for(var r=this.children.length,n=null,a=0;r>a;a++){var o=this.children[a];o.visible&&(i=o._getBounds(s))&&(n?n.extend(i.x,i.y,i.width,i.height):n=i.clone())}return n},createjs.Container=createjs.promote(t,"DisplayObject")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t){this.Container_constructor(),this.autoClear=!0,this.canvas="string"==typeof t?document.getElementById(t):t,this.mouseX=0,this.mouseY=0,this.drawRect=null,this.snapToPixelEnabled=!1,this.mouseInBounds=!1,this.tickOnUpdate=!0,this.mouseMoveOutside=!1,this.preventSelection=!0,this._pointerData={},this._pointerCount=0,this._primaryPointerID=null,this._mouseOverIntervalID=null,this._nextStage=null,this._prevStage=null,this.enableDOMEvents(!0)}var e=createjs.extend(t,createjs.Container);e._get_nextStage=function(){return this._nextStage},e._set_nextStage=function(t){this._nextStage&&(this._nextStage._prevStage=null),t&&(t._prevStage=this),this._nextStage=t};try{Object.defineProperties(e,{nextStage:{get:e._get_nextStage,set:e._set_nextStage}})}catch(i){}e.update=function(t){if(this.canvas&&(this.tickOnUpdate&&this.tick(t),!this.dispatchEvent("drawstart"))){createjs.DisplayObject._snapToPixelEnabled=this.snapToPixelEnabled;var e=this.drawRect,i=this.canvas.getContext("2d");i.setTransform(1,0,0,1,0,0),this.autoClear&&(e?i.clearRect(e.x,e.y,e.width,e.height):i.clearRect(0,0,this.canvas.width+1,this.canvas.height+1)),i.save(),this.drawRect&&(i.beginPath(),i.rect(e.x,e.y,e.width,e.height),i.clip()),this.updateContext(i),this.draw(i,!1),i.restore(),this.dispatchEvent("drawend")}},e.tick=function(t){if(this.tickEnabled&&!this.dispatchEvent("tickstart")){var e=new createjs.Event("tick");if(t)for(var i in t)t.hasOwnProperty(i)&&(e[i]=t[i]);this._tick(e),this.dispatchEvent("tickend")}},e.handleEvent=function(t){"tick"==t.type&&this.update(t)},e.clear=function(){if(this.canvas){var t=this.canvas.getContext("2d");t.setTransform(1,0,0,1,0,0),t.clearRect(0,0,this.canvas.width+1,this.canvas.height+1)}},e.toDataURL=function(t,e){var i,s=this.canvas.getContext("2d"),r=this.canvas.width,n=this.canvas.height;if(t){i=s.getImageData(0,0,r,n);var a=s.globalCompositeOperation;s.globalCompositeOperation="destination-over",s.fillStyle=t,s.fillRect(0,0,r,n)}var o=this.canvas.toDataURL(e||"image/png");return t&&(s.putImageData(i,0,0),s.globalCompositeOperation=a),o},e.enableMouseOver=function(t){if(this._mouseOverIntervalID&&(clearInterval(this._mouseOverIntervalID),this._mouseOverIntervalID=null,0==t&&this._testMouseOver(!0)),null==t)t=20;else if(0>=t)return;var e=this;this._mouseOverIntervalID=setInterval(function(){e._testMouseOver()},1e3/Math.min(50,t))},e.enableDOMEvents=function(t){null==t&&(t=!0);var e,i,s=this._eventListeners;if(!t&&s){for(e in s)i=s[e],i.t.removeEventListener(e,i.f,!1);this._eventListeners=null}else if(t&&!s&&this.canvas){var r=window.addEventListener?window:document,n=this;s=this._eventListeners={},s.mouseup={t:r,f:function(t){n._handleMouseUp(t)}},s.mousemove={t:r,f:function(t){n._handleMouseMove(t)}},s.dblclick={t:this.canvas,f:function(t){n._handleDoubleClick(t)}},s.mousedown={t:this.canvas,f:function(t){n._handleMouseDown(t)}};for(e in s)i=s[e],i.t.addEventListener(e,i.f,!1)}},e.clone=function(){throw"Stage cannot be cloned."},e.toString=function(){return"[Stage (name="+this.name+")]"},e._getElementRect=function(t){var e;try{e=t.getBoundingClientRect()}catch(i){e={top:t.offsetTop,left:t.offsetLeft,width:t.offsetWidth,height:t.offsetHeight}}var s=(window.pageXOffset||document.scrollLeft||0)-(document.clientLeft||document.body.clientLeft||0),r=(window.pageYOffset||document.scrollTop||0)-(document.clientTop||document.body.clientTop||0),n=window.getComputedStyle?getComputedStyle(t,null):t.currentStyle,a=parseInt(n.paddingLeft)+parseInt(n.borderLeftWidth),o=parseInt(n.paddingTop)+parseInt(n.borderTopWidth),h=parseInt(n.paddingRight)+parseInt(n.borderRightWidth),c=parseInt(n.paddingBottom)+parseInt(n.borderBottomWidth);return{left:e.left+s+a,right:e.right+s-h,top:e.top+r+o,bottom:e.bottom+r-c}},e._getPointerData=function(t){var e=this._pointerData[t];return e||(e=this._pointerData[t]={x:0,y:0}),e},e._handleMouseMove=function(t){t||(t=window.event),this._handlePointerMove(-1,t,t.pageX,t.pageY)},e._handlePointerMove=function(t,e,i,s,r){if((!this._prevStage||void 0!==r)&&this.canvas){var n=this._nextStage,a=this._getPointerData(t),o=a.inBounds;this._updatePointerPosition(t,e,i,s),(o||a.inBounds||this.mouseMoveOutside)&&(-1===t&&a.inBounds==!o&&this._dispatchMouseEvent(this,o?"mouseleave":"mouseenter",!1,t,a,e),this._dispatchMouseEvent(this,"stagemousemove",!1,t,a,e),this._dispatchMouseEvent(a.target,"pressmove",!0,t,a,e)),n&&n._handlePointerMove(t,e,i,s,null)}},e._updatePointerPosition=function(t,e,i,s){var r=this._getElementRect(this.canvas);i-=r.left,s-=r.top;var n=this.canvas.width,a=this.canvas.height;i/=(r.right-r.left)/n,s/=(r.bottom-r.top)/a;var o=this._getPointerData(t);(o.inBounds=i>=0&&s>=0&&n-1>=i&&a-1>=s)?(o.x=i,o.y=s):this.mouseMoveOutside&&(o.x=0>i?0:i>n-1?n-1:i,o.y=0>s?0:s>a-1?a-1:s),o.posEvtObj=e,o.rawX=i,o.rawY=s,(t===this._primaryPointerID||-1===t)&&(this.mouseX=o.x,this.mouseY=o.y,this.mouseInBounds=o.inBounds)},e._handleMouseUp=function(t){this._handlePointerUp(-1,t,!1)},e._handlePointerUp=function(t,e,i,s){var r=this._nextStage,n=this._getPointerData(t);if(!this._prevStage||void 0!==s){n.down&&this._dispatchMouseEvent(this,"stagemouseup",!1,t,n,e),n.down=!1;var a=null,o=n.target;s||!o&&!r||(a=this._getObjectsUnderPoint(n.x,n.y,null,!0)),a==o&&this._dispatchMouseEvent(o,"click",!0,t,n,e),this._dispatchMouseEvent(o,"pressup",!0,t,n,e),i?(t==this._primaryPointerID&&(this._primaryPointerID=null),delete this._pointerData[t]):n.target=null,r&&r._handlePointerUp(t,e,i,s||a&&this)}},e._handleMouseDown=function(t){this._handlePointerDown(-1,t,t.pageX,t.pageY)},e._handlePointerDown=function(t,e,i,s,r){this.preventSelection&&e.preventDefault(),(null==this._primaryPointerID||-1===t)&&(this._primaryPointerID=t),null!=s&&this._updatePointerPosition(t,e,i,s);var n=null,a=this._nextStage,o=this._getPointerData(t);o.inBounds&&(this._dispatchMouseEvent(this,"stagemousedown",!1,t,o,e),o.down=!0),r||(n=o.target=this._getObjectsUnderPoint(o.x,o.y,null,!0),this._dispatchMouseEvent(o.target,"mousedown",!0,t,o,e)),a&&a._handlePointerDown(t,e,i,s,r||n&&this)},e._testMouseOver=function(t,e,i){if(!this._prevStage||void 0!==e){var s=this._nextStage;if(!this._mouseOverIntervalID)return void(s&&s._testMouseOver(t,e,i));var r=this._getPointerData(-1);if(r&&(t||this.mouseX!=this._mouseOverX||this.mouseY!=this._mouseOverY||!this.mouseInBounds)){var n,a,o,h=r.posEvtObj,c=i||h&&h.target==this.canvas,u=null,l=-1,d="";!e&&(t||this.mouseInBounds&&c)&&(u=this._getObjectsUnderPoint(this.mouseX,this.mouseY,null,!0),this._mouseOverX=this.mouseX,this._mouseOverY=this.mouseY);var _=this._mouseOverTarget||[],p=_[_.length-1],f=this._mouseOverTarget=[];for(n=u;n;)f.unshift(n),null!=n.cursor&&(d=n.cursor),n=n.parent;for(this.canvas.style.cursor=d,!e&&i&&(i.canvas.style.cursor=d),a=0,o=f.length;o>a&&f[a]==_[a];a++)l=a;for(p!=u&&this._dispatchMouseEvent(p,"mouseout",!0,-1,r,h),a=_.length-1;a>l;a--)this._dispatchMouseEvent(_[a],"rollout",!1,-1,r,h);for(a=f.length-1;a>l;a--)this._dispatchMouseEvent(f[a],"rollover",!1,-1,r,h);p!=u&&this._dispatchMouseEvent(u,"mouseover",!0,-1,r,h),s&&s._testMouseOver(t,e||u&&this,i||c&&this)}}},e._handleDoubleClick=function(t,e){var i=null,s=this._nextStage,r=this._getPointerData(-1);e||(i=this._getObjectsUnderPoint(r.x,r.y,null,!0),this._dispatchMouseEvent(i,"dblclick",!0,-1,r,t)),s&&s._handleDoubleClick(t,e||i&&this)},e._dispatchMouseEvent=function(t,e,i,s,r,n){if(t&&(i||t.hasEventListener(e))){var a=new createjs.MouseEvent(e,i,!1,r.x,r.y,n,s,s===this._primaryPointerID||-1===s,r.rawX,r.rawY);t.dispatchEvent(a)}},createjs.Stage=createjs.promote(t,"Container")}(),this.createjs=this.createjs||{},function(){function t(t){this.DisplayObject_constructor(),"string"==typeof t?(this.image=document.createElement("img"),this.image.src=t):this.image=t,this.sourceRect=null}var e=createjs.extend(t,createjs.DisplayObject);e.initialize=t,e.isVisible=function(){var t=this.cacheCanvas||this.image&&(this.image.complete||this.image.getContext||this.image.readyState>=2);return!!(this.visible&&this.alpha>0&&0!=this.scaleX&&0!=this.scaleY&&t)},e.draw=function(t,e){if(this.DisplayObject_draw(t,e)||!this.image)return!0;var i=this.image,s=this.sourceRect;if(s){var r=s.x,n=s.y,a=r+s.width,o=n+s.height,h=0,c=0,u=i.width,l=i.height;0>r&&(h-=r,r=0),a>u&&(a=u),0>n&&(c-=n,n=0),o>l&&(o=l),t.drawImage(i,r,n,a-r,o-n,h,c,a-r,o-n)}else t.drawImage(i,0,0);return!0},e.getBounds=function(){var t=this.DisplayObject_getBounds();if(t)return t;var e=this.sourceRect||this.image,i=this.image&&(this.image.complete||this.image.getContext||this.image.readyState>=2);return i?this._rectangle.setValues(0,0,e.width,e.height):null},e.clone=function(){var e=new t(this.image);return this.sourceRect&&(e.sourceRect=this.sourceRect.clone()),this._cloneProps(e),e},e.toString=function(){return"[Bitmap (name="+this.name+")]"},createjs.Bitmap=createjs.promote(t,"DisplayObject")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e){this.DisplayObject_constructor(),this.currentFrame=0,this.currentAnimation=null,this.paused=!0,this.spriteSheet=t,this.currentAnimationFrame=0,this.framerate=0,this._animation=null,this._currentFrame=null,this._skipAdvance=!1,e&&this.gotoAndPlay(e)}var e=createjs.extend(t,createjs.DisplayObject);e.isVisible=function(){var t=this.cacheCanvas||this.spriteSheet.complete;return!!(this.visible&&this.alpha>0&&0!=this.scaleX&&0!=this.scaleY&&t)},e.draw=function(t,e){if(this.DisplayObject_draw(t,e))return!0;this._normalizeFrame();var i=this.spriteSheet.getFrame(0|this._currentFrame);if(!i)return!1;var s=i.rect;return s.width&&s.height&&t.drawImage(i.image,s.x,s.y,s.width,s.height,-i.regX,-i.regY,s.width,s.height),!0},e.play=function(){this.paused=!1},e.stop=function(){this.paused=!0},e.gotoAndPlay=function(t){this.paused=!1,this._skipAdvance=!0,this._goto(t)},e.gotoAndStop=function(t){this.paused=!0,this._goto(t)},e.advance=function(t){var e=this.framerate||this.spriteSheet.framerate,i=e&&null!=t?t/(1e3/e):1;this._normalizeFrame(i)},e.getBounds=function(){return this.DisplayObject_getBounds()||this.spriteSheet.getFrameBounds(this.currentFrame,this._rectangle)},e.clone=function(){return this._cloneProps(new t(this.spriteSheet))},e.toString=function(){return"[Sprite (name="+this.name+")]"},e._cloneProps=function(t){return this.DisplayObject__cloneProps(t),t.currentFrame=this.currentFrame,t.currentAnimation=this.currentAnimation,t.paused=this.paused,t.currentAnimationFrame=this.currentAnimationFrame,t.framerate=this.framerate,t._animation=this._animation,t._currentFrame=this._currentFrame,t._skipAdvance=this._skipAdvance,t},e._tick=function(t){this.paused||(this._skipAdvance||this.advance(t&&t.delta),this._skipAdvance=!1),this.DisplayObject__tick(t)},e._normalizeFrame=function(t){t=t||0;var e,i=this._animation,s=this.paused,r=this._currentFrame;if(i){var n=i.speed||1,a=this.currentAnimationFrame;if(e=i.frames.length,a+t*n>=e){var o=i.next;if(this._dispatchAnimationEnd(i,r,s,o,e-1))return;if(o)return this._goto(o,t-(e-a)/n);this.paused=!0,a=i.frames.length-1}else a+=t*n;this.currentAnimationFrame=a,this._currentFrame=i.frames[0|a]}else if(r=this._currentFrame+=t,e=this.spriteSheet.getNumFrames(),r>=e&&e>0&&!this._dispatchAnimationEnd(i,r,s,e-1)&&(this._currentFrame-=e)>=e)return this._normalizeFrame();r=0|this._currentFrame,this.currentFrame!=r&&(this.currentFrame=r,this.dispatchEvent("change"))},e._dispatchAnimationEnd=function(t,e,i,s,r){var n=t?t.name:null;if(this.hasEventListener("animationend")){var a=new createjs.Event("animationend");a.name=n,a.next=s,this.dispatchEvent(a)}var o=this._animation!=t||this._currentFrame!=e;return o||i||!this.paused||(this.currentAnimationFrame=r,o=!0),o},e._goto=function(t,e){if(this.currentAnimationFrame=0,isNaN(t)){var i=this.spriteSheet.getAnimation(t);i&&(this._animation=i,this.currentAnimation=t,this._normalizeFrame(e))}else this.currentAnimation=this._animation=null,this._currentFrame=t,this._normalizeFrame()},createjs.Sprite=createjs.promote(t,"DisplayObject")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t){this.DisplayObject_constructor(),this.graphics=t?t:new createjs.Graphics}var e=createjs.extend(t,createjs.DisplayObject);e.isVisible=function(){var t=this.cacheCanvas||this.graphics&&!this.graphics.isEmpty();return!!(this.visible&&this.alpha>0&&0!=this.scaleX&&0!=this.scaleY&&t)},e.draw=function(t,e){return this.DisplayObject_draw(t,e)?!0:(this.graphics.draw(t,this),!0)},e.clone=function(e){var i=e&&this.graphics?this.graphics.clone():this.graphics;return this._cloneProps(new t(i))},e.toString=function(){return"[Shape (name="+this.name+")]"},createjs.Shape=createjs.promote(t,"DisplayObject")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e,i){this.DisplayObject_constructor(),this.text=t,this.font=e,this.color=i,this.textAlign="left",this.textBaseline="top",this.maxWidth=null,this.outline=0,this.lineHeight=0,this.lineWidth=null}var e=createjs.extend(t,createjs.DisplayObject),i=createjs.createCanvas?createjs.createCanvas():document.createElement("canvas");i.getContext&&(t._workingContext=i.getContext("2d"),i.width=i.height=1),t.H_OFFSETS={start:0,left:0,center:-.5,end:-1,right:-1},t.V_OFFSETS={top:0,hanging:-.01,middle:-.4,alphabetic:-.8,ideographic:-.85,bottom:-1},e.isVisible=function(){var t=this.cacheCanvas||null!=this.text&&""!==this.text;return!!(this.visible&&this.alpha>0&&0!=this.scaleX&&0!=this.scaleY&&t)},e.draw=function(t,e){if(this.DisplayObject_draw(t,e))return!0;var i=this.color||"#000";return this.outline?(t.strokeStyle=i,t.lineWidth=1*this.outline):t.fillStyle=i,this._drawText(this._prepContext(t)),!0},e.getMeasuredWidth=function(){return this._getMeasuredWidth(this.text)},e.getMeasuredLineHeight=function(){return 1.2*this._getMeasuredWidth("M")},e.getMeasuredHeight=function(){return this._drawText(null,{}).height},e.getBounds=function(){var e=this.DisplayObject_getBounds();if(e)return e;if(null==this.text||""==this.text)return null;var i=this._drawText(null,{}),s=this.maxWidth&&this.maxWidth<i.width?this.maxWidth:i.width,r=s*t.H_OFFSETS[this.textAlign||"left"],n=this.lineHeight||this.getMeasuredLineHeight(),a=n*t.V_OFFSETS[this.textBaseline||"top"];return this._rectangle.setValues(r,a,s,i.height)},e.getMetrics=function(){var e={lines:[]};return e.lineHeight=this.lineHeight||this.getMeasuredLineHeight(),e.vOffset=e.lineHeight*t.V_OFFSETS[this.textBaseline||"top"],this._drawText(null,e,e.lines)},e.clone=function(){return this._cloneProps(new t(this.text,this.font,this.color))},e.toString=function(){return"[Text (text="+(this.text.length>20?this.text.substr(0,17)+"...":this.text)+")]"},e._cloneProps=function(t){return this.DisplayObject__cloneProps(t),t.textAlign=this.textAlign,t.textBaseline=this.textBaseline,t.maxWidth=this.maxWidth,t.outline=this.outline,t.lineHeight=this.lineHeight,t.lineWidth=this.lineWidth,t},e._prepContext=function(t){return t.font=this.font||"10px sans-serif",t.textAlign=this.textAlign||"left",t.textBaseline=this.textBaseline||"top",t},e._drawText=function(e,i,s){var r=!!e;r||(e=t._workingContext,e.save(),this._prepContext(e));for(var n=this.lineHeight||this.getMeasuredLineHeight(),a=0,o=0,h=String(this.text).split(/(?:\r\n|\r|\n)/),c=0,u=h.length;u>c;c++){var l=h[c],d=null;if(null!=this.lineWidth&&(d=e.measureText(l).width)>this.lineWidth){var _=l.split(/(\s)/);l=_[0],d=e.measureText(l).width;for(var p=1,f=_.length;f>p;p+=2){var g=e.measureText(_[p]+_[p+1]).width;d+g>this.lineWidth?(r&&this._drawTextLine(e,l,o*n),s&&s.push(l),d>a&&(a=d),l=_[p+1],d=e.measureText(l).width,o++):(l+=_[p]+_[p+1],d+=g)}}r&&this._drawTextLine(e,l,o*n),s&&s.push(l),i&&null==d&&(d=e.measureText(l).width),d>a&&(a=d),o++}return i&&(i.width=a,i.height=o*n),r||e.restore(),i},e._drawTextLine=function(t,e,i){this.outline?t.strokeText(e,0,i,this.maxWidth||65535):t.fillText(e,0,i,this.maxWidth||65535)},e._getMeasuredWidth=function(e){var i=t._workingContext;i.save();var s=this._prepContext(i).measureText(e).width;return i.restore(),s},createjs.Text=createjs.promote(t,"DisplayObject")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e){this.Container_constructor(),this.text=t||"",this.spriteSheet=e,this.lineHeight=0,this.letterSpacing=0,this.spaceWidth=0,this._oldProps={text:0,spriteSheet:0,lineHeight:0,letterSpacing:0,spaceWidth:0}}var e=createjs.extend(t,createjs.Container);t.maxPoolSize=100,t._spritePool=[],e.draw=function(t,e){this.DisplayObject_draw(t,e)||(this._updateText(),this.Container_draw(t,e))},e.getBounds=function(){return this._updateText(),this.Container_getBounds()},e.isVisible=function(){var t=this.cacheCanvas||this.spriteSheet&&this.spriteSheet.complete&&this.text;return!!(this.visible&&this.alpha>0&&0!==this.scaleX&&0!==this.scaleY&&t)},e.clone=function(){return this._cloneProps(new t(this.text,this.spriteSheet))},e.addChild=e.addChildAt=e.removeChild=e.removeChildAt=e.removeAllChildren=function(){},e._cloneProps=function(t){return this.DisplayObject__cloneProps(t),t.lineHeight=this.lineHeight,t.letterSpacing=this.letterSpacing,t.spaceWidth=this.spaceWidth,t},e._getFrameIndex=function(t,e){var i,s=e.getAnimation(t);return s||(t!=(i=t.toUpperCase())||t!=(i=t.toLowerCase())||(i=null),i&&(s=e.getAnimation(i))),s&&s.frames[0]},e._getFrame=function(t,e){var i=this._getFrameIndex(t,e);return null==i?i:e.getFrame(i)},e._getLineHeight=function(t){var e=this._getFrame("1",t)||this._getFrame("T",t)||this._getFrame("L",t)||t.getFrame(0);return e?e.rect.height:1},e._getSpaceWidth=function(t){var e=this._getFrame("1",t)||this._getFrame("l",t)||this._getFrame("e",t)||this._getFrame("a",t)||t.getFrame(0);return e?e.rect.width:1},e._updateText=function(){var e,i=0,s=0,r=this._oldProps,n=!1,a=this.spaceWidth,o=this.lineHeight,h=this.spriteSheet,c=t._spritePool,u=this.children,l=0,d=u.length;for(var _ in r)r[_]!=this[_]&&(r[_]=this[_],n=!0);if(n){var p=!!this._getFrame(" ",h);p||a||(a=this._getSpaceWidth(h)),o||(o=this._getLineHeight(h));for(var f=0,g=this.text.length;g>f;f++){var m=this.text.charAt(f);if(" "!=m||p)if("\n"!=m&&"\r"!=m){var v=this._getFrameIndex(m,h);null!=v&&(d>l?e=u[l]:(u.push(e=c.length?c.pop():new createjs.Sprite),e.parent=this,d++),e.spriteSheet=h,e.gotoAndStop(v),e.x=i,e.y=s,l++,i+=e.getBounds().width+this.letterSpacing)}else"\r"==m&&"\n"==this.text.charAt(f+1)&&f++,i=0,s+=o;else i+=a}for(;d>l;)c.push(e=u.pop()),e.parent=null,d--;c.length>t.maxPoolSize&&(c.length=t.maxPoolSize)}},createjs.BitmapText=createjs.promote(t,"Container")}(),this.createjs=this.createjs||{},function(){"use strict";function t(){throw"SpriteSheetUtils cannot be instantiated"}var e=createjs.createCanvas?createjs.createCanvas():document.createElement("canvas");e.getContext&&(t._workingCanvas=e,t._workingContext=e.getContext("2d"),e.width=e.height=1),t.addFlippedFrames=function(e,i,s,r){if(i||s||r){var n=0;i&&t._flip(e,++n,!0,!1),s&&t._flip(e,++n,!1,!0),r&&t._flip(e,++n,!0,!0)}},t.extractFrame=function(e,i){isNaN(i)&&(i=e.getAnimation(i).frames[0]);var s=e.getFrame(i);if(!s)return null;var r=s.rect,n=t._workingCanvas;n.width=r.width,n.height=r.height,t._workingContext.drawImage(s.image,r.x,r.y,r.width,r.height,0,0,r.width,r.height);var a=document.createElement("img");return a.src=n.toDataURL("image/png"),a},t.mergeAlpha=function(t,e,i){i||(i=createjs.createCanvas?createjs.createCanvas():document.createElement("canvas")),i.width=Math.max(e.width,t.width),i.height=Math.max(e.height,t.height);var s=i.getContext("2d");return s.save(),s.drawImage(t,0,0),s.globalCompositeOperation="destination-in",s.drawImage(e,0,0),s.restore(),i},t._flip=function(e,i,s,r){for(var n=e._images,a=t._workingCanvas,o=t._workingContext,h=n.length/i,c=0;h>c;c++){var u=n[c];u.__tmp=c,o.setTransform(1,0,0,1,0,0),o.clearRect(0,0,a.width+1,a.height+1),a.width=u.width,a.height=u.height,o.setTransform(s?-1:1,0,0,r?-1:1,s?u.width:0,r?u.height:0),o.drawImage(u,0,0);var l=document.createElement("img");l.src=a.toDataURL("image/png"),l.width=u.width,l.height=u.height,n.push(l)}var d=e._frames,_=d.length/i;for(c=0;_>c;c++){u=d[c];var p=u.rect.clone();l=n[u.image.__tmp+h*i];var f={image:l,rect:p,regX:u.regX,regY:u.regY};s&&(p.x=l.width-p.x-p.width,f.regX=p.width-u.regX),r&&(p.y=l.height-p.y-p.height,f.regY=p.height-u.regY),d.push(f)}var g="_"+(s?"h":"")+(r?"v":""),m=e._animations,v=e._data,E=m.length/i;for(c=0;E>c;c++){var b=m[c];u=v[b];var y={name:b+g,speed:u.speed,next:u.next,frames:[]};u.next&&(y.next+=g),d=u.frames;for(var S=0,j=d.length;j>S;S++)y.frames.push(d[S]+_*i);v[y.name]=y,m.push(y.name)}},createjs.SpriteSheetUtils=t}(),this.createjs=this.createjs||{},function(){"use strict";function t(){this.EventDispatcher_constructor(),
this.maxWidth=2048,this.maxHeight=2048,this.spriteSheet=null,this.scale=1,this.padding=1,this.timeSlice=.3,this.progress=-1,this._frames=[],this._animations={},this._data=null,this._nextFrameIndex=0,this._index=0,this._timerID=null,this._scale=1}var e=createjs.extend(t,createjs.EventDispatcher);t.ERR_DIMENSIONS="frame dimensions exceed max spritesheet dimensions",t.ERR_RUNNING="a build is already running",e.addFrame=function(e,i,s,r,n){if(this._data)throw t.ERR_RUNNING;var a=i||e.bounds||e.nominalBounds;return!a&&e.getBounds&&(a=e.getBounds()),a?(s=s||1,this._frames.push({source:e,sourceRect:a,scale:s,funct:r,data:n,index:this._frames.length,height:a.height*s})-1):null},e.addAnimation=function(e,i,s,r){if(this._data)throw t.ERR_RUNNING;this._animations[e]={frames:i,next:s,frequency:r}},e.addMovieClip=function(e,i,s,r,n,a){if(this._data)throw t.ERR_RUNNING;var o=e.frameBounds,h=i||e.bounds||e.nominalBounds;if(!h&&e.getBounds&&(h=e.getBounds()),h||o){var c,u,l=this._frames.length,d=e.timeline.duration;for(c=0;d>c;c++){var _=o&&o[c]?o[c]:h;this.addFrame(e,_,s,this._setupMovieClipFrame,{i:c,f:r,d:n})}var p=e.timeline._labels,f=[];for(var g in p)f.push({index:p[g],label:g});if(f.length)for(f.sort(function(t,e){return t.index-e.index}),c=0,u=f.length;u>c;c++){for(var m=f[c].label,v=l+f[c].index,E=l+(c==u-1?d:f[c+1].index),b=[],y=v;E>y;y++)b.push(y);(!a||(m=a(m,e,v,E)))&&this.addAnimation(m,b,!0)}}},e.build=function(){if(this._data)throw t.ERR_RUNNING;for(this._startBuild();this._drawNext(););return this._endBuild(),this.spriteSheet},e.buildAsync=function(e){if(this._data)throw t.ERR_RUNNING;this.timeSlice=e,this._startBuild();var i=this;this._timerID=setTimeout(function(){i._run()},50-50*Math.max(.01,Math.min(.99,this.timeSlice||.3)))},e.stopAsync=function(){clearTimeout(this._timerID),this._data=null},e.clone=function(){throw"SpriteSheetBuilder cannot be cloned."},e.toString=function(){return"[SpriteSheetBuilder]"},e._startBuild=function(){var e=this.padding||0;this.progress=0,this.spriteSheet=null,this._index=0,this._scale=this.scale;var i=[];this._data={images:[],frames:i,animations:this._animations};var s=this._frames.slice();if(s.sort(function(t,e){return t.height<=e.height?-1:1}),s[s.length-1].height+2*e>this.maxHeight)throw t.ERR_DIMENSIONS;for(var r=0,n=0,a=0;s.length;){var o=this._fillRow(s,r,a,i,e);if(o.w>n&&(n=o.w),r+=o.h,!o.h||!s.length){var h=createjs.createCanvas?createjs.createCanvas():document.createElement("canvas");h.width=this._getSize(n,this.maxWidth),h.height=this._getSize(r,this.maxHeight),this._data.images[a]=h,o.h||(n=r=0,a++)}}},e._setupMovieClipFrame=function(t,e){var i=t.actionsEnabled;t.actionsEnabled=!1,t.gotoAndStop(e.i),t.actionsEnabled=i,e.f&&e.f(t,e.d,e.i)},e._getSize=function(t,e){for(var i=4;Math.pow(2,++i)<t;);return Math.min(e,Math.pow(2,i))},e._fillRow=function(e,i,s,r,n){var a=this.maxWidth,o=this.maxHeight;i+=n;for(var h=o-i,c=n,u=0,l=e.length-1;l>=0;l--){var d=e[l],_=this._scale*d.scale,p=d.sourceRect,f=d.source,g=Math.floor(_*p.x-n),m=Math.floor(_*p.y-n),v=Math.ceil(_*p.height+2*n),E=Math.ceil(_*p.width+2*n);if(E>a)throw t.ERR_DIMENSIONS;v>h||c+E>a||(d.img=s,d.rect=new createjs.Rectangle(c,i,E,v),u=u||v,e.splice(l,1),r[d.index]=[c,i,E,v,s,Math.round(-g+_*f.regX-n),Math.round(-m+_*f.regY-n)],c+=E)}return{w:c,h:u}},e._endBuild=function(){this.spriteSheet=new createjs.SpriteSheet(this._data),this._data=null,this.progress=1,this.dispatchEvent("complete")},e._run=function(){for(var t=50*Math.max(.01,Math.min(.99,this.timeSlice||.3)),e=(new Date).getTime()+t,i=!1;e>(new Date).getTime();)if(!this._drawNext()){i=!0;break}if(i)this._endBuild();else{var s=this;this._timerID=setTimeout(function(){s._run()},50-t)}var r=this.progress=this._index/this._frames.length;if(this.hasEventListener("progress")){var n=new createjs.Event("progress");n.progress=r,this.dispatchEvent(n)}},e._drawNext=function(){var t=this._frames[this._index],e=t.scale*this._scale,i=t.rect,s=t.sourceRect,r=this._data.images[t.img],n=r.getContext("2d");return t.funct&&t.funct(t.source,t.data),n.save(),n.beginPath(),n.rect(i.x,i.y,i.width,i.height),n.clip(),n.translate(Math.ceil(i.x-s.x*e),Math.ceil(i.y-s.y*e)),n.scale(e,e),t.source.draw(n),n.restore(),++this._index<this._frames.length},createjs.SpriteSheetBuilder=createjs.promote(t,"EventDispatcher")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t){this.DisplayObject_constructor(),"string"==typeof t&&(t=document.getElementById(t)),this.mouseEnabled=!1;var e=t.style;e.position="absolute",e.transformOrigin=e.WebkitTransformOrigin=e.msTransformOrigin=e.MozTransformOrigin=e.OTransformOrigin="0% 0%",this.htmlElement=t,this._oldProps=null}var e=createjs.extend(t,createjs.DisplayObject);e.isVisible=function(){return null!=this.htmlElement},e.draw=function(){return!0},e.cache=function(){},e.uncache=function(){},e.updateCache=function(){},e.hitTest=function(){},e.localToGlobal=function(){},e.globalToLocal=function(){},e.localToLocal=function(){},e.clone=function(){throw"DOMElement cannot be cloned."},e.toString=function(){return"[DOMElement (name="+this.name+")]"},e._tick=function(t){var e=this.getStage();e&&e.on("drawend",this._handleDrawEnd,this,!0),this.DisplayObject__tick(t)},e._handleDrawEnd=function(){var t=this.htmlElement;if(t){var e=t.style,i=this.getConcatenatedDisplayProps(this._props),s=i.matrix,r=i.visible?"visible":"hidden";if(r!=e.visibility&&(e.visibility=r),i.visible){var n=this._oldProps,a=n&&n.matrix,o=1e4;if(!a||!a.equals(s)){var h="matrix("+(s.a*o|0)/o+","+(s.b*o|0)/o+","+(s.c*o|0)/o+","+(s.d*o|0)/o+","+(s.tx+.5|0);e.transform=e.WebkitTransform=e.OTransform=e.msTransform=h+","+(s.ty+.5|0)+")",e.MozTransform=h+"px,"+(s.ty+.5|0)+"px)",n||(n=this._oldProps=new createjs.DisplayProps(!0,0/0)),n.matrix.copy(s)}n.alpha!=i.alpha&&(e.opacity=""+(i.alpha*o|0)/o,n.alpha=i.alpha)}}},createjs.DOMElement=createjs.promote(t,"DisplayObject")}(),this.createjs=this.createjs||{},function(){"use strict";function t(){}var e=t.prototype;e.getBounds=function(t){return t},e.applyFilter=function(t,e,i,s,r,n,a,o){n=n||t,null==a&&(a=e),null==o&&(o=i);try{var h=t.getImageData(e,i,s,r)}catch(c){return!1}return this._applyFilter(h)?(n.putImageData(h,a,o),!0):!1},e.toString=function(){return"[Filter]"},e.clone=function(){return new t},e._applyFilter=function(){return!0},createjs.Filter=t}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e,i){(isNaN(t)||0>t)&&(t=0),(isNaN(e)||0>e)&&(e=0),(isNaN(i)||1>i)&&(i=1),this.blurX=0|t,this.blurY=0|e,this.quality=0|i}var e=createjs.extend(t,createjs.Filter);t.MUL_TABLE=[1,171,205,293,57,373,79,137,241,27,391,357,41,19,283,265,497,469,443,421,25,191,365,349,335,161,155,149,9,278,269,261,505,245,475,231,449,437,213,415,405,395,193,377,369,361,353,345,169,331,325,319,313,307,301,37,145,285,281,69,271,267,263,259,509,501,493,243,479,118,465,459,113,446,55,435,429,423,209,413,51,403,199,393,97,3,379,375,371,367,363,359,355,351,347,43,85,337,333,165,327,323,5,317,157,311,77,305,303,75,297,294,73,289,287,71,141,279,277,275,68,135,67,133,33,262,260,129,511,507,503,499,495,491,61,121,481,477,237,235,467,232,115,457,227,451,7,445,221,439,218,433,215,427,425,211,419,417,207,411,409,203,202,401,399,396,197,49,389,387,385,383,95,189,47,187,93,185,23,183,91,181,45,179,89,177,11,175,87,173,345,343,341,339,337,21,167,83,331,329,327,163,81,323,321,319,159,79,315,313,39,155,309,307,153,305,303,151,75,299,149,37,295,147,73,291,145,289,287,143,285,71,141,281,35,279,139,69,275,137,273,17,271,135,269,267,133,265,33,263,131,261,130,259,129,257,1],t.SHG_TABLE=[0,9,10,11,9,12,10,11,12,9,13,13,10,9,13,13,14,14,14,14,10,13,14,14,14,13,13,13,9,14,14,14,15,14,15,14,15,15,14,15,15,15,14,15,15,15,15,15,14,15,15,15,15,15,15,12,14,15,15,13,15,15,15,15,16,16,16,15,16,14,16,16,14,16,13,16,16,16,15,16,13,16,15,16,14,9,16,16,16,16,16,16,16,16,16,13,14,16,16,15,16,16,10,16,15,16,14,16,16,14,16,16,14,16,16,14,15,16,16,16,14,15,14,15,13,16,16,15,17,17,17,17,17,17,14,15,17,17,16,16,17,16,15,17,16,17,11,17,16,17,16,17,16,17,17,16,17,17,16,17,17,16,16,17,17,17,16,14,17,17,17,17,15,16,14,16,15,16,13,16,15,16,14,16,15,16,12,16,15,16,17,17,17,17,17,13,16,15,17,17,17,16,15,17,17,17,16,15,17,17,14,16,17,17,16,17,17,16,15,17,16,14,17,16,15,17,16,17,17,16,17,15,16,17,14,17,16,15,17,16,17,13,17,16,17,17,16,17,14,17,16,17,16,17,16,17,9],e.getBounds=function(t){var e=0|this.blurX,i=0|this.blurY;if(0>=e&&0>=i)return t;var s=Math.pow(this.quality,.2);return(t||new createjs.Rectangle).pad(e*s+1,i*s+1,e*s+1,i*s+1)},e.clone=function(){return new t(this.blurX,this.blurY,this.quality)},e.toString=function(){return"[BlurFilter]"},e._applyFilter=function(e){var i=this.blurX>>1;if(isNaN(i)||0>i)return!1;var s=this.blurY>>1;if(isNaN(s)||0>s)return!1;if(0==i&&0==s)return!1;var r=this.quality;(isNaN(r)||1>r)&&(r=1),r|=0,r>3&&(r=3),1>r&&(r=1);var n=e.data,a=0,o=0,h=0,c=0,u=0,l=0,d=0,_=0,p=0,f=0,g=0,m=0,v=0,E=0,b=0,y=i+i+1|0,S=s+s+1|0,j=0|e.width,T=0|e.height,x=j-1|0,w=T-1|0,P=i+1|0,L=s+1|0,A={r:0,b:0,g:0,a:0},R=A;for(h=1;y>h;h++)R=R.n={r:0,b:0,g:0,a:0};R.n=A;var I={r:0,b:0,g:0,a:0},M=I;for(h=1;S>h;h++)M=M.n={r:0,b:0,g:0,a:0};M.n=I;for(var O=null,C=0|t.MUL_TABLE[i],D=0|t.SHG_TABLE[i],N=0|t.MUL_TABLE[s],k=0|t.SHG_TABLE[s];r-->0;){d=l=0;var F=C,B=D;for(o=T;--o>-1;){for(_=P*(m=n[0|l]),p=P*(v=n[l+1|0]),f=P*(E=n[l+2|0]),g=P*(b=n[l+3|0]),R=A,h=P;--h>-1;)R.r=m,R.g=v,R.b=E,R.a=b,R=R.n;for(h=1;P>h;h++)c=l+((h>x?x:h)<<2)|0,_+=R.r=n[c],p+=R.g=n[c+1],f+=R.b=n[c+2],g+=R.a=n[c+3],R=R.n;for(O=A,a=0;j>a;a++)n[l++]=_*F>>>B,n[l++]=p*F>>>B,n[l++]=f*F>>>B,n[l++]=g*F>>>B,c=d+((c=a+i+1)<x?c:x)<<2,_-=O.r-(O.r=n[c]),p-=O.g-(O.g=n[c+1]),f-=O.b-(O.b=n[c+2]),g-=O.a-(O.a=n[c+3]),O=O.n;d+=j}for(F=N,B=k,a=0;j>a;a++){for(l=a<<2|0,_=L*(m=n[l])|0,p=L*(v=n[l+1|0])|0,f=L*(E=n[l+2|0])|0,g=L*(b=n[l+3|0])|0,M=I,h=0;L>h;h++)M.r=m,M.g=v,M.b=E,M.a=b,M=M.n;for(u=j,h=1;s>=h;h++)l=u+a<<2,_+=M.r=n[l],p+=M.g=n[l+1],f+=M.b=n[l+2],g+=M.a=n[l+3],M=M.n,w>h&&(u+=j);if(l=a,O=I,r>0)for(o=0;T>o;o++)c=l<<2,n[c+3]=b=g*F>>>B,b>0?(n[c]=_*F>>>B,n[c+1]=p*F>>>B,n[c+2]=f*F>>>B):n[c]=n[c+1]=n[c+2]=0,c=a+((c=o+L)<w?c:w)*j<<2,_-=O.r-(O.r=n[c]),p-=O.g-(O.g=n[c+1]),f-=O.b-(O.b=n[c+2]),g-=O.a-(O.a=n[c+3]),O=O.n,l+=j;else for(o=0;T>o;o++)c=l<<2,n[c+3]=b=g*F>>>B,b>0?(b=255/b,n[c]=(_*F>>>B)*b,n[c+1]=(p*F>>>B)*b,n[c+2]=(f*F>>>B)*b):n[c]=n[c+1]=n[c+2]=0,c=a+((c=o+L)<w?c:w)*j<<2,_-=O.r-(O.r=n[c]),p-=O.g-(O.g=n[c+1]),f-=O.b-(O.b=n[c+2]),g-=O.a-(O.a=n[c+3]),O=O.n,l+=j}}return!0},createjs.BlurFilter=createjs.promote(t,"Filter")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t){this.alphaMap=t,this._alphaMap=null,this._mapData=null}var e=createjs.extend(t,createjs.Filter);e.clone=function(){var e=new t(this.alphaMap);return e._alphaMap=this._alphaMap,e._mapData=this._mapData,e},e.toString=function(){return"[AlphaMapFilter]"},e._applyFilter=function(t){if(!this.alphaMap)return!0;if(!this._prepAlphaMap())return!1;for(var e=t.data,i=this._mapData,s=0,r=e.length;r>s;s+=4)e[s+3]=i[s]||0;return!0},e._prepAlphaMap=function(){if(!this.alphaMap)return!1;if(this.alphaMap==this._alphaMap&&this._mapData)return!0;this._mapData=null;var t,e=this._alphaMap=this.alphaMap,i=e;e instanceof HTMLCanvasElement?t=i.getContext("2d"):(i=createjs.createCanvas?createjs.createCanvas():document.createElement("canvas"),i.width=e.width,i.height=e.height,t=i.getContext("2d"),t.drawImage(e,0,0));try{var s=t.getImageData(0,0,e.width,e.height)}catch(r){return!1}return this._mapData=s.data,!0},createjs.AlphaMapFilter=createjs.promote(t,"Filter")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t){this.mask=t}var e=createjs.extend(t,createjs.Filter);e.applyFilter=function(t,e,i,s,r,n,a,o){return this.mask?(n=n||t,null==a&&(a=e),null==o&&(o=i),n.save(),t!=n?!1:(n.globalCompositeOperation="destination-in",n.drawImage(this.mask,a,o),n.restore(),!0)):!0},e.clone=function(){return new t(this.mask)},e.toString=function(){return"[AlphaMaskFilter]"},createjs.AlphaMaskFilter=createjs.promote(t,"Filter")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e,i,s,r,n,a,o){this.redMultiplier=null!=t?t:1,this.greenMultiplier=null!=e?e:1,this.blueMultiplier=null!=i?i:1,this.alphaMultiplier=null!=s?s:1,this.redOffset=r||0,this.greenOffset=n||0,this.blueOffset=a||0,this.alphaOffset=o||0}var e=createjs.extend(t,createjs.Filter);e.toString=function(){return"[ColorFilter]"},e.clone=function(){return new t(this.redMultiplier,this.greenMultiplier,this.blueMultiplier,this.alphaMultiplier,this.redOffset,this.greenOffset,this.blueOffset,this.alphaOffset)},e._applyFilter=function(t){for(var e=t.data,i=e.length,s=0;i>s;s+=4)e[s]=e[s]*this.redMultiplier+this.redOffset,e[s+1]=e[s+1]*this.greenMultiplier+this.greenOffset,e[s+2]=e[s+2]*this.blueMultiplier+this.blueOffset,e[s+3]=e[s+3]*this.alphaMultiplier+this.alphaOffset;return!0},createjs.ColorFilter=createjs.promote(t,"Filter")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e,i,s){this.setColor(t,e,i,s)}var e=t.prototype;t.DELTA_INDEX=[0,.01,.02,.04,.05,.06,.07,.08,.1,.11,.12,.14,.15,.16,.17,.18,.2,.21,.22,.24,.25,.27,.28,.3,.32,.34,.36,.38,.4,.42,.44,.46,.48,.5,.53,.56,.59,.62,.65,.68,.71,.74,.77,.8,.83,.86,.89,.92,.95,.98,1,1.06,1.12,1.18,1.24,1.3,1.36,1.42,1.48,1.54,1.6,1.66,1.72,1.78,1.84,1.9,1.96,2,2.12,2.25,2.37,2.5,2.62,2.75,2.87,3,3.2,3.4,3.6,3.8,4,4.3,4.7,4.9,5,5.5,6,6.5,6.8,7,7.3,7.5,7.8,8,8.4,8.7,9,9.4,9.6,9.8,10],t.IDENTITY_MATRIX=[1,0,0,0,0,0,1,0,0,0,0,0,1,0,0,0,0,0,1,0,0,0,0,0,1],t.LENGTH=t.IDENTITY_MATRIX.length,e.setColor=function(t,e,i,s){return this.reset().adjustColor(t,e,i,s)},e.reset=function(){return this.copy(t.IDENTITY_MATRIX)},e.adjustColor=function(t,e,i,s){return this.adjustHue(s),this.adjustContrast(e),this.adjustBrightness(t),this.adjustSaturation(i)},e.adjustBrightness=function(t){return 0==t||isNaN(t)?this:(t=this._cleanValue(t,255),this._multiplyMatrix([1,0,0,0,t,0,1,0,0,t,0,0,1,0,t,0,0,0,1,0,0,0,0,0,1]),this)},e.adjustContrast=function(e){if(0==e||isNaN(e))return this;e=this._cleanValue(e,100);var i;return 0>e?i=127+e/100*127:(i=e%1,i=0==i?t.DELTA_INDEX[e]:t.DELTA_INDEX[e<<0]*(1-i)+t.DELTA_INDEX[(e<<0)+1]*i,i=127*i+127),this._multiplyMatrix([i/127,0,0,0,.5*(127-i),0,i/127,0,0,.5*(127-i),0,0,i/127,0,.5*(127-i),0,0,0,1,0,0,0,0,0,1]),this},e.adjustSaturation=function(t){if(0==t||isNaN(t))return this;t=this._cleanValue(t,100);var e=1+(t>0?3*t/100:t/100),i=.3086,s=.6094,r=.082;return this._multiplyMatrix([i*(1-e)+e,s*(1-e),r*(1-e),0,0,i*(1-e),s*(1-e)+e,r*(1-e),0,0,i*(1-e),s*(1-e),r*(1-e)+e,0,0,0,0,0,1,0,0,0,0,0,1]),this},e.adjustHue=function(t){if(0==t||isNaN(t))return this;t=this._cleanValue(t,180)/180*Math.PI;var e=Math.cos(t),i=Math.sin(t),s=.213,r=.715,n=.072;return this._multiplyMatrix([s+e*(1-s)+i*-s,r+e*-r+i*-r,n+e*-n+i*(1-n),0,0,s+e*-s+.143*i,r+e*(1-r)+.14*i,n+e*-n+i*-.283,0,0,s+e*-s+i*-(1-s),r+e*-r+i*r,n+e*(1-n)+i*n,0,0,0,0,0,1,0,0,0,0,0,1]),this},e.concat=function(e){return e=this._fixMatrix(e),e.length!=t.LENGTH?this:(this._multiplyMatrix(e),this)},e.clone=function(){return(new t).copy(this)},e.toArray=function(){for(var e=[],i=0,s=t.LENGTH;s>i;i++)e[i]=this[i];return e},e.copy=function(e){for(var i=t.LENGTH,s=0;i>s;s++)this[s]=e[s];return this},e.toString=function(){return"[ColorMatrix]"},e._multiplyMatrix=function(t){var e,i,s,r=[];for(e=0;5>e;e++){for(i=0;5>i;i++)r[i]=this[i+5*e];for(i=0;5>i;i++){var n=0;for(s=0;5>s;s++)n+=t[i+5*s]*r[s];this[i+5*e]=n}}},e._cleanValue=function(t,e){return Math.min(e,Math.max(-e,t))},e._fixMatrix=function(e){return e instanceof t&&(e=e.toArray()),e.length<t.LENGTH?e=e.slice(0,e.length).concat(t.IDENTITY_MATRIX.slice(e.length,t.LENGTH)):e.length>t.LENGTH&&(e=e.slice(0,t.LENGTH)),e},createjs.ColorMatrix=t}(),this.createjs=this.createjs||{},function(){"use strict";function t(t){this.matrix=t}var e=createjs.extend(t,createjs.Filter);e.toString=function(){return"[ColorMatrixFilter]"},e.clone=function(){return new t(this.matrix)},e._applyFilter=function(t){for(var e,i,s,r,n=t.data,a=n.length,o=this.matrix,h=o[0],c=o[1],u=o[2],l=o[3],d=o[4],_=o[5],p=o[6],f=o[7],g=o[8],m=o[9],v=o[10],E=o[11],b=o[12],y=o[13],S=o[14],j=o[15],T=o[16],x=o[17],w=o[18],P=o[19],L=0;a>L;L+=4)e=n[L],i=n[L+1],s=n[L+2],r=n[L+3],n[L]=e*h+i*c+s*u+r*l+d,n[L+1]=e*_+i*p+s*f+r*g+m,n[L+2]=e*v+i*E+s*b+r*y+S,n[L+3]=e*j+i*T+s*x+r*w+P;return!0},createjs.ColorMatrixFilter=createjs.promote(t,"Filter")}(),this.createjs=this.createjs||{},function(){"use strict";function t(){throw"Touch cannot be instantiated"}t.isSupported=function(){return!!("ontouchstart"in window||window.navigator.msPointerEnabled&&window.navigator.msMaxTouchPoints>0||window.navigator.pointerEnabled&&window.navigator.maxTouchPoints>0)},t.enable=function(e,i,s){return e&&e.canvas&&t.isSupported()?e.__touch?!0:(e.__touch={pointers:{},multitouch:!i,preventDefault:!s,count:0},"ontouchstart"in window?t._IOS_enable(e):(window.navigator.msPointerEnabled||window.navigator.pointerEnabled)&&t._IE_enable(e),!0):!1},t.disable=function(e){e&&("ontouchstart"in window?t._IOS_disable(e):(window.navigator.msPointerEnabled||window.navigator.pointerEnabled)&&t._IE_disable(e),delete e.__touch)},t._IOS_enable=function(e){var i=e.canvas,s=e.__touch.f=function(i){t._IOS_handleEvent(e,i)};i.addEventListener("touchstart",s,!1),i.addEventListener("touchmove",s,!1),i.addEventListener("touchend",s,!1),i.addEventListener("touchcancel",s,!1)},t._IOS_disable=function(t){var e=t.canvas;if(e){var i=t.__touch.f;e.removeEventListener("touchstart",i,!1),e.removeEventListener("touchmove",i,!1),e.removeEventListener("touchend",i,!1),e.removeEventListener("touchcancel",i,!1)}},t._IOS_handleEvent=function(t,e){if(t){t.__touch.preventDefault&&e.preventDefault&&e.preventDefault();for(var i=e.changedTouches,s=e.type,r=0,n=i.length;n>r;r++){var a=i[r],o=a.identifier;a.target==t.canvas&&("touchstart"==s?this._handleStart(t,o,e,a.pageX,a.pageY):"touchmove"==s?this._handleMove(t,o,e,a.pageX,a.pageY):("touchend"==s||"touchcancel"==s)&&this._handleEnd(t,o,e))}}},t._IE_enable=function(e){var i=e.canvas,s=e.__touch.f=function(i){t._IE_handleEvent(e,i)};void 0===window.navigator.pointerEnabled?(i.addEventListener("MSPointerDown",s,!1),window.addEventListener("MSPointerMove",s,!1),window.addEventListener("MSPointerUp",s,!1),window.addEventListener("MSPointerCancel",s,!1),e.__touch.preventDefault&&(i.style.msTouchAction="none")):(i.addEventListener("pointerdown",s,!1),window.addEventListener("pointermove",s,!1),window.addEventListener("pointerup",s,!1),window.addEventListener("pointercancel",s,!1),e.__touch.preventDefault&&(i.style.touchAction="none")),e.__touch.activeIDs={}},t._IE_disable=function(t){var e=t.__touch.f;void 0===window.navigator.pointerEnabled?(window.removeEventListener("MSPointerMove",e,!1),window.removeEventListener("MSPointerUp",e,!1),window.removeEventListener("MSPointerCancel",e,!1),t.canvas&&t.canvas.removeEventListener("MSPointerDown",e,!1)):(window.removeEventListener("pointermove",e,!1),window.removeEventListener("pointerup",e,!1),window.removeEventListener("pointercancel",e,!1),t.canvas&&t.canvas.removeEventListener("pointerdown",e,!1))},t._IE_handleEvent=function(t,e){if(t){t.__touch.preventDefault&&e.preventDefault&&e.preventDefault();var i=e.type,s=e.pointerId,r=t.__touch.activeIDs;if("MSPointerDown"==i||"pointerdown"==i){if(e.srcElement!=t.canvas)return;r[s]=!0,this._handleStart(t,s,e,e.pageX,e.pageY)}else r[s]&&("MSPointerMove"==i||"pointermove"==i?this._handleMove(t,s,e,e.pageX,e.pageY):("MSPointerUp"==i||"MSPointerCancel"==i||"pointerup"==i||"pointercancel"==i)&&(delete r[s],this._handleEnd(t,s,e)))}},t._handleStart=function(t,e,i,s,r){var n=t.__touch;if(n.multitouch||!n.count){var a=n.pointers;a[e]||(a[e]=!0,n.count++,t._handlePointerDown(e,i,s,r))}},t._handleMove=function(t,e,i,s,r){t.__touch.pointers[e]&&t._handlePointerMove(e,i,s,r)},t._handleEnd=function(t,e,i){var s=t.__touch,r=s.pointers;r[e]&&(s.count--,t._handlePointerUp(e,i,!0),delete r[e])},createjs.Touch=t}(),this.createjs=this.createjs||{},function(){"use strict";var t=createjs.EaselJS=createjs.EaselJS||{};t.version="0.8.0",t.buildDate="Thu, 11 Dec 2014 23:32:09 GMT"}(),this.createjs=this.createjs||{},function(){"use strict";var t=createjs.PreloadJS=createjs.PreloadJS||{};t.version="0.6.0",t.buildDate="Thu, 11 Dec 2014 23:32:09 GMT"}(),this.createjs=this.createjs||{},function(){"use strict";createjs.proxy=function(t,e){var i=Array.prototype.slice.call(arguments,2);return function(){return t.apply(e,Array.prototype.slice.call(arguments,0).concat(i))}}}(),this.createjs=this.createjs||{},function(){"use strict";function t(){throw"BrowserDetect cannot be instantiated"}var e=t.agent=window.navigator.userAgent;t.isWindowPhone=e.indexOf("IEMobile")>-1||e.indexOf("Windows Phone")>-1,t.isFirefox=e.indexOf("Firefox")>-1,t.isOpera=null!=window.opera,t.isChrome=e.indexOf("Chrome")>-1,t.isIOS=(e.indexOf("iPod")>-1||e.indexOf("iPhone")>-1||e.indexOf("iPad")>-1)&&!t.isWindowPhone,t.isAndroid=e.indexOf("Android")>-1&&!t.isWindowPhone,t.isBlackberry=e.indexOf("Blackberry")>-1,createjs.BrowserDetect=t}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e,i){this.Event_constructor("error"),this.title=t,this.message=e,this.data=i}var e=createjs.extend(t,createjs.Event);e.clone=function(){return new createjs.ErrorEvent(this.title,this.message,this.data)},createjs.ErrorEvent=createjs.promote(t,"Event")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e){this.Event_constructor("progress"),this.loaded=t,this.total=null==e?1:e,this.progress=0==e?0:this.loaded/this.total}var e=createjs.extend(t,createjs.Event);e.clone=function(){return new createjs.ProgressEvent(this.loaded,this.total)},createjs.ProgressEvent=createjs.promote(t,"Event")}(window),function(){function t(e,s){function n(t){if(n[t]!==g)return n[t];var e;if("bug-string-char-index"==t)e="a"!="a"[0];else if("json"==t)e=n("json-stringify")&&n("json-parse");else{var i,r='{"a":[1,true,false,null,"\\u0000\\b\\n\\f\\r\\t"]}';if("json-stringify"==t){var h=s.stringify,u="function"==typeof h&&E;if(u){(i=function(){return 1}).toJSON=i;try{u="0"===h(0)&&"0"===h(new a)&&'""'==h(new o)&&h(v)===g&&h(g)===g&&h()===g&&"1"===h(i)&&"[1]"==h([i])&&"[null]"==h([g])&&"null"==h(null)&&"[null,null,null]"==h([g,v,null])&&h({a:[i,!0,!1,null,"\x00\b\n\f\r	"]})==r&&"1"===h(null,i)&&"[\n 1,\n 2\n]"==h([1,2],null,1)&&'"-271821-04-20T00:00:00.000Z"'==h(new c(-864e13))&&'"+275760-09-13T00:00:00.000Z"'==h(new c(864e13))&&'"-000001-01-01T00:00:00.000Z"'==h(new c(-621987552e5))&&'"1969-12-31T23:59:59.999Z"'==h(new c(-1))}catch(l){u=!1}}e=u}if("json-parse"==t){var d=s.parse;if("function"==typeof d)try{if(0===d("0")&&!d(!1)){i=d(r);var _=5==i.a.length&&1===i.a[0];if(_){try{_=!d('"	"')}catch(l){}if(_)try{_=1!==d("01")}catch(l){}if(_)try{_=1!==d("1.")}catch(l){}}}}catch(l){_=!1}e=_}}return n[t]=!!e}e||(e=r.Object()),s||(s=r.Object());var a=e.Number||r.Number,o=e.String||r.String,h=e.Object||r.Object,c=e.Date||r.Date,u=e.SyntaxError||r.SyntaxError,l=e.TypeError||r.TypeError,d=e.Math||r.Math,_=e.JSON||r.JSON;"object"==typeof _&&_&&(s.stringify=_.stringify,s.parse=_.parse);var p,f,g,m=h.prototype,v=m.toString,E=new c(-0xc782b5b800cec);try{E=-109252==E.getUTCFullYear()&&0===E.getUTCMonth()&&1===E.getUTCDate()&&10==E.getUTCHours()&&37==E.getUTCMinutes()&&6==E.getUTCSeconds()&&708==E.getUTCMilliseconds()}catch(b){}if(!n("json")){var y="[object Function]",S="[object Date]",j="[object Number]",T="[object String]",x="[object Array]",w="[object Boolean]",P=n("bug-string-char-index");if(!E)var L=d.floor,A=[0,31,59,90,120,151,181,212,243,273,304,334],R=function(t,e){return A[e]+365*(t-1970)+L((t-1969+(e=+(e>1)))/4)-L((t-1901+e)/100)+L((t-1601+e)/400)};if((p=m.hasOwnProperty)||(p=function(t){var e,i={};return(i.__proto__=null,i.__proto__={toString:1},i).toString!=v?p=function(t){var e=this.__proto__,i=t in(this.__proto__=null,this);return this.__proto__=e,i}:(e=i.constructor,p=function(t){var i=(this.constructor||e).prototype;return t in this&&!(t in i&&this[t]===i[t])}),i=null,p.call(this,t)}),f=function(t,e){var s,r,n,a=0;(s=function(){this.valueOf=0}).prototype.valueOf=0,r=new s;for(n in r)p.call(r,n)&&a++;return s=r=null,a?f=2==a?function(t,e){var i,s={},r=v.call(t)==y;for(i in t)r&&"prototype"==i||p.call(s,i)||!(s[i]=1)||!p.call(t,i)||e(i)}:function(t,e){var i,s,r=v.call(t)==y;for(i in t)r&&"prototype"==i||!p.call(t,i)||(s="constructor"===i)||e(i);(s||p.call(t,i="constructor"))&&e(i)}:(r=["valueOf","toString","toLocaleString","propertyIsEnumerable","isPrototypeOf","hasOwnProperty","constructor"],f=function(t,e){var s,n,a=v.call(t)==y,o=!a&&"function"!=typeof t.constructor&&i[typeof t.hasOwnProperty]&&t.hasOwnProperty||p;for(s in t)a&&"prototype"==s||!o.call(t,s)||e(s);for(n=r.length;s=r[--n];o.call(t,s)&&e(s));}),f(t,e)},!n("json-stringify")){var I={92:"\\\\",34:'\\"',8:"\\b",12:"\\f",10:"\\n",13:"\\r",9:"\\t"},M="000000",O=function(t,e){return(M+(e||0)).slice(-t)},C="\\u00",D=function(t){for(var e='"',i=0,s=t.length,r=!P||s>10,n=r&&(P?t.split(""):t);s>i;i++){var a=t.charCodeAt(i);switch(a){case 8:case 9:case 10:case 12:case 13:case 34:case 92:e+=I[a];break;default:if(32>a){e+=C+O(2,a.toString(16));break}e+=r?n[i]:t.charAt(i)}}return e+'"'},N=function(t,e,i,s,r,n,a){var o,h,c,u,d,_,m,E,b,y,P,A,I,M,C,k;try{o=e[t]}catch(F){}if("object"==typeof o&&o)if(h=v.call(o),h!=S||p.call(o,"toJSON"))"function"==typeof o.toJSON&&(h!=j&&h!=T&&h!=x||p.call(o,"toJSON"))&&(o=o.toJSON(t));else if(o>-1/0&&1/0>o){if(R){for(d=L(o/864e5),c=L(d/365.2425)+1970-1;R(c+1,0)<=d;c++);for(u=L((d-R(c,0))/30.42);R(c,u+1)<=d;u++);d=1+d-R(c,u),_=(o%864e5+864e5)%864e5,m=L(_/36e5)%24,E=L(_/6e4)%60,b=L(_/1e3)%60,y=_%1e3}else c=o.getUTCFullYear(),u=o.getUTCMonth(),d=o.getUTCDate(),m=o.getUTCHours(),E=o.getUTCMinutes(),b=o.getUTCSeconds(),y=o.getUTCMilliseconds();o=(0>=c||c>=1e4?(0>c?"-":"+")+O(6,0>c?-c:c):O(4,c))+"-"+O(2,u+1)+"-"+O(2,d)+"T"+O(2,m)+":"+O(2,E)+":"+O(2,b)+"."+O(3,y)+"Z"}else o=null;if(i&&(o=i.call(e,t,o)),null===o)return"null";if(h=v.call(o),h==w)return""+o;if(h==j)return o>-1/0&&1/0>o?""+o:"null";if(h==T)return D(""+o);if("object"==typeof o){for(M=a.length;M--;)if(a[M]===o)throw l();if(a.push(o),P=[],C=n,n+=r,h==x){for(I=0,M=o.length;M>I;I++)A=N(I,o,i,s,r,n,a),P.push(A===g?"null":A);k=P.length?r?"[\n"+n+P.join(",\n"+n)+"\n"+C+"]":"["+P.join(",")+"]":"[]"}else f(s||o,function(t){var e=N(t,o,i,s,r,n,a);e!==g&&P.push(D(t)+":"+(r?" ":"")+e)}),k=P.length?r?"{\n"+n+P.join(",\n"+n)+"\n"+C+"}":"{"+P.join(",")+"}":"{}";return a.pop(),k}};s.stringify=function(t,e,s){var r,n,a,o;if(i[typeof e]&&e)if((o=v.call(e))==y)n=e;else if(o==x){a={};for(var h,c=0,u=e.length;u>c;h=e[c++],o=v.call(h),(o==T||o==j)&&(a[h]=1));}if(s)if((o=v.call(s))==j){if((s-=s%1)>0)for(r="",s>10&&(s=10);r.length<s;r+=" ");}else o==T&&(r=s.length<=10?s:s.slice(0,10));return N("",(h={},h[""]=t,h),n,a,r,"",[])}}if(!n("json-parse")){var k,F,B=o.fromCharCode,H={92:"\\",34:'"',47:"/",98:"\b",116:"	",110:"\n",102:"\f",114:"\r"},X=function(){throw k=F=null,u()},U=function(){for(var t,e,i,s,r,n=F,a=n.length;a>k;)switch(r=n.charCodeAt(k)){case 9:case 10:case 13:case 32:k++;break;case 123:case 125:case 91:case 93:case 58:case 44:return t=P?n.charAt(k):n[k],k++,t;case 34:for(t="@",k++;a>k;)if(r=n.charCodeAt(k),32>r)X();else if(92==r)switch(r=n.charCodeAt(++k)){case 92:case 34:case 47:case 98:case 116:case 110:case 102:case 114:t+=H[r],k++;break;case 117:for(e=++k,i=k+4;i>k;k++)r=n.charCodeAt(k),r>=48&&57>=r||r>=97&&102>=r||r>=65&&70>=r||X();t+=B("0x"+n.slice(e,k));break;default:X()}else{if(34==r)break;for(r=n.charCodeAt(k),e=k;r>=32&&92!=r&&34!=r;)r=n.charCodeAt(++k);t+=n.slice(e,k)}if(34==n.charCodeAt(k))return k++,t;X();default:if(e=k,45==r&&(s=!0,r=n.charCodeAt(++k)),r>=48&&57>=r){for(48==r&&(r=n.charCodeAt(k+1),r>=48&&57>=r)&&X(),s=!1;a>k&&(r=n.charCodeAt(k),r>=48&&57>=r);k++);if(46==n.charCodeAt(k)){for(i=++k;a>i&&(r=n.charCodeAt(i),r>=48&&57>=r);i++);i==k&&X(),k=i}if(r=n.charCodeAt(k),101==r||69==r){for(r=n.charCodeAt(++k),(43==r||45==r)&&k++,i=k;a>i&&(r=n.charCodeAt(i),r>=48&&57>=r);i++);i==k&&X(),k=i}return+n.slice(e,k)}if(s&&X(),"true"==n.slice(k,k+4))return k+=4,!0;if("false"==n.slice(k,k+5))return k+=5,!1;if("null"==n.slice(k,k+4))return k+=4,null;X()}return"$"},G=function(t){var e,i;if("$"==t&&X(),"string"==typeof t){if("@"==(P?t.charAt(0):t[0]))return t.slice(1);if("["==t){for(e=[];t=U(),"]"!=t;i||(i=!0))i&&(","==t?(t=U(),"]"==t&&X()):X()),","==t&&X(),e.push(G(t));return e}if("{"==t){for(e={};t=U(),"}"!=t;i||(i=!0))i&&(","==t?(t=U(),"}"==t&&X()):X()),(","==t||"string"!=typeof t||"@"!=(P?t.charAt(0):t[0])||":"!=U())&&X(),e[t.slice(1)]=G(U());return e}X()}return t},q=function(t,e,i){var s=Y(t,e,i);s===g?delete t[e]:t[e]=s},Y=function(t,e,i){var s,r=t[e];if("object"==typeof r&&r)if(v.call(r)==x)for(s=r.length;s--;)q(r,s,i);else f(r,function(t){q(r,t,i)});return i.call(t,e,r)};s.parse=function(t,e){var i,s;return k=0,F=""+t,i=G(U()),"$"!=U()&&X(),k=F=null,e&&v.call(e)==y?Y((s={},s[""]=i,s),"",e):i}}}return s.runInContext=t,s}var e="function"==typeof define&&define.amd,i={"function":!0,object:!0},s=i[typeof exports]&&exports&&!exports.nodeType&&exports,r=i[typeof window]&&window||this,n=s&&i[typeof module]&&module&&!module.nodeType&&"object"==typeof global&&global;if(!n||n.global!==n&&n.window!==n&&n.self!==n||(r=n),s&&!e)t(r,s);else{var a=r.JSON,o=r.JSON3,h=!1,c=t(r,r.JSON3={noConflict:function(){return h||(h=!0,r.JSON=a,r.JSON3=o,a=o=null),c}});r.JSON={parse:c.parse,stringify:c.stringify}}e&&define(function(){return c})}.call(this),function(){var t={};t.parseXML=function(t,e){var i=null;try{if(window.DOMParser){var s=new DOMParser;i=s.parseFromString(t,e)}else i=new ActiveXObject("Microsoft.XMLDOM"),i.async=!1,i.loadXML(t)}catch(r){}return i},t.parseJSON=function(t){if(null==t)return null;try{return JSON.parse(t)}catch(e){throw e}},createjs.DataUtils=t}(),this.createjs=this.createjs||{},function(){"use strict";function t(){this.src=null,this.type=null,this.id=null,this.maintainOrder=!1,this.callback=null,this.data=null,this.method=createjs.LoadItem.GET,this.values=null,this.headers=null,this.withCredentials=!1,this.mimeType=null,this.crossOrigin=null,this.loadTimeout=8e3}var e=t.prototype={},i=t;i.create=function(e){if("string"==typeof e){var s=new t;return s.src=e,s}if(e instanceof i)return e;if(e instanceof Object)return e;throw new Error("Type not recognized.")},e.set=function(t){for(var e in t)this[e]=t[e];return this},createjs.LoadItem=i}(),function(){var t={};t.ABSOLUTE_PATT=/^(?:\w+:)?\/{2}/i,t.RELATIVE_PATT=/^[./]*?\//i,t.EXTENSION_PATT=/\/?[^/]+\.(\w{1,5})$/i,t.parseURI=function(e){var i={absolute:!1,relative:!1};if(null==e)return i;var s=e.indexOf("?");s>-1&&(e=e.substr(0,s));var r;return t.ABSOLUTE_PATT.test(e)?i.absolute=!0:t.RELATIVE_PATT.test(e)&&(i.relative=!0),(r=e.match(t.EXTENSION_PATT))&&(i.extension=r[1].toLowerCase()),i},t.formatQueryString=function(t,e){if(null==t)throw new Error("You must specify data.");var i=[];for(var s in t)i.push(s+"="+escape(t[s]));return e&&(i=i.concat(e)),i.join("&")},t.buildPath=function(t,e){if(null==e)return t;var i=[],s=t.indexOf("?");if(-1!=s){var r=t.slice(s+1);i=i.concat(r.split("&"))}return-1!=s?t.slice(0,s)+"?"+this._formatQueryString(e,i):t+"?"+this._formatQueryString(e,i)},t.isCrossDomain=function(t){var e=document.createElement("a");e.href=t.src;var i=document.createElement("a");i.href=location.href;var s=""!=e.hostname&&(e.port!=i.port||e.protocol!=i.protocol||e.hostname!=i.hostname);return s},t.isLocal=function(t){var e=document.createElement("a");return e.href=t.src,""==e.hostname&&"file:"==e.protocol},t.isBinary=function(t){switch(t){case createjs.AbstractLoader.IMAGE:case createjs.AbstractLoader.BINARY:return!0;default:return!1}},t.isImageTag=function(t){return t instanceof HTMLImageElement},t.isAudioTag=function(t){return window.HTMLAudioElement?t instanceof HTMLAudioElement:!1},t.isVideoTag=function(t){return window.HTMLVideoElement?t instanceof HTMLVideoElement:void 0},t.isText=function(t){
switch(t){case createjs.AbstractLoader.TEXT:case createjs.AbstractLoader.JSON:case createjs.AbstractLoader.MANIFEST:case createjs.AbstractLoader.XML:case createjs.AbstractLoader.CSS:case createjs.AbstractLoader.SVG:case createjs.AbstractLoader.JAVASCRIPT:return!0;default:return!1}},t.getTypeByExtension=function(t){if(null==t)return createjs.AbstractLoader.TEXT;switch(t.toLowerCase()){case"jpeg":case"jpg":case"gif":case"png":case"webp":case"bmp":return createjs.AbstractLoader.IMAGE;case"ogg":case"mp3":case"webm":return createjs.AbstractLoader.SOUND;case"mp4":case"webm":case"ts":return createjs.AbstractLoader.VIDEO;case"json":return createjs.AbstractLoader.JSON;case"xml":return createjs.AbstractLoader.XML;case"css":return createjs.AbstractLoader.CSS;case"js":return createjs.AbstractLoader.JAVASCRIPT;case"svg":return createjs.AbstractLoader.SVG;default:return createjs.AbstractLoader.TEXT}},createjs.RequestUtils=t}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e,i){this.EventDispatcher_constructor(),this.loaded=!1,this.canceled=!1,this.progress=0,this.type=i,this.resultFormatter=null,this._item=t?createjs.LoadItem.create(t):null,this._preferXHR=e,this._result=null,this._rawResult=null,this._loadedItems=null,this._tagSrcAttribute=null,this._tag=null}var e=createjs.extend(t,createjs.EventDispatcher),i=t;i.POST="POST",i.GET="GET",i.BINARY="binary",i.CSS="css",i.IMAGE="image",i.JAVASCRIPT="javascript",i.JSON="json",i.JSONP="jsonp",i.MANIFEST="manifest",i.SOUND="sound",i.VIDEO="video",i.SPRITESHEET="spritesheet",i.SVG="svg",i.TEXT="text",i.XML="xml",e.getItem=function(){return this._item},e.getResult=function(t){return t?this._rawResult:this._result},e.getTag=function(){return this._tag},e.setTag=function(t){this._tag=t},e.load=function(){this._createRequest(),this._request.on("complete",this,this),this._request.on("progress",this,this),this._request.on("loadStart",this,this),this._request.on("abort",this,this),this._request.on("timeout",this,this),this._request.on("error",this,this);var t=new createjs.Event("initialize");t.loader=this._request,this.dispatchEvent(t),this._request.load()},e.cancel=function(){this.canceled=!0,this.destroy()},e.destroy=function(){this._request&&(this._request.removeAllEventListeners(),this._request.destroy()),this._request=null,this._item=null,this._rawResult=null,this._result=null,this._loadItems=null,this.removeAllEventListeners()},e.getLoadedItems=function(){return this._loadedItems},e._createRequest=function(){this._request=this._preferXHR?new createjs.XHRRequest(this._item):new createjs.TagRequest(this._item,this._tag||this._createTag(),this._tagSrcAttribute)},e._createTag=function(){return null},e._sendLoadStart=function(){this._isCanceled()||this.dispatchEvent("loadstart")},e._sendProgress=function(t){if(!this._isCanceled()){var e=null;"number"==typeof t?(this.progress=t,e=new createjs.ProgressEvent(this.progress)):(e=t,this.progress=t.loaded/t.total,e.progress=this.progress,(isNaN(this.progress)||1/0==this.progress)&&(this.progress=0)),this.hasEventListener("progress")&&this.dispatchEvent(e)}},e._sendComplete=function(){if(!this._isCanceled()){this.loaded=!0;var t=new createjs.Event("complete");t.rawResult=this._rawResult,null!=this._result&&(t.result=this._result),this.dispatchEvent(t)}},e._sendError=function(t){!this._isCanceled()&&this.hasEventListener("error")&&(null==t&&(t=new createjs.ErrorEvent("PRELOAD_ERROR_EMPTY")),this.dispatchEvent(t))},e._isCanceled=function(){return null==window.createjs||this.canceled?!0:!1},e.resultFormatter=null,e.handleEvent=function(t){switch(t.type){case"complete":this._rawResult=t.target._response;var e=this.resultFormatter&&this.resultFormatter(this),i=this;e instanceof Function?e(function(t){i._result=t,i._sendComplete()}):(this._result=e||this._rawResult,this._sendComplete());break;case"progress":this._sendProgress(t);break;case"error":this._sendError(t);break;case"loadstart":this._sendLoadStart();break;case"abort":case"timeout":this._isCanceled()||this.dispatchEvent(t.type)}},e.buildPath=function(t,e){return createjs.RequestUtils.buildPath(t,e)},e.toString=function(){return"[PreloadJS AbstractLoader]"},createjs.AbstractLoader=createjs.promote(t,"EventDispatcher")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e,i){this.AbstractLoader_constructor(t,e,i),this.resultFormatter=this._formatResult,this._tagSrcAttribute="src"}var e=createjs.extend(t,createjs.AbstractLoader);e.load=function(){this._tag||(this._tag=this._createTag(this._item.src)),this._tag.preload="auto",this._tag.load(),this.AbstractLoader_load()},e._createTag=function(){},e._createRequest=function(){this._request=this._preferXHR?new createjs.XHRRequest(this._item):new createjs.MediaTagRequest(this._item,this._tag||this._createTag(),this._tagSrcAttribute)},e._formatResult=function(t){return this._tag.removeEventListener&&this._tag.removeEventListener("canplaythrough",this._loadedHandler),this._tag.onstalled=null,this._preferXHR&&(t.getTag().src=t.getResult(!0)),t.getTag()},createjs.AbstractMediaLoader=createjs.promote(t,"AbstractLoader")}(),this.createjs=this.createjs||{},function(){"use strict";var t=function(t){this._item=t},e=createjs.extend(t,createjs.EventDispatcher);e.load=function(){},e.destroy=function(){},e.cancel=function(){},createjs.AbstractRequest=createjs.promote(t,"EventDispatcher")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e,i){this.AbstractRequest_constructor(t),this._tag=e,this._tagSrcAttribute=i,this._loadedHandler=createjs.proxy(this._handleTagComplete,this),this._addedToDOM=!1,this._startTagVisibility=null}var e=createjs.extend(t,createjs.AbstractRequest);e.load=function(){null==this._tag.parentNode&&(window.document.body.appendChild(this._tag),this._addedToDOM=!0),this._tag.onload=createjs.proxy(this._handleTagComplete,this),this._tag.onreadystatechange=createjs.proxy(this._handleReadyStateChange,this);var t=new createjs.Event("initialize");t.loader=this._tag,this.dispatchEvent(t),this._hideTag(),this._tag[this._tagSrcAttribute]=this._item.src},e.destroy=function(){this._clean(),this._tag=null,this.AbstractRequest_destroy()},e._handleReadyStateChange=function(){clearTimeout(this._loadTimeout);var t=this._tag;("loaded"==t.readyState||"complete"==t.readyState)&&this._handleTagComplete()},e._handleTagComplete=function(){this._rawResult=this._tag,this._result=this.resultFormatter&&this.resultFormatter(this)||this._rawResult,this._clean(),this._showTag(),this.dispatchEvent("complete")},e._clean=function(){this._tag.onload=null,this._tag.onreadystatechange=null,this._addedToDOM&&null!=this._tag.parentNode&&this._tag.parentNode.removeChild(this._tag)},e._hideTag=function(){this._startTagVisibility=this._tag.style.visibility,this._tag.style.visibility="hidden"},e._showTag=function(){this._tag.style.visibility=this._startTagVisibility},e._handleStalled=function(){},createjs.TagRequest=createjs.promote(t,"AbstractRequest")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e,i){this.AbstractRequest_constructor(t),this._tag=e,this._tagSrcAttribute=i,this._loadedHandler=createjs.proxy(this._handleTagComplete,this)}var e=createjs.extend(t,createjs.TagRequest);e.load=function(){this._tag.onstalled=createjs.proxy(this._handleStalled,this),this._tag.onprogress=createjs.proxy(this._handleProgress,this),this._tag.addEventListener&&this._tag.addEventListener("canplaythrough",this._loadedHandler,!1),this.TagRequest_load()},e._handleReadyStateChange=function(){clearTimeout(this._loadTimeout);var t=this._tag;("loaded"==t.readyState||"complete"==t.readyState)&&this._handleTagComplete()},e._handleStalled=function(){},e._handleProgress=function(t){if(t&&!(t.loaded>0&&0==t.total)){var e=new createjs.ProgressEvent(t.loaded,t.total);this.dispatchEvent(e)}},e._clean=function(){this._tag.removeEventListener&&this._tag.removeEventListener("canplaythrough",this._loadedHandler),this._tag.onstalled=null,this._tag.onprogress=null,this.TagRequest__clean()},createjs.MediaTagRequest=createjs.promote(t,"TagRequest")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t){this.AbstractRequest_constructor(t),this._request=null,this._loadTimeout=null,this._xhrLevel=1,this._response=null,this._rawResponse=null,this._canceled=!1,this._handleLoadStartProxy=createjs.proxy(this._handleLoadStart,this),this._handleProgressProxy=createjs.proxy(this._handleProgress,this),this._handleAbortProxy=createjs.proxy(this._handleAbort,this),this._handleErrorProxy=createjs.proxy(this._handleError,this),this._handleTimeoutProxy=createjs.proxy(this._handleTimeout,this),this._handleLoadProxy=createjs.proxy(this._handleLoad,this),this._handleReadyStateChangeProxy=createjs.proxy(this._handleReadyStateChange,this),!this._createXHR(t)}var e=createjs.extend(t,createjs.AbstractRequest);t.ACTIVEX_VERSIONS=["Msxml2.XMLHTTP.6.0","Msxml2.XMLHTTP.5.0","Msxml2.XMLHTTP.4.0","MSXML2.XMLHTTP.3.0","MSXML2.XMLHTTP","Microsoft.XMLHTTP"],e.getResult=function(t){return t&&this._rawResponse?this._rawResponse:this._response},e.cancel=function(){this.canceled=!0,this._clean(),this._request.abort()},e.load=function(){if(null==this._request)return void this._handleError();this._request.addEventListener("loadstart",this._handleLoadStartProxy,!1),this._request.addEventListener("progress",this._handleProgressProxy,!1),this._request.addEventListener("abort",this._handleAbortProxy,!1),this._request.addEventListener("error",this._handleErrorProxy,!1),this._request.addEventListener("timeout",this._handleTimeoutProxy,!1),this._request.addEventListener("load",this._handleLoadProxy,!1),this._request.addEventListener("readystatechange",this._handleReadyStateChangeProxy,!1),1==this._xhrLevel&&(this._loadTimeout=setTimeout(createjs.proxy(this._handleTimeout,this),this._item.loadTimeout));try{this._item.values&&this._item.method!=createjs.AbstractLoader.GET?this._item.method==createjs.AbstractLoader.POST&&this._request.send(createjs.RequestUtils.formatQueryString(this._item.values)):this._request.send()}catch(t){this.dispatchEvent(new createjs.ErrorEvent("XHR_SEND",null,t))}},e.setResponseType=function(t){this._request.responseType=t},e.getAllResponseHeaders=function(){return this._request.getAllResponseHeaders instanceof Function?this._request.getAllResponseHeaders():null},e.getResponseHeader=function(t){return this._request.getResponseHeader instanceof Function?this._request.getResponseHeader(t):null},e._handleProgress=function(t){if(t&&!(t.loaded>0&&0==t.total)){var e=new createjs.ProgressEvent(t.loaded,t.total);this.dispatchEvent(e)}},e._handleLoadStart=function(){clearTimeout(this._loadTimeout),this.dispatchEvent("loadstart")},e._handleAbort=function(t){this._clean(),this.dispatchEvent(new createjs.ErrorEvent("XHR_ABORTED",null,t))},e._handleError=function(t){this._clean(),this.dispatchEvent(new createjs.ErrorEvent(t.message))},e._handleReadyStateChange=function(){4==this._request.readyState&&this._handleLoad()},e._handleLoad=function(){if(!this.loaded){this.loaded=!0;var t=this._checkError();if(t)return void this._handleError(t);this._response=this._getResponse(),this._clean(),this.dispatchEvent(new createjs.Event("complete"))}},e._handleTimeout=function(t){this._clean(),this.dispatchEvent(new createjs.ErrorEvent("PRELOAD_TIMEOUT",null,t))},e._checkError=function(){var t=parseInt(this._request.status);switch(t){case 404:case 0:return new Error(t)}return null},e._getResponse=function(){if(null!=this._response)return this._response;if(null!=this._request.response)return this._request.response;try{if(null!=this._request.responseText)return this._request.responseText}catch(t){}try{if(null!=this._request.responseXML)return this._request.responseXML}catch(t){}return null},e._createXHR=function(t){var e=createjs.RequestUtils.isCrossDomain(t),i={},r=null;if(window.XMLHttpRequest)r=new XMLHttpRequest,e&&void 0===r.withCredentials&&window.XDomainRequest&&(r=new XDomainRequest);else{for(var n=0,a=s.ACTIVEX_VERSIONS.length;a>n;n++){s.ACTIVEX_VERSIONS[n];try{r=new ActiveXObject(axVersions);break}catch(o){}}if(null==r)return!1}t.mimeType&&r.overrideMimeType&&r.overrideMimeType(t.mimeType),this._xhrLevel="string"==typeof r.responseType?2:1;var h=null;if(h=t.method==createjs.AbstractLoader.GET?createjs.RequestUtils.buildPath(t.src,t.values):t.src,r.open(t.method||createjs.AbstractLoader.GET,h,!0),e&&r instanceof XMLHttpRequest&&1==this._xhrLevel&&(i.Origin=location.origin),t.values&&t.method==createjs.AbstractLoader.POST&&(i["Content-Type"]="application/x-www-form-urlencoded"),e||i["X-Requested-With"]||(i["X-Requested-With"]="XMLHttpRequest"),t.headers)for(var c in t.headers)i[c]=t.headers[c];for(c in i)r.setRequestHeader(c,i[c]);return r instanceof XMLHttpRequest&&void 0!==t.withCredentials&&(r.withCredentials=t.withCredentials),this._request=r,!0},e._clean=function(){clearTimeout(this._loadTimeout),this._request.removeEventListener("loadstart",this._handleLoadStartProxy),this._request.removeEventListener("progress",this._handleProgressProxy),this._request.removeEventListener("abort",this._handleAbortProxy),this._request.removeEventListener("error",this._handleErrorProxy),this._request.removeEventListener("timeout",this._handleTimeoutProxy),this._request.removeEventListener("load",this._handleLoadProxy),this._request.removeEventListener("readystatechange",this._handleReadyStateChangeProxy)},e.toString=function(){return"[PreloadJS XHRRequest]"},createjs.XHRRequest=createjs.promote(t,"AbstractRequest")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e,i){this.AbstractLoader_constructor(),this.init(t,e,i)}var e=createjs.extend(t,createjs.AbstractLoader),i=t;e.init=function(t,e,i){this.useXHR=!0,this.preferXHR=!0,this._preferXHR=!0,this.setPreferXHR(t),this.stopOnError=!1,this.maintainScriptOrder=!0,this.next=null,this._paused=!1,this._basePath=e,this._crossOrigin=i,this._typeCallbacks={},this._extensionCallbacks={},this._loadStartWasDispatched=!1,this._maxConnections=1,this._currentlyLoadingScript=null,this._currentLoads=[],this._loadQueue=[],this._loadQueueBackup=[],this._loadItemsById={},this._loadItemsBySrc={},this._loadedResults={},this._loadedRawResults={},this._numItems=0,this._numItemsLoaded=0,this._scriptOrder=[],this._loadedScripts=[],this._lastProgress=0/0,this._availableLoaders=[createjs.ImageLoader,createjs.JavaScriptLoader,createjs.CSSLoader,createjs.JSONLoader,createjs.JSONPLoader,createjs.SoundLoader,createjs.ManifestLoader,createjs.SpriteSheetLoader,createjs.XMLLoader,createjs.SVGLoader,createjs.BinaryLoader,createjs.VideoLoader,createjs.TextLoader],this._defaultLoaderLength=this._availableLoaders.length},i.loadTimeout=8e3,i.LOAD_TIMEOUT=0,i.BINARY=createjs.AbstractLoader.BINARY,i.CSS=createjs.AbstractLoader.CSS,i.IMAGE=createjs.AbstractLoader.IMAGE,i.JAVASCRIPT=createjs.AbstractLoader.JAVASCRIPT,i.JSON=createjs.AbstractLoader.JSON,i.JSONP=createjs.AbstractLoader.JSONP,i.MANIFEST=createjs.AbstractLoader.MANIFEST,i.SOUND=createjs.AbstractLoader.SOUND,i.VIDEO=createjs.AbstractLoader.VIDEO,i.SVG=createjs.AbstractLoader.SVG,i.TEXT=createjs.AbstractLoader.TEXT,i.XML=createjs.AbstractLoader.XML,i.POST=createjs.AbstractLoader.POST,i.GET=createjs.AbstractLoader.GET,e.registerLoader=function(t){if(!t||!t.canLoadItem)throw new Error("loader is of an incorrect type.");if(-1!=this._availableLoaders.indexOf(t))throw new Error("loader already exists.");this._availableLoaders.unshift(t)},e.unregisterLoader=function(t){var e=this._availableLoaders.indexOf(t);-1!=e&&e<this._defaultLoaderLength-1&&this._availableLoaders.splice(e,1)},e.setUseXHR=function(t){return this.setPreferXHR(t)},e.setPreferXHR=function(t){return this.preferXHR=0!=t&&null!=window.XMLHttpRequest,this.preferXHR},e.removeAll=function(){this.remove()},e.remove=function(t){var e=null;if(!t||t instanceof Array){if(t)e=t;else if(arguments.length>0)return}else e=[t];var i=!1;if(e){for(;e.length;){var s=e.pop(),r=this.getResult(s);for(n=this._loadQueue.length-1;n>=0;n--)if(a=this._loadQueue[n].getItem(),a.id==s||a.src==s){this._loadQueue.splice(n,1)[0].cancel();break}for(n=this._loadQueueBackup.length-1;n>=0;n--)if(a=this._loadQueueBackup[n].getItem(),a.id==s||a.src==s){this._loadQueueBackup.splice(n,1)[0].cancel();break}if(r)delete this._loadItemsById[r.id],delete this._loadItemsBySrc[r.src],this._disposeItem(r);else for(var n=this._currentLoads.length-1;n>=0;n--){var a=this._currentLoads[n].getItem();if(a.id==s||a.src==s){this._currentLoads.splice(n,1)[0].cancel(),i=!0;break}}}i&&this._loadNext()}else{this.close();for(var o in this._loadItemsById)this._disposeItem(this._loadItemsById[o]);this.init(this.preferXHR,this._basePath)}},e.reset=function(){this.close();for(var t in this._loadItemsById)this._disposeItem(this._loadItemsById[t]);for(var e=[],i=0,s=this._loadQueueBackup.length;s>i;i++)e.push(this._loadQueueBackup[i].getItem());this.loadManifest(e,!1)},e.installPlugin=function(t){if(null!=t&&null!=t.getPreloadHandlers){var e=t.getPreloadHandlers();if(e.scope=t,null!=e.types)for(var i=0,s=e.types.length;s>i;i++)this._typeCallbacks[e.types[i]]=e;if(null!=e.extensions)for(i=0,s=e.extensions.length;s>i;i++)this._extensionCallbacks[e.extensions[i]]=e}},e.setMaxConnections=function(t){this._maxConnections=t,!this._paused&&this._loadQueue.length>0&&this._loadNext()},e.loadFile=function(t,e,i){if(null==t){var s=new createjs.ErrorEvent("PRELOAD_NO_FILE");return void this._sendError(s)}this._addItem(t,null,i),this.setPaused(e!==!1?!1:!0)},e.loadManifest=function(t,e,s){var r=null,n=null;if(t instanceof Array){if(0==t.length){var a=new createjs.ErrorEvent("PRELOAD_MANIFEST_EMPTY");return void this._sendError(a)}r=t}else if("string"==typeof t)r=[{src:t,type:i.MANIFEST}];else{if("object"!=typeof t){var a=new createjs.ErrorEvent("PRELOAD_MANIFEST_NULL");return void this._sendError(a)}if(void 0!==t.src){if(null==t.type)t.type=i.MANIFEST;else if(t.type!=i.MANIFEST){var a=new createjs.ErrorEvent("PRELOAD_MANIFEST_TYPE");this._sendError(a)}r=[t]}else void 0!==t.manifest&&(r=t.manifest,n=t.path)}for(var o=0,h=r.length;h>o;o++)this._addItem(r[o],n,s);this.setPaused(e!==!1?!1:!0)},e.load=function(){this.setPaused(!1)},e.getItem=function(t){return this._loadItemsById[t]||this._loadItemsBySrc[t]},e.getResult=function(t,e){var i=this._loadItemsById[t]||this._loadItemsBySrc[t];if(null==i)return null;var s=i.id;return e&&this._loadedRawResults[s]?this._loadedRawResults[s]:this._loadedResults[s]},e.getItems=function(t){for(var e=[],i=0,s=this._loadQueueBackup.length;s>i;i++){var r=this._loadQueueBackup[i],n=r.getItem();(t!==!0||r.loaded)&&e.push({item:n,result:this.getResult(n.id),rawResult:this.getResult(n.id,!0)})}return e},e.setPaused=function(t){this._paused=t,this._paused||this._loadNext()},e.close=function(){for(;this._currentLoads.length;)this._currentLoads.pop().cancel();this._scriptOrder.length=0,this._loadedScripts.length=0,this.loadStartWasDispatched=!1,this._itemCount=0,this._lastProgress=0/0},e._addItem=function(t,e,i){var s=this._createLoadItem(t,e,i);if(null!=s){var r=this._createLoader(s);null!=r&&(s._loader=r,this._loadQueue.push(r),this._loadQueueBackup.push(r),this._numItems++,this._updateProgress(),(this.maintainScriptOrder&&s.type==createjs.LoadQueue.JAVASCRIPT||s.maintainOrder===!0)&&(this._scriptOrder.push(s),this._loadedScripts.push(null)))}},e._createLoadItem=function(t,e,s){var r=createjs.LoadItem.create(t);if(null==r)return null;var n=createjs.RequestUtils.parseURI(r.src);n.extension&&(r.ext=n.extension),null==r.type&&(r.type=createjs.RequestUtils.getTypeByExtension(r.ext));var a="",o=s||this._basePath,h=r.src;if(!n.absolute&&!n.relative)if(e){a=e;var c=createjs.RequestUtils.parseURI(e);h=e+h,null==o||c.absolute||c.relative||(a=o+a)}else null!=o&&(a=o);r.src=a+r.src,r.path=a,(void 0===r.id||null===r.id||""===r.id)&&(r.id=h);var u=this._typeCallbacks[r.type]||this._extensionCallbacks[r.ext];if(u){var l=u.callback.call(u.scope,r,this);if(l===!1)return null;l===!0||null!=l&&(r._loader=l),n=createjs.RequestUtils.parseURI(r.src),null!=n.extension&&(r.ext=n.extension)}return this._loadItemsById[r.id]=r,this._loadItemsBySrc[r.src]=r,null==r.loadTimeout&&(r.loadTimeout=i.loadTimeout),null==r.crossOrigin&&(r.crossOrigin=this._crossOrigin),r},e._createLoader=function(t){if(null!=t._loader)return t._loader;for(var e=this.preferXHR,i=0;i<this._availableLoaders.length;i++){var s=this._availableLoaders[i];if(s&&s.canLoadItem(t))return new s(t,e)}return null},e._loadNext=function(){if(!this._paused){this._loadStartWasDispatched||(this._sendLoadStart(),this._loadStartWasDispatched=!0),this._numItems==this._numItemsLoaded?(this.loaded=!0,this._sendComplete(),this.next&&this.next.load&&this.next.load()):this.loaded=!1;for(var t=0;t<this._loadQueue.length&&!(this._currentLoads.length>=this._maxConnections);t++){var e=this._loadQueue[t];this._canStartLoad(e)&&(this._loadQueue.splice(t,1),t--,this._loadItem(e))}}},e._loadItem=function(t){t.on("fileload",this._handleFileLoad,this),t.on("progress",this._handleProgress,this),t.on("complete",this._handleFileComplete,this),t.on("error",this._handleError,this),t.on("fileerror",this._handleFileError,this),this._currentLoads.push(t),this._sendFileStart(t.getItem()),t.load()},e._handleFileLoad=function(t){t.target=null,this.dispatchEvent(t)},e._handleFileError=function(t){var e=new createjs.ErrorEvent("FILE_LOAD_ERROR",null,t.item);this._sendError(e)},e._handleError=function(t){var e=t.target;this._numItemsLoaded++,this._finishOrderedItem(e,!0),this._updateProgress();var i=new createjs.ErrorEvent("FILE_LOAD_ERROR",null,e.getItem());this._sendError(i),this.stopOnError||(this._removeLoadItem(e),this._loadNext())},e._handleFileComplete=function(t){var e=t.target,i=e.getItem(),s=e.getResult();this._loadedResults[i.id]=s;var r=e.getResult(!0);null!=r&&r!==s&&(this._loadedRawResults[i.id]=r),this._saveLoadedItems(e),this._removeLoadItem(e),this._finishOrderedItem(e)||this._processFinishedLoad(i,e)},e._saveLoadedItems=function(t){var e=t.getLoadedItems();if(null!==e)for(var i=0;i<e.length;i++){var s=e[i].item;this._loadItemsBySrc[s.src]=s,this._loadItemsById[s.id]=s,this._loadedResults[s.id]=e[i].result,this._loadedRawResults[s.id]=e[i].rawResult}},e._finishOrderedItem=function(t,e){var i=t.getItem();if(this.maintainScriptOrder&&i.type==createjs.LoadQueue.JAVASCRIPT||i.maintainOrder){t instanceof createjs.JavaScriptLoader&&(this._currentlyLoadingScript=!1);var s=createjs.indexOf(this._scriptOrder,i);return-1==s?!1:(this._loadedScripts[s]=e===!0?!0:i,this._checkScriptLoadOrder(),!0)}return!1},e._checkScriptLoadOrder=function(){for(var t=this._loadedScripts.length,e=0;t>e;e++){var i=this._loadedScripts[e];if(null===i)break;if(i!==!0){var s=this._loadedResults[i.id];i.type==createjs.LoadQueue.JAVASCRIPT&&(document.body||document.getElementsByTagName("body")[0]).appendChild(s);var r=i._loader;this._processFinishedLoad(i,r),this._loadedScripts[e]=!0}}},e._processFinishedLoad=function(t,e){this._numItemsLoaded++,this._updateProgress(),this._sendFileComplete(t,e),this._loadNext()},e._canStartLoad=function(t){if(!this.maintainScriptOrder||t.preferXHR)return!0;var e=t.getItem();if(e.type!=createjs.LoadQueue.JAVASCRIPT)return!0;if(this._currentlyLoadingScript)return!1;for(var i=this._scriptOrder.indexOf(e),s=0;i>s;){var r=this._loadedScripts[s];if(null==r)return!1;s++}return this._currentlyLoadingScript=!0,!0},e._removeLoadItem=function(t){var e=t.getItem();delete e._loader;for(var i=this._currentLoads.length,s=0;i>s;s++)if(this._currentLoads[s]==t){this._currentLoads.splice(s,1);break}},e._handleProgress=function(t){var e=t.target;this._sendFileProgress(e.getItem(),e.progress),this._updateProgress()},e._updateProgress=function(){var t=this._numItemsLoaded/this._numItems,e=this._numItems-this._numItemsLoaded;if(e>0){for(var i=0,s=0,r=this._currentLoads.length;r>s;s++)i+=this._currentLoads[s].progress;t+=i/e*(e/this._numItems)}this._lastProgress!=t&&(this._sendProgress(t),this._lastProgress=t)},e._disposeItem=function(t){delete this._loadedResults[t.id],delete this._loadedRawResults[t.id],delete this._loadItemsById[t.id],delete this._loadItemsBySrc[t.src]},e._sendFileProgress=function(t,e){if(this._isCanceled())return void this._cleanUp();if(this.hasEventListener("fileprogress")){var i=new createjs.Event("fileprogress");i.progress=e,i.loaded=e,i.total=1,i.item=t,this.dispatchEvent(i)}},e._sendFileComplete=function(t,e){if(!this._isCanceled()){var i=new createjs.Event("fileload");i.loader=e,i.item=t,i.result=this._loadedResults[t.id],i.rawResult=this._loadedRawResults[t.id],t.completeHandler&&t.completeHandler(i),this.hasEventListener("fileload")&&this.dispatchEvent(i)}},e._sendFileStart=function(t){var e=new createjs.Event("filestart");e.item=t,this.hasEventListener("filestart")&&this.dispatchEvent(e)},e.toString=function(){return"[PreloadJS LoadQueue]"},createjs.LoadQueue=createjs.promote(t,"AbstractLoader")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t){this.AbstractLoader_constructor(t,!0,createjs.AbstractLoader.TEXT)}var e=(createjs.extend(t,createjs.AbstractLoader),t);e.canLoadItem=function(t){return t.type==createjs.AbstractLoader.TEXT},createjs.TextLoader=createjs.promote(t,"AbstractLoader")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t){this.AbstractLoader_constructor(t,!0,createjs.AbstractLoader.BINARY),this.on("initialize",this._updateXHR,this)}var e=createjs.extend(t,createjs.AbstractLoader),i=t;i.canLoadItem=function(t){return t.type==createjs.AbstractLoader.BINARY},e._updateXHR=function(t){t.loader.setResponseType("arraybuffer")},createjs.BinaryLoader=createjs.promote(t,"AbstractLoader")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e){this.AbstractLoader_constructor(t,e,createjs.AbstractLoader.CSS),this.resultFormatter=this._formatResult,this._tagSrcAttribute="href",this._tag=document.createElement(e?"style":"link"),this._tag.rel="stylesheet",this._tag.type="text/css"}var e=createjs.extend(t,createjs.AbstractLoader),i=t;i.canLoadItem=function(t){return t.type==createjs.AbstractLoader.CSS},e._formatResult=function(t){if(this._preferXHR){var e=t.getTag(),i=document.getElementsByTagName("head")[0];if(i.appendChild(e),e.styleSheet)e.styleSheet.cssText=t.getResult(!0);else{var s=document.createTextNode(t.getResult(!0));e.appendChild(s)}}else e=this._tag;return e},createjs.CSSLoader=createjs.promote(t,"AbstractLoader")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e){this.AbstractLoader_constructor(t,e,createjs.AbstractLoader.IMAGE),this.resultFormatter=this._formatResult,this._tagSrcAttribute="src",createjs.RequestUtils.isImageTag(t)?this._tag=t:createjs.RequestUtils.isImageTag(t.src)?this._tag=t.src:createjs.RequestUtils.isImageTag(t.tag)&&(this._tag=t.tag),null!=this._tag?this._preferXHR=!1:this._tag=document.createElement("img"),this.on("initialize",this._updateXHR,this)}var e=createjs.extend(t,createjs.AbstractLoader),i=t;i.canLoadItem=function(t){return t.type==createjs.AbstractLoader.IMAGE},e.load=function(){if(""!=this._tag.src&&this._tag.complete)return void this._sendComplete();var t=this._item.crossOrigin;1==t&&(t="Anonymous"),null==t||createjs.RequestUtils.isLocal(this._item.src)||(this._tag.crossOrigin=t),this.AbstractLoader_load()},e._updateXHR=function(t){t.loader.mimeType="text/plain; charset=x-user-defined-binary",t.loader.setResponseType&&t.loader.setResponseType("blob")},e._formatResult=function(t){var e=this;return function(i){var s=e._tag,r=window.URL||window.webkitURL;if(e._preferXHR)if(r){var n=r.createObjectURL(t.getResult(!0));s.src=n,s.onload=function(){r.revokeObjectURL(e.src)}}else s.src=t.getItem().src;s.complete?i(s):s.onload=function(){i(this)}}},createjs.ImageLoader=createjs.promote(t,"AbstractLoader")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e){this.AbstractLoader_constructor(t,e,createjs.AbstractLoader.JAVASCRIPT),this.resultFormatter=this._formatResult,this._tagSrcAttribute="src",this.setTag(document.createElement("script"))}var e=createjs.extend(t,createjs.AbstractLoader),i=t;i.canLoadItem=function(t){return t.type==createjs.AbstractLoader.JAVASCRIPT},e._formatResult=function(t){var e=t.getTag();return this._preferXHR&&(e.text=t.getResult(!0)),e},createjs.JavaScriptLoader=createjs.promote(t,"AbstractLoader")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t){this.AbstractLoader_constructor(t,!0,createjs.AbstractLoader.JSON),this.resultFormatter=this._formatResult}var e=createjs.extend(t,createjs.AbstractLoader),i=t;i.canLoadItem=function(t){return t.type==createjs.AbstractLoader.JSON&&!t._loadAsJSONP},e._formatResult=function(t){var e=null;try{e=createjs.DataUtils.parseJSON(t.getResult(!0))}catch(i){var s=new createjs.ErrorEvent("JSON_FORMAT",null,i);return this._sendError(s),i}return e},createjs.JSONLoader=createjs.promote(t,"AbstractLoader")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t){this.AbstractLoader_constructor(t,!1,createjs.AbstractLoader.JSONP),this.setTag(document.createElement("script")),this.getTag().type="text/javascript"}var e=createjs.extend(t,createjs.AbstractLoader),i=t;i.canLoadItem=function(t){return t.type==createjs.AbstractLoader.JSONP||t._loadAsJSONP},e.cancel=function(){this.AbstractLoader_cancel(),this._dispose()},e.load=function(){if(null==this._item.callback)throw new Error("callback is required for loading JSONP requests.");if(null!=window[this._item.callback])throw new Error("JSONP callback '"+this._item.callback+"' already exists on window. You need to specify a different callback or re-name the current one.");window[this._item.callback]=createjs.proxy(this._handleLoad,this),window.document.body.appendChild(this._tag),this._tag.src=this._item.src},e._handleLoad=function(t){this._result=this._rawResult=t,this._sendComplete(),this._dispose()},e._dispose=function(){window.document.body.removeChild(this._tag),delete window[this._item.callback]},createjs.JSONPLoader=createjs.promote(t,"AbstractLoader")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t){this.AbstractLoader_constructor(t,null,createjs.AbstractLoader.MANIFEST),this._manifestQueue=null}var e=createjs.extend(t,createjs.AbstractLoader),i=t;i.MANIFEST_PROGRESS=.25,i.canLoadItem=function(t){return t.type==createjs.AbstractLoader.MANIFEST},e.load=function(){this.AbstractLoader_load()},e._createRequest=function(){var t=this._item.callback;this._request=null!=t?new createjs.JSONPLoader(this._item):new createjs.JSONLoader(this._item)},e.handleEvent=function(t){switch(t.type){case"complete":return this._rawResult=t.target.getResult(!0),this._result=t.target.getResult(),this._sendProgress(i.MANIFEST_PROGRESS),void this._loadManifest(this._result);case"progress":return t.loaded*=i.MANIFEST_PROGRESS,this.progress=t.loaded/t.total,(isNaN(this.progress)||1/0==this.progress)&&(this.progress=0),void this._sendProgress(t)}this.AbstractLoader_handleEvent(t)},e.destroy=function(){this.AbstractLoader_destroy(),this._manifestQueue.close()},e._loadManifest=function(t){if(t&&t.manifest){var e=this._manifestQueue=new createjs.LoadQueue;e.on("fileload",this._handleManifestFileLoad,this),e.on("progress",this._handleManifestProgress,this),e.on("complete",this._handleManifestComplete,this,!0),e.on("error",this._handleManifestError,this,!0),e.loadManifest(t)}else this._sendComplete()},e._handleManifestFileLoad=function(t){t.target=null,this.dispatchEvent(t)},e._handleManifestComplete=function(){this._loadedItems=this._manifestQueue.getItems(!0),this._sendComplete()},e._handleManifestProgress=function(t){this.progress=t.progress*(1-i.MANIFEST_PROGRESS)+i.MANIFEST_PROGRESS,this._sendProgress(this.progress)},e._handleManifestError=function(t){var e=new createjs.Event("fileerror");e.item=t.data,this.dispatchEvent(e)},createjs.ManifestLoader=createjs.promote(t,"AbstractLoader")}(),this.createjs=this.createjs||{},function(){"use strict";

function t(t,e){this.AbstractMediaLoader_constructor(t,e,createjs.AbstractLoader.SOUND),createjs.RequestUtils.isAudioTag(t)?this._tag=t:createjs.RequestUtils.isAudioTag(t.src)?this._tag=t:createjs.RequestUtils.isAudioTag(t.tag)&&(this._tag=createjs.RequestUtils.isAudioTag(t)?t:t.src),null!=this._tag&&(this._preferXHR=!1)}var e=createjs.extend(t,createjs.AbstractMediaLoader),i=t;i.canLoadItem=function(t){return t.type==createjs.AbstractLoader.SOUND},e._createTag=function(t){var e=document.createElement("audio");return e.autoplay=!1,e.preload="none",e.src=t,e},createjs.SoundLoader=createjs.promote(t,"AbstractMediaLoader")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e){this.AbstractMediaLoader_constructor(t,e,createjs.AbstractLoader.VIDEO),createjs.RequestUtils.isVideoTag(t)||createjs.RequestUtils.isVideoTag(t.src)?(this.setTag(createjs.RequestUtils.isVideoTag(t)?t:t.src),this._preferXHR=!1):this.setTag(this._createTag())}var e=createjs.extend(t,createjs.AbstractMediaLoader),i=t;e._createTag=function(){return document.createElement("video")},i.canLoadItem=function(t){return t.type==createjs.AbstractLoader.VIDEO},createjs.VideoLoader=createjs.promote(t,"AbstractMediaLoader")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t){this.AbstractLoader_constructor(t,null,createjs.AbstractLoader.SPRITESHEET),this._manifestQueue=null}var e=createjs.extend(t,createjs.AbstractLoader),i=t;i.SPRITESHEET_PROGRESS=.25,i.canLoadItem=function(t){return t.type==createjs.AbstractLoader.SPRITESHEET},e.destroy=function(){this.AbstractLoader_destroy,this._manifestQueue.close()},e._createRequest=function(){var t=this._item.callback;this._request=null!=t&&t instanceof Function?new createjs.JSONPLoader(this._item):new createjs.JSONLoader(this._item)},e.handleEvent=function(t){switch(t.type){case"complete":return this._rawResult=t.target.getResult(!0),this._result=t.target.getResult(),this._sendProgress(i.SPRITESHEET_PROGRESS),void this._loadManifest(this._result);case"progress":return t.loaded*=i.SPRITESHEET_PROGRESS,this.progress=t.loaded/t.total,(isNaN(this.progress)||1/0==this.progress)&&(this.progress=0),void this._sendProgress(t)}this.AbstractLoader_handleEvent(t)},e._loadManifest=function(t){if(t&&t.images){var e=this._manifestQueue=new createjs.LoadQueue;e.on("complete",this._handleManifestComplete,this,!0),e.on("fileload",this._handleManifestFileLoad,this),e.on("progress",this._handleManifestProgress,this),e.on("error",this._handleManifestError,this,!0),e.loadManifest(t.images)}},e._handleManifestFileLoad=function(t){var e=t.result;if(null!=e){var i=this.getResult().images,s=i.indexOf(t.item.src);i[s]=e}},e._handleManifestComplete=function(){this._result=new createjs.SpriteSheet(this._result),this._loadedItems=this._manifestQueue.getItems(!0),this._sendComplete()},e._handleManifestProgress=function(t){this.progress=t.progress*(1-i.SPRITESHEET_PROGRESS)+i.SPRITESHEET_PROGRESS,this._sendProgress(this.progress)},e._handleManifestError=function(t){var e=new createjs.Event("fileerror");e.item=t.data,this.dispatchEvent(e)},createjs.SpriteSheetLoader=createjs.promote(t,"AbstractLoader")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e){this.AbstractLoader_constructor(t,e,createjs.AbstractLoader.SVG),this.resultFormatter=this._formatResult,this._tagSrcAttribute="data",e?this.setTag(document.createElement("svg")):(this.setTag(document.createElement("object")),this.getTag().type="image/svg+xml"),this.getTag().style.visibility="hidden"}var e=createjs.extend(t,createjs.AbstractLoader),i=t;i.canLoadItem=function(t){return t.type==createjs.AbstractLoader.SVG},e._formatResult=function(t){var e=createjs.DataUtils.parseXML(t.getResult(!0),"text/xml"),i=t.getTag();return!this._preferXHR&&document.body.contains(i)&&document.body.removeChild(i),null!=e.documentElement?(i.appendChild(e.documentElement),i.style.visibility="visible",i):e},createjs.SVGLoader=createjs.promote(t,"AbstractLoader")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t){this.AbstractLoader_constructor(t,!0,createjs.AbstractLoader.XML),this.resultFormatter=this._formatResult}var e=createjs.extend(t,createjs.AbstractLoader),i=t;i.canLoadItem=function(t){return t.type==createjs.AbstractLoader.XML},e._formatResult=function(t){return createjs.DataUtils.parseXML(t.getResult(!0),"text/xml")},createjs.XMLLoader=createjs.promote(t,"AbstractLoader")}(),this.createjs=this.createjs||{},function(){var t=createjs.SoundJS=createjs.SoundJS||{};t.version="0.6.0",t.buildDate="Thu, 11 Dec 2014 23:32:09 GMT"}(),this.createjs=this.createjs||{},createjs.indexOf=function(t,e){"use strict";for(var i=0,s=t.length;s>i;i++)if(e===t[i])return i;return-1},this.createjs=this.createjs||{},function(){"use strict";createjs.proxy=function(t,e){var i=Array.prototype.slice.call(arguments,2);return function(){return t.apply(e,Array.prototype.slice.call(arguments,0).concat(i))}}}(),this.createjs=this.createjs||{},function(){"use strict";var t=Object.defineProperty?!0:!1,e={};try{Object.defineProperty(e,"bar",{get:function(){return this._bar},set:function(t){this._bar=t}})}catch(i){t=!1}createjs.definePropertySupported=t}(),this.createjs=this.createjs||{},function(){"use strict";function t(){throw"Sound cannot be instantiated"}function e(t,e){this.init(t,e)}var i=t;i.INTERRUPT_ANY="any",i.INTERRUPT_EARLY="early",i.INTERRUPT_LATE="late",i.INTERRUPT_NONE="none",i.PLAY_INITED="playInited",i.PLAY_SUCCEEDED="playSucceeded",i.PLAY_INTERRUPTED="playInterrupted",i.PLAY_FINISHED="playFinished",i.PLAY_FAILED="playFailed",i.SUPPORTED_EXTENSIONS=["mp3","ogg","mpeg","wav","m4a","mp4","aiff","wma","mid"],i.EXTENSION_MAP={m4a:"mp4"},i.FILE_PATTERN=/^(?:(\w+:)\/{2}(\w+(?:\.\w+)*\/?))?([/.]*?(?:[^?]+)?\/)?((?:[^/?]+)\.(\w+))(?:\?(\S+)?)?$/,i.defaultInterruptBehavior=i.INTERRUPT_NONE,i.alternateExtensions=[],i.activePlugin=null,i._pluginsRegistered=!1,i._lastID=0,i._masterVolume=1,i._masterMute=!1,i._instances=[],i._idHash={},i._preloadHash={},i.addEventListener=null,i.removeEventListener=null,i.removeAllEventListeners=null,i.dispatchEvent=null,i.hasEventListener=null,i._listeners=null,createjs.EventDispatcher.initialize(i),i.getPreloadHandlers=function(){return{callback:createjs.proxy(i.initLoad,i),types:["sound"],extensions:i.SUPPORTED_EXTENSIONS}},i._handleLoadComplete=function(t){var e=t.target.getItem().src;if(i._preloadHash[e])for(var s=0,r=i._preloadHash[e].length;r>s;s++){var n=i._preloadHash[e][s];if(i._preloadHash[e][s]=!0,i.hasEventListener("fileload")){var t=new createjs.Event("fileload");t.src=n.src,t.id=n.id,t.data=n.data,t.sprite=n.sprite,i.dispatchEvent(t)}}},i._handleLoadError=function(t){var e=t.target.getItem().src;if(i._preloadHash[e])for(var s=0,r=i._preloadHash[e].length;r>s;s++){var n=i._preloadHash[e][s];if(i._preloadHash[e][s]=!1,i.hasEventListener("fileerror")){var t=new createjs.Event("fileerror");t.src=n.src,t.id=n.id,t.data=n.data,t.sprite=n.sprite,i.dispatchEvent(t)}}},i._registerPlugin=function(t){return t.isSupported()?(i.activePlugin=new t,!0):!1},i.registerPlugins=function(t){i._pluginsRegistered=!0;for(var e=0,s=t.length;s>e;e++)if(i._registerPlugin(t[e]))return!0;return!1},i.initializeDefaultPlugins=function(){return null!=i.activePlugin?!0:i._pluginsRegistered?!1:i.registerPlugins([createjs.WebAudioPlugin,createjs.HTMLAudioPlugin])?!0:!1},i.isReady=function(){return null!=i.activePlugin},i.getCapabilities=function(){return null==i.activePlugin?null:i.activePlugin._capabilities},i.getCapability=function(t){return null==i.activePlugin?null:i.activePlugin._capabilities[t]},i.initLoad=function(t){return i._registerSound(t)},i._registerSound=function(t){if(!i.initializeDefaultPlugins())return!1;var s=i._parsePath(t.src);if(null==s)return!1;t.src=s.src,t.type="sound";var r=t.data,n=i.activePlugin.defaultNumChannels||null;if(null!=r&&(isNaN(r.channels)?isNaN(r)||(n=parseInt(r)):n=parseInt(r.channels),r.audioSprite))for(var a,o=r.audioSprite.length;o--;)a=r.audioSprite[o],i._idHash[a.id]={src:t.src,startTime:parseInt(a.startTime),duration:parseInt(a.duration)};null!=t.id&&(i._idHash[t.id]={src:t.src});var h=i.activePlugin.register(t,n);return e.create(t.src,n),null!=r&&isNaN(r)?t.data.channels=n||e.maxPerChannel():t.data=n||e.maxPerChannel(),h.type&&(t.type=h.type),h},i.registerSound=function(t,e,s,r){var n={src:t,id:e,data:s};t instanceof Object&&(r=e,n=t),n=createjs.LoadItem.create(n),null!=r&&(n.src=r+t);var a=i._registerSound(n);if(!a)return!1;if(i._preloadHash[n.src]||(i._preloadHash[n.src]=[]),i._preloadHash[n.src].push(n),1==i._preloadHash[n.src].length)a.on("complete",createjs.proxy(this._handleLoadComplete,this)),a.on("error",createjs.proxy(this._handleLoadError,this)),i.activePlugin.preload(a);else if(1==i._preloadHash[n.src][0])return!0;return n},i.registerSounds=function(t,e){var i=[];t.path&&(e?e+=t.path:e=t.path);for(var s=0,r=t.length;r>s;s++)i[s]=createjs.Sound.registerSound(t[s].src,t[s].id,t[s].data,e);return i},i.registerManifest=function(t,e){try{console.log("createjs.Sound.registerManifest is deprecated, please use createjs.Sound.registerSounds.")}catch(i){}return this.registerSounds(t,e)},i.removeSound=function(t,s){if(null==i.activePlugin)return!1;t instanceof Object&&(t=t.src),t=i._getSrcById(t).src,null!=s&&(t=s+t);var r=i._parsePath(t);if(null==r)return!1;t=r.src;for(var n in i._idHash)i._idHash[n].src==t&&delete i._idHash[n];return e.removeSrc(t),delete i._preloadHash[t],i.activePlugin.removeSound(t),!0},i.removeSounds=function(t,e){var i=[];t.path&&(e?e+=t.path:e=t.path);for(var s=0,r=t.length;r>s;s++)i[s]=createjs.Sound.removeSound(t[s].src,e);return i},i.removeManifest=function(t,e){try{console.log("createjs.Sound.removeManifest is deprecated, please use createjs.Sound.removeSounds.")}catch(s){}return i.removeSounds(t,e)},i.removeAllSounds=function(){i._idHash={},i._preloadHash={},e.removeAll(),i.activePlugin&&i.activePlugin.removeAllSounds()},i.loadComplete=function(t){if(!i.isReady())return!1;var e=i._parsePath(t);return t=e?i._getSrcById(e.src).src:i._getSrcById(t).src,1==i._preloadHash[t][0]},i._parsePath=function(t){"string"!=typeof t&&(t=t.toString());var e=t.match(i.FILE_PATTERN);if(null==e)return!1;for(var s=e[4],r=e[5],n=i.getCapabilities(),a=0;!n[r];)if(r=i.alternateExtensions[a++],a>i.alternateExtensions.length)return null;t=t.replace("."+e[5],"."+r);var o={name:s,src:t,extension:r};return o},i.play=function(t,e,s,r,n,a,o,h,c){e instanceof Object&&(s=e.delay,r=e.offset,n=e.loop,a=e.volume,o=e.pan,h=e.startTime,c=e.duration,e=e.interrupt);var u=i.createInstance(t,h,c),l=i._playInstance(u,e,s,r,n,a,o);return l||u._playFailed(),u},i.createInstance=function(t,s,r){if(!i.initializeDefaultPlugins())return new createjs.DefaultSoundInstance(t,s,r);t=i._getSrcById(t);var n=i._parsePath(t.src),a=null;return null!=n&&null!=n.src?(e.create(n.src),null==s&&(s=t.startTime),a=i.activePlugin.create(n.src,s,r||t.duration)):a=new createjs.DefaultSoundInstance(t,s,r),a.uniqueId=i._lastID++,a},i.setVolume=function(t){if(null==Number(t))return!1;if(t=Math.max(0,Math.min(1,t)),i._masterVolume=t,!this.activePlugin||!this.activePlugin.setVolume||!this.activePlugin.setVolume(t))for(var e=this._instances,s=0,r=e.length;r>s;s++)e[s].setMasterVolume(t)},i.getVolume=function(){return i._masterVolume},i.setMute=function(t){if(null==t)return!1;if(this._masterMute=t,!this.activePlugin||!this.activePlugin.setMute||!this.activePlugin.setMute(t))for(var e=this._instances,i=0,s=e.length;s>i;i++)e[i].setMasterMute(t);return!0},i.getMute=function(){return this._masterMute},i.stop=function(){for(var t=this._instances,e=t.length;e--;)t[e].stop()},i._playInstance=function(t,e,s,r,n,a,o){if(e instanceof Object&&(s=e.delay,r=e.offset,n=e.loop,a=e.volume,o=e.pan,e=e.interrupt),e=e||i.defaultInterruptBehavior,null==s&&(s=0),null==r&&(r=t.getPosition()),null==n&&(n=t.loop),null==a&&(a=t.volume),null==o&&(o=t.pan),0==s){var h=i._beginPlaying(t,e,r,n,a,o);if(!h)return!1}else{var c=setTimeout(function(){i._beginPlaying(t,e,r,n,a,o)},s);t.delayTimeoutId=c}return this._instances.push(t),!0},i._beginPlaying=function(t,i,s,r,n,a){if(!e.add(t,i))return!1;var o=t._beginPlaying(s,r,n,a);if(!o){var h=createjs.indexOf(this._instances,t);return h>-1&&this._instances.splice(h,1),!1}return!0},i._getSrcById=function(t){return i._idHash[t]||{src:t}},i._playFinished=function(t){e.remove(t);var i=createjs.indexOf(this._instances,t);i>-1&&this._instances.splice(i,1)},createjs.Sound=t,e.channels={},e.create=function(t,i){var s=e.get(t);return null==s?(e.channels[t]=new e(t,i),!0):!1},e.removeSrc=function(t){var i=e.get(t);return null==i?!1:(i._removeAll(),delete e.channels[t],!0)},e.removeAll=function(){for(var t in e.channels)e.channels[t]._removeAll();e.channels={}},e.add=function(t,i){var s=e.get(t.src);return null==s?!1:s._add(t,i)},e.remove=function(t){var i=e.get(t.src);return null==i?!1:(i._remove(t),!0)},e.maxPerChannel=function(){return s.maxDefault},e.get=function(t){return e.channels[t]};var s=e.prototype;s.constructor=e,s.src=null,s.max=null,s.maxDefault=100,s.length=0,s.init=function(t,e){this.src=t,this.max=e||this.maxDefault,-1==this.max&&(this.max=this.maxDefault),this._instances=[]},s._get=function(t){return this._instances[t]},s._add=function(t,e){return this._getSlot(e,t)?(this._instances.push(t),this.length++,!0):!1},s._remove=function(t){var e=createjs.indexOf(this._instances,t);return-1==e?!1:(this._instances.splice(e,1),this.length--,!0)},s._removeAll=function(){for(var t=this.length-1;t>=0;t--)this._instances[t].stop()},s._getSlot=function(e){var i,s;if(e!=t.INTERRUPT_NONE&&(s=this._get(0),null==s))return!0;for(var r=0,n=this.max;n>r;r++){if(i=this._get(r),null==i)return!0;if(i.playState==t.PLAY_FINISHED||i.playState==t.PLAY_INTERRUPTED||i.playState==t.PLAY_FAILED){s=i;break}e!=t.INTERRUPT_NONE&&(e==t.INTERRUPT_EARLY&&i.getPosition()<s.getPosition()||e==t.INTERRUPT_LATE&&i.getPosition()>s.getPosition())&&(s=i)}return null!=s?(s._interrupt(),this._remove(s),!0):!1},s.toString=function(){return"[Sound SoundChannel]"}}(),this.createjs=this.createjs||{},function(){"use strict";var t=function(t,e,i,s){this.EventDispatcher_constructor(),this.src=t,this.uniqueId=-1,this.playState=null,this.delayTimeoutId=null,this._startTime=Math.max(0,e||0),this._volume=1,createjs.definePropertySupported&&Object.defineProperty(this,"volume",{get:this.getVolume,set:this.setVolume}),this._pan=0,createjs.definePropertySupported&&Object.defineProperty(this,"pan",{get:this.getPan,set:this.setPan}),this._duration=Math.max(0,i||0),createjs.definePropertySupported&&Object.defineProperty(this,"duration",{get:this.getDuration,set:this.setDuration}),this._playbackResource=null,createjs.definePropertySupported&&Object.defineProperty(this,"playbackResource",{get:this.getPlaybackResource,set:this.setPlaybackResource}),s!==!1&&s!==!0&&this.setPlaybackResource(s),this._position=0,createjs.definePropertySupported&&Object.defineProperty(this,"position",{get:this.getPosition,set:this.setPosition}),this._loop=0,createjs.definePropertySupported&&Object.defineProperty(this,"loop",{get:this.getLoop,set:this.setLoop}),this._muted=!1,createjs.definePropertySupported&&Object.defineProperty(this,"muted",{get:this.getMuted,set:this.setMuted}),this._paused=!1,createjs.definePropertySupported&&Object.defineProperty(this,"paused",{get:this.getPaused,set:this.setPaused})},e=createjs.extend(t,createjs.EventDispatcher);e.play=function(t,e,i,s,r,n){return this.playState==createjs.Sound.PLAY_SUCCEEDED?(t instanceof Object&&(i=t.offset,s=t.loop,r=t.volume,n=t.pan),null!=i&&this.setPosition(i),null!=s&&this.setLoop(s),null!=r&&this.setVolume(r),null!=n&&this.setPan(n),void(this._paused&&this.setPaused(!1))):(this._cleanUp(),createjs.Sound._playInstance(this,t,e,i,s,r,n),this)},e.pause=function(){return this._paused||this.playState!=createjs.Sound.PLAY_SUCCEEDED?!1:(this.setPaused(!0),!0)},e.resume=function(){return this._paused?(this.setPaused(!1),!0):!1},e.stop=function(){return this._position=0,this._paused=!1,this._handleStop(),this._cleanUp(),this.playState=createjs.Sound.PLAY_FINISHED,this},e.destroy=function(){this._cleanUp(),this.src=null,this.playbackResource=null,this.removeAllEventListeners()},e.toString=function(){return"[AbstractSoundInstance]"},e.getPaused=function(){return this._paused},e.setPaused=function(t){return t!==!0&&t!==!1||this._paused==t||1==t&&this.playState!=createjs.Sound.PLAY_SUCCEEDED?void 0:(this._paused=t,t?this._pause():this._resume(),clearTimeout(this.delayTimeoutId),this)},e.setVolume=function(t){return t==this._volume?this:(this._volume=Math.max(0,Math.min(1,t)),this._muted||this._updateVolume(),this)},e.getVolume=function(){return this._volume},e.setMute=function(t){this.setMuted(t)},e.getMute=function(){return this._muted},e.setMuted=function(t){return t===!0||t===!1?(this._muted=t,this._updateVolume(),this):void 0},e.getMuted=function(){return this._muted},e.setPan=function(t){return t==this._pan?this:(this._pan=Math.max(-1,Math.min(1,t)),this._updatePan(),this)},e.getPan=function(){return this._pan},e.getPosition=function(){return this._paused||this.playState!=createjs.Sound.PLAY_SUCCEEDED?this._position:this._calculateCurrentPosition()},e.setPosition=function(t){return this._position=Math.max(0,t),this.playState==createjs.Sound.PLAY_SUCCEEDED&&this._updatePosition(),this},e.getDuration=function(){return this._duration},e.setDuration=function(t){return t==this._duration?this:(this._duration=Math.max(0,t||0),this._updateDuration(),this)},e.setPlaybackResource=function(t){return this._playbackResource=t,0==this._duration&&this._setDurationFromSource(),this},e.getPlaybackResource=function(){return this._playbackResource},e.getLoop=function(){return this._loop},e.setLoop=function(t){null!=this._playbackResource&&(0!=this._loop&&0==t&&this._removeLooping(t),0==this._loop&&0!=t&&this._addLooping(t)),this._loop=t},e._sendEvent=function(t){var e=new createjs.Event(t);this.dispatchEvent(e)},e._cleanUp=function(){clearTimeout(this.delayTimeoutId),this._handleCleanUp(),this._paused=!1,createjs.Sound._playFinished(this)},e._interrupt=function(){this._cleanUp(),this.playState=createjs.Sound.PLAY_INTERRUPTED,this._sendEvent("interrupted")},e._beginPlaying=function(t,e,i,s){return this.setPosition(t),this.setLoop(e),this.setVolume(i),this.setPan(s),null!=this._playbackResource&&this._position<this._duration?(this._paused=!1,this._handleSoundReady(),this.playState=createjs.Sound.PLAY_SUCCEEDED,this._sendEvent("succeeded"),!0):(this._playFailed(),!1)},e._playFailed=function(){this._cleanUp(),this.playState=createjs.Sound.PLAY_FAILED,this._sendEvent("failed")},e._handleSoundComplete=function(){return this._position=0,0!=this._loop?(this._loop--,this._handleLoop(),void this._sendEvent("loop")):(this._cleanUp(),this.playState=createjs.Sound.PLAY_FINISHED,void this._sendEvent("complete"))},e._handleSoundReady=function(){},e._updateVolume=function(){},e._updatePan=function(){},e._updateDuration=function(){},e._setDurationFromSource=function(){},e._calculateCurrentPosition=function(){},e._updatePosition=function(){},e._removeLooping=function(){},e._addLooping=function(){},e._pause=function(){},e._resume=function(){},e._handleStop=function(){},e._handleCleanUp=function(){},e._handleLoop=function(){},createjs.AbstractSoundInstance=createjs.promote(t,"EventDispatcher"),createjs.DefaultSoundInstance=createjs.AbstractSoundInstance}(),this.createjs=this.createjs||{},function(){"use strict";var t=function(){this._capabilities=null,this._loaders={},this._audioSources={},this._soundInstances={},this._loaderClass,this._soundInstanceClass},e=t.prototype;t._capabilities=null,t.isSupported=function(){return!0},e.register=function(t){if(this._audioSources[t.src]=!0,this._soundInstances[t.src]=[],this._loaders[t.src])return this._loaders[t.src];var e=new this._loaderClass(t);return e.on("complete",createjs.proxy(this._handlePreloadComplete,this)),this._loaders[t.src]=e,e},e.preload=function(t){t.on("error",createjs.proxy(this._handlePreloadError,this)),t.load()},e.isPreloadStarted=function(t){return null!=this._audioSources[t]},e.isPreloadComplete=function(t){return!(null==this._audioSources[t]||1==this._audioSources[t])},e.removeSound=function(t){if(this._soundInstances[t]){for(var e=this._soundInstances[t].length;e--;){var i=this._soundInstances[t][e];i.destroy()}delete this._soundInstances[t],delete this._audioSources[t],this._loaders[t]&&this._loaders[t].destroy(),delete this._loaders[t]}},e.removeAllSounds=function(){for(var t in this._audioSources)this.removeSound(t)},e.create=function(t,e,i){this.isPreloadStarted(t)||this.preload(this.register(t));var s=new this._soundInstanceClass(t,e,i,this._audioSources[t]);return this._soundInstances[t].push(s),s},e.setVolume=function(t){return this._volume=t,this._updateVolume(),!0},e.getVolume=function(){return this._volume},e.setMute=function(){return this._updateVolume(),!0},e.toString=function(){return"[AbstractPlugin]"},e._handlePreloadComplete=function(t){var e=t.target.getItem().src;this._audioSources[e]=t.result;for(var i=0,s=this._soundInstances[e].length;s>i;i++){var r=this._soundInstances[e][i];r.setPlaybackResource(this._audioSources[e])}},e._handlePreloadError=function(){},e._updateVolume=function(){},createjs.AbstractPlugin=t}(),this.createjs=this.createjs||{},function(){"use strict";function t(t){this.AbstractLoader_constructor(t,!0,createjs.AbstractLoader.SOUND)}var e=createjs.extend(t,createjs.AbstractLoader);t.context=null,e.toString=function(){return"[WebAudioLoader]"},e._createRequest=function(){this._request=new createjs.XHRRequest(this._item,!1),this._request.setResponseType("arraybuffer")},e._sendComplete=function(){t.context.decodeAudioData(this._rawResult,createjs.proxy(this._handleAudioDecoded,this),createjs.proxy(this._handleError,this))},e._handleAudioDecoded=function(t){this._result=t,this.AbstractLoader__sendComplete()},createjs.WebAudioLoader=createjs.promote(t,"AbstractLoader")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e,s,r){this.AbstractSoundInstance_constructor(t,e,s,r),this.gainNode=i.context.createGain(),this.panNode=i.context.createPanner(),this.panNode.panningModel=i._panningModel,this.panNode.connect(this.gainNode),this.sourceNode=null,this._soundCompleteTimeout=null,this._sourceNodeNext=null,this._playbackStartTime=0,this._endedHandler=createjs.proxy(this._handleSoundComplete,this)}var e=createjs.extend(t,createjs.AbstractSoundInstance),i=t;i.context=null,i.destinationNode=null,i._panningModel="equalpower",e.destroy=function(){this.AbstractSoundInstance_destroy(),this.panNode.disconnect(0),this.panNode=null,this.gainNode.disconnect(0),this.gainNode=null},e.toString=function(){return"[WebAudioSoundInstance]"},e._updatePan=function(){this.panNode.setPosition(this._pan,0,-.5)},e._removeLooping=function(){this._sourceNodeNext=this._cleanUpAudioNode(this._sourceNodeNext)},e._addLooping=function(){this.playState==createjs.Sound.PLAY_SUCCEEDED&&(this._sourceNodeNext=this._createAndPlayAudioNode(this._playbackStartTime,0))},e._setDurationFromSource=function(){this._duration=1e3*this.playbackResource.duration},e._handleCleanUp=function(){this.sourceNode&&this.playState==createjs.Sound.PLAY_SUCCEEDED&&(this.sourceNode=this._cleanUpAudioNode(this.sourceNode),this._sourceNodeNext=this._cleanUpAudioNode(this._sourceNodeNext)),0!=this.gainNode.numberOfOutputs&&this.gainNode.disconnect(0),clearTimeout(this._soundCompleteTimeout),this._playbackStartTime=0},e._cleanUpAudioNode=function(t){return t&&(t.stop(0),t.disconnect(0),t=null),t},e._handleSoundReady=function(){this.gainNode.connect(i.destinationNode);var t=.001*this._duration,e=.001*this._position;this.sourceNode=this._createAndPlayAudioNode(i.context.currentTime-t,e),this._playbackStartTime=this.sourceNode.startTime-e,this._soundCompleteTimeout=setTimeout(this._endedHandler,1e3*(t-e)),0!=this._loop&&(this._sourceNodeNext=this._createAndPlayAudioNode(this._playbackStartTime,0))},e._createAndPlayAudioNode=function(t,e){var s=i.context.createBufferSource();s.buffer=this.playbackResource,s.connect(this.panNode);var r=.001*this._duration;return s.startTime=t+r,s.start(s.startTime,e+.001*this._startTime,r-e),s},e._pause=function(){this._position=1e3*(i.context.currentTime-this._playbackStartTime),this.sourceNode=this._cleanUpAudioNode(this.sourceNode),this._sourceNodeNext=this._cleanUpAudioNode(this._sourceNodeNext),0!=this.gainNode.numberOfOutputs&&this.gainNode.disconnect(0),clearTimeout(this._soundCompleteTimeout)},e._resume=function(){this._handleSoundReady()},e._updateVolume=function(){var t=this._muted?0:this._volume;t!=this.gainNode.gain.value&&(this.gainNode.gain.value=t)},e._calculateCurrentPosition=function(){return 1e3*(i.context.currentTime-this._playbackStartTime)},e._updatePosition=function(){this.sourceNode=this._cleanUpAudioNode(this.sourceNode),this._sourceNodeNext=this._cleanUpAudioNode(this._sourceNodeNext),clearTimeout(this._soundCompleteTimeout),this._paused||this._handleSoundReady()},e._handleLoop=function(){this._cleanUpAudioNode(this.sourceNode),this.sourceNode=this._sourceNodeNext,this._playbackStartTime=this.sourceNode.startTime,this._sourceNodeNext=this._createAndPlayAudioNode(this._playbackStartTime,0),this._soundCompleteTimeout=setTimeout(this._endedHandler,this._duration)},e._updateDuration=function(){this._pause(),this._resume()},createjs.WebAudioSoundInstance=createjs.promote(t,"AbstractSoundInstance")}(),this.createjs=this.createjs||{},function(){"use strict";function t(){this.AbstractPlugin_constructor(),this._panningModel=i._panningModel,this._volume=1,this.context=i.context,this.dynamicsCompressorNode=this.context.createDynamicsCompressor(),this.dynamicsCompressorNode.connect(this.context.destination),this.gainNode=this.context.createGain(),this.gainNode.connect(this.dynamicsCompressorNode),createjs.WebAudioSoundInstance.destinationNode=this.gainNode,this._capabilities=i._capabilities,this._loaderClass=createjs.WebAudioLoader,this._soundInstanceClass=createjs.WebAudioSoundInstance,this._addPropsToClasses()}var e=createjs.extend(t,createjs.AbstractPlugin),i=t;i._capabilities=null,i._panningModel="equalpower",i.context=null,i.isSupported=function(){var t=createjs.BrowserDetect.isIOS||createjs.BrowserDetect.isAndroid||createjs.BrowserDetect.isBlackberry;return"file:"!=location.protocol||t||this._isFileXHRSupported()?(i._generateCapabilities(),null==i.context?!1:!0):!1},i.playEmptySound=function(){var t=i.context.createBufferSource();t.buffer=i.context.createBuffer(1,1,22050),t.connect(i.context.destination),t.start(0,0,0)},i._isFileXHRSupported=function(){var t=!0,e=new XMLHttpRequest;try{e.open("GET","WebAudioPluginTest.fail",!1)}catch(i){return t=!1}e.onerror=function(){t=!1},e.onload=function(){t=404==this.status||200==this.status||0==this.status&&""!=this.response};try{e.send()}catch(i){t=!1}return t},i._generateCapabilities=function(){if(null==i._capabilities){var t=document.createElement("audio");if(null==t.canPlayType)return null;if(null==i.context)if(window.AudioContext)i.context=new AudioContext;else{if(!window.webkitAudioContext)return null;i.context=new webkitAudioContext}i._compatibilitySetUp(),i.playEmptySound(),i._capabilities={panning:!0,volume:!0,tracks:-1};for(var e=createjs.Sound.SUPPORTED_EXTENSIONS,s=createjs.Sound.EXTENSION_MAP,r=0,n=e.length;n>r;r++){var a=e[r],o=s[a]||a;i._capabilities[a]="no"!=t.canPlayType("audio/"+a)&&""!=t.canPlayType("audio/"+a)||"no"!=t.canPlayType("audio/"+o)&&""!=t.canPlayType("audio/"+o)}i.context.destination.numberOfChannels<2&&(i._capabilities.panning=!1)}},i._compatibilitySetUp=function(){if(i._panningModel="equalpower",!i.context.createGain){i.context.createGain=i.context.createGainNode;var t=i.context.createBufferSource();t.__proto__.start=t.__proto__.noteGrainOn,t.__proto__.stop=t.__proto__.noteOff,i._panningModel=0}},e.toString=function(){return"[WebAudioPlugin]"},e._addPropsToClasses=function(){var t=this._soundInstanceClass;t.context=this.context,t.destinationNode=this.gainNode,t._panningModel=this._panningModel,this._loaderClass.context=this.context},e._updateVolume=function(){var t=createjs.Sound._masterMute?0:this._volume;t!=this.gainNode.gain.value&&(this.gainNode.gain.value=t)},createjs.WebAudioPlugin=createjs.promote(t,"AbstractPlugin")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t){this.src=t,this.length=0,this.available=0,this.tags=[],this.duration=0}var e=t.prototype;e.constructor=t;var i=t;i.tags={},i.get=function(e){var s=i.tags[e];return null==s&&(s=i.tags[e]=new t(e)),s},i.remove=function(t){var e=i.tags[t];return null==e?!1:(e.removeAll(),delete i.tags[t],!0)},i.getInstance=function(t){var e=i.tags[t];return null==e?null:e.get()},i.setInstance=function(t,e){var s=i.tags[t];return null==s?null:s.set(e)},i.getDuration=function(t){var e=i.tags[t];return null==e?0:e.getDuration()},e.add=function(t){this.tags.push(t),this.length++,this.available++},e.removeAll=function(){for(var t;this.length--;)t=this.tags[this.length],t.parentNode&&t.parentNode.removeChild(t),delete this.tags[this.length];this.src=null,this.tags.length=0},e.get=function(){if(0==this.tags.length)return null;this.available=this.tags.length;var t=this.tags.pop();return null==t.parentNode&&document.body.appendChild(t),t},e.set=function(t){var e=createjs.indexOf(this.tags,t);-1==e&&this.tags.push(t),this.available=this.tags.length},e.getDuration=function(){return this.duration||(this.duration=1e3*this.tags[this.tags.length-1].duration),this.duration},e.toString=function(){return"[HTMLAudioTagPool]"},createjs.HTMLAudioTagPool=t}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e,i,s){this.AbstractSoundInstance_constructor(t,e,i,s),this._audioSpriteStopTime=null,this._delayTimeoutId=null,this._endedHandler=createjs.proxy(this._handleSoundComplete,this),this._readyHandler=createjs.proxy(this._handleTagReady,this),this._stalledHandler=createjs.proxy(this.playFailed,this),this._audioSpriteEndHandler=createjs.proxy(this._handleAudioSpriteLoop,this),this._loopHandler=createjs.proxy(this._handleSoundComplete,this),i?this._audioSpriteStopTime=.001*(e+i):this._duration=createjs.HTMLAudioTagPool.getDuration(this.src)}var e=createjs.extend(t,createjs.AbstractSoundInstance);e.setMasterVolume=function(){this._updateVolume()},e.setMasterMute=function(){this._updateVolume()},e.toString=function(){return"[HTMLAudioSoundInstance]"},e._removeLooping=function(){null!=this._playbackResource&&(this._playbackResource.loop=!1,this._playbackResource.removeEventListener(createjs.HTMLAudioPlugin._AUDIO_SEEKED,this._loopHandler,!1))},e._addLooping=function(){null==this._playbackResource||this._audioSpriteStopTime||(this._playbackResource.addEventListener(createjs.HTMLAudioPlugin._AUDIO_SEEKED,this._loopHandler,!1),this._playbackResource.loop=!0)},e._handleCleanUp=function(){var t=this._playbackResource;if(null!=t){t.pause(),t.loop=!1,t.removeEventListener(createjs.HTMLAudioPlugin._AUDIO_ENDED,this._endedHandler,!1),t.removeEventListener(createjs.HTMLAudioPlugin._AUDIO_READY,this._readyHandler,!1),t.removeEventListener(createjs.HTMLAudioPlugin._AUDIO_STALLED,this._stalledHandler,!1),t.removeEventListener(createjs.HTMLAudioPlugin._AUDIO_SEEKED,this._loopHandler,!1),t.removeEventListener(createjs.HTMLAudioPlugin._TIME_UPDATE,this._audioSpriteEndHandler,!1);try{t.currentTime=this._startTime}catch(e){}createjs.HTMLAudioTagPool.setInstance(this.src,t),this._playbackResource=null}},e._beginPlaying=function(t,e,i,s){return this._playbackResource=createjs.HTMLAudioTagPool.getInstance(this.src),this.AbstractSoundInstance__beginPlaying(t,e,i,s)},e._handleSoundReady=function(){if(4!==this._playbackResource.readyState){var t=this._playbackResource;return t.addEventListener(createjs.HTMLAudioPlugin._AUDIO_READY,this._readyHandler,!1),t.addEventListener(createjs.HTMLAudioPlugin._AUDIO_STALLED,this._stalledHandler,!1),t.preload="auto",
void t.load()}this._updateVolume(),this._playbackResource.currentTime=.001*(this._startTime+this._position),this._audioSpriteStopTime?this._playbackResource.addEventListener(createjs.HTMLAudioPlugin._TIME_UPDATE,this._audioSpriteEndHandler,!1):(this._playbackResource.addEventListener(createjs.HTMLAudioPlugin._AUDIO_ENDED,this._endedHandler,!1),0!=this._loop&&(this._playbackResource.addEventListener(createjs.HTMLAudioPlugin._AUDIO_SEEKED,this._loopHandler,!1),this._playbackResource.loop=!0)),this._playbackResource.play()},e._handleTagReady=function(){this._playbackResource.removeEventListener(createjs.HTMLAudioPlugin._AUDIO_READY,this._readyHandler,!1),this._playbackResource.removeEventListener(createjs.HTMLAudioPlugin._AUDIO_STALLED,this._stalledHandler,!1),this._handleSoundReady()},e._pause=function(){this._playbackResource.pause()},e._resume=function(){this._playbackResource.play()},e._updateVolume=function(){if(null!=this._playbackResource){var t=this._muted||createjs.Sound._masterMute?0:this._volume*createjs.Sound._masterVolume;t!=this._playbackResource.volume&&(this._playbackResource.volume=t)}},e._calculateCurrentPosition=function(){return 1e3*this._playbackResource.currentTime-this._startTime},e._updatePosition=function(){this._playbackResource.removeEventListener(createjs.HTMLAudioPlugin._AUDIO_SEEKED,this._loopHandler,!1),this._playbackResource.addEventListener(createjs.HTMLAudioPlugin._AUDIO_SEEKED,this._handleSetPositionSeek,!1);try{this._playbackResource.currentTime=.001*(this._position+this._startTime)}catch(t){this._handleSetPositionSeek(null)}},e._handleSetPositionSeek=function(){null!=this._playbackResource&&(this._playbackResource.removeEventListener(createjs.HTMLAudioPlugin._AUDIO_SEEKED,this._handleSetPositionSeek,!1),this._playbackResource.addEventListener(createjs.HTMLAudioPlugin._AUDIO_SEEKED,this._loopHandler,!1))},e._handleAudioSpriteLoop=function(){this._playbackResource.currentTime<=this._audioSpriteStopTime||(this._playbackResource.pause(),0==this._loop?this._handleSoundComplete(null):(this._position=0,this._loop--,this._playbackResource.currentTime=.001*this._startTime,this._paused||this._playbackResource.play(),this._sendEvent("loop")))},e._handleLoop=function(){0==this._loop&&(this._playbackResource.loop=!1,this._playbackResource.removeEventListener(createjs.HTMLAudioPlugin._AUDIO_SEEKED,this._loopHandler,!1))},e._updateDuration=function(){this._audioSpriteStopTime=.001*(startTime+duration),this.playState==createjs.Sound.PLAY_SUCCEEDED&&(this._playbackResource.removeEventListener(createjs.HTMLAudioPlugin._AUDIO_ENDED,this._endedHandler,!1),this._playbackResource.addEventListener(createjs.HTMLAudioPlugin._TIME_UPDATE,this._audioSpriteEndHandler,!1))},createjs.HTMLAudioSoundInstance=createjs.promote(t,"AbstractSoundInstance")}(),this.createjs=this.createjs||{},function(){"use strict";function t(){this.AbstractPlugin_constructor(),this.defaultNumChannels=2,this._capabilities=i._capabilities,this._loaderClass=createjs.SoundLoader,this._soundInstanceClass=createjs.HTMLAudioSoundInstance}var e=createjs.extend(t,createjs.AbstractPlugin),i=t;i.MAX_INSTANCES=30,i._AUDIO_READY="canplaythrough",i._AUDIO_ENDED="ended",i._AUDIO_SEEKED="seeked",i._AUDIO_STALLED="stalled",i._TIME_UPDATE="timeupdate",i._capabilities=null,i.enableIOS=!1,i.isSupported=function(){return i._generateCapabilities(),null==i._capabilities?!1:!0},i._generateCapabilities=function(){if(null==i._capabilities){var t=document.createElement("audio");if(null==t.canPlayType)return null;i._capabilities={panning:!0,volume:!0,tracks:-1};for(var e=createjs.Sound.SUPPORTED_EXTENSIONS,s=createjs.Sound.EXTENSION_MAP,r=0,n=e.length;n>r;r++){var a=e[r],o=s[a]||a;i._capabilities[a]="no"!=t.canPlayType("audio/"+a)&&""!=t.canPlayType("audio/"+a)||"no"!=t.canPlayType("audio/"+o)&&""!=t.canPlayType("audio/"+o)}}},e.register=function(t,e){for(var i=createjs.HTMLAudioTagPool.get(t.src),s=null,r=0;e>r;r++)s=this._createTag(t.src),i.add(s);var n=this.AbstractPlugin_register(t,e);return n.setTag(s),n},e.removeSound=function(t){this.AbstractPlugin_removeSound(t),createjs.HTMLAudioTagPool.remove(t)},e.create=function(t,e,i){var s=this.AbstractPlugin_create(t,e,i);return s.setPlaybackResource(null),s},e.toString=function(){return"[HTMLAudioPlugin]"},e.setVolume=e.getVolume=e.setMute=null,e._createTag=function(t){var e=document.createElement("audio");return e.autoplay=!1,e.preload="none",e.src=t,e},createjs.HTMLAudioPlugin=createjs.promote(t,"AbstractPlugin")}(),this.createjs=this.createjs||{},function(){"use strict";function t(e,i,s){this.ignoreGlobalPause=!1,this.loop=!1,this.duration=0,this.pluginData=s||{},this.target=e,this.position=null,this.passive=!1,this._paused=!1,this._curQueueProps={},this._initQueueProps={},this._steps=[],this._actions=[],this._prevPosition=0,this._stepPosition=0,this._prevPos=-1,this._target=e,this._useTicks=!1,this._inited=!1,i&&(this._useTicks=i.useTicks,this.ignoreGlobalPause=i.ignoreGlobalPause,this.loop=i.loop,i.onChange&&this.addEventListener("change",i.onChange),i.override&&t.removeTweens(e)),i&&i.paused?this._paused=!0:createjs.Tween._register(this,!0),i&&null!=i.position&&this.setPosition(i.position,t.NONE)}var e=createjs.extend(t,createjs.EventDispatcher);t.NONE=0,t.LOOP=1,t.REVERSE=2,t.IGNORE={},t._tweens=[],t._plugins={},t.get=function(e,i,s,r){return r&&t.removeTweens(e),new t(e,i,s)},t.tick=function(e,i){for(var s=t._tweens.slice(),r=s.length-1;r>=0;r--){var n=s[r];i&&!n.ignoreGlobalPause||n._paused||n.tick(n._useTicks?1:e)}},t.handleEvent=function(t){"tick"==t.type&&this.tick(t.delta,t.paused)},t.removeTweens=function(e){if(e.tweenjs_count){for(var i=t._tweens,s=i.length-1;s>=0;s--){var r=i[s];r._target==e&&(r._paused=!0,i.splice(s,1))}e.tweenjs_count=0}},t.removeAllTweens=function(){for(var e=t._tweens,i=0,s=e.length;s>i;i++){var r=e[i];r._paused=!0,r.target.tweenjs_count=0}e.length=0},t.hasActiveTweens=function(e){return e?e.tweenjs_count:t._tweens&&!!t._tweens.length},t.installPlugin=function(e,i){var s=e.priority;null==s&&(e.priority=s=0);for(var r=0,n=i.length,a=t._plugins;n>r;r++){var o=i[r];if(a[o]){for(var h=a[o],c=0,u=h.length;u>c&&!(s<h[c].priority);c++);a[o].splice(c,0,e)}else a[o]=[e]}},t._register=function(e,i){var s=e._target,r=t._tweens;if(i)s&&(s.tweenjs_count=s.tweenjs_count?s.tweenjs_count+1:1),r.push(e),!t._inited&&createjs.Ticker&&(createjs.Ticker.addEventListener("tick",t),t._inited=!0);else{s&&s.tweenjs_count--;for(var n=r.length;n--;)if(r[n]==e)return void r.splice(n,1)}},e.wait=function(t,e){if(null==t||0>=t)return this;var i=this._cloneProps(this._curQueueProps);return this._addStep({d:t,p0:i,e:this._linearEase,p1:i,v:e})},e.to=function(t,e,i){return(isNaN(e)||0>e)&&(e=0),this._addStep({d:e||0,p0:this._cloneProps(this._curQueueProps),e:i,p1:this._cloneProps(this._appendQueueProps(t))})},e.call=function(t,e,i){return this._addAction({f:t,p:e?e:[this],o:i?i:this._target})},e.set=function(t,e){return this._addAction({f:this._set,o:this,p:[t,e?e:this._target]})},e.play=function(t){return t||(t=this),this.call(t.setPaused,[!1],t)},e.pause=function(t){return t||(t=this),this.call(t.setPaused,[!0],t)},e.setPosition=function(t,e){0>t&&(t=0),null==e&&(e=1);var i=t,s=!1;if(i>=this.duration&&(this.loop?i%=this.duration:(i=this.duration,s=!0)),i==this._prevPos)return s;var r=this._prevPos;if(this.position=this._prevPos=i,this._prevPosition=t,this._target)if(s)this._updateTargetProps(null,1);else if(this._steps.length>0){for(var n=0,a=this._steps.length;a>n&&!(this._steps[n].t>i);n++);var o=this._steps[n-1];this._updateTargetProps(o,(this._stepPosition=i-o.t)/o.d)}return 0!=e&&this._actions.length>0&&(this._useTicks?this._runActions(i,i):1==e&&r>i?(r!=this.duration&&this._runActions(r,this.duration),this._runActions(0,i,!0)):this._runActions(r,i)),s&&this.setPaused(!0),this.dispatchEvent("change"),s},e.tick=function(t){this._paused||this.setPosition(this._prevPosition+t)},e.setPaused=function(e){return this._paused===!!e?this:(this._paused=!!e,t._register(this,!e),this)},e.w=e.wait,e.t=e.to,e.c=e.call,e.s=e.set,e.toString=function(){return"[Tween]"},e.clone=function(){throw"Tween can not be cloned."},e._updateTargetProps=function(e,i){var s,r,n,a,o,h;if(e||1!=i){if(this.passive=!!e.v,this.passive)return;e.e&&(i=e.e(i,0,1,1)),s=e.p0,r=e.p1}else this.passive=!1,s=r=this._curQueueProps;for(var c in this._initQueueProps){null==(a=s[c])&&(s[c]=a=this._initQueueProps[c]),null==(o=r[c])&&(r[c]=o=a),n=a==o||0==i||1==i||"number"!=typeof a?1==i?o:a:a+(o-a)*i;var u=!1;if(h=t._plugins[c])for(var l=0,d=h.length;d>l;l++){var _=h[l].tween(this,c,n,s,r,i,!!e&&s==r,!e);_==t.IGNORE?u=!0:n=_}u||(this._target[c]=n)}},e._runActions=function(t,e,i){var s=t,r=e,n=-1,a=this._actions.length,o=1;for(t>e&&(s=e,r=t,n=a,a=o=-1);(n+=o)!=a;){var h=this._actions[n],c=h.t;(c==r||c>s&&r>c||i&&c==t)&&h.f.apply(h.o,h.p)}},e._appendQueueProps=function(e){var i,s,r,n,a;for(var o in e)if(void 0===this._initQueueProps[o]){if(s=this._target[o],i=t._plugins[o])for(r=0,n=i.length;n>r;r++)s=i[r].init(this,o,s);this._initQueueProps[o]=this._curQueueProps[o]=void 0===s?null:s}else s=this._curQueueProps[o];for(var o in e){if(s=this._curQueueProps[o],i=t._plugins[o])for(a=a||{},r=0,n=i.length;n>r;r++)i[r].step&&i[r].step(this,o,s,e[o],a);this._curQueueProps[o]=e[o]}return a&&this._appendQueueProps(a),this._curQueueProps},e._cloneProps=function(t){var e={};for(var i in t)e[i]=t[i];return e},e._addStep=function(t){return t.d>0&&(this._steps.push(t),t.t=this.duration,this.duration+=t.d),this},e._addAction=function(t){return t.t=this.duration,this._actions.push(t),this},e._set=function(t,e){for(var i in t)e[i]=t[i]},createjs.Tween=createjs.promote(t,"EventDispatcher")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e,i){this.EventDispatcher_constructor(),this.ignoreGlobalPause=!1,this.duration=0,this.loop=!1,this.position=null,this._paused=!1,this._tweens=[],this._labels=null,this._labelList=null,this._prevPosition=0,this._prevPos=-1,this._useTicks=!1,i&&(this._useTicks=i.useTicks,this.loop=i.loop,this.ignoreGlobalPause=i.ignoreGlobalPause,i.onChange&&this.addEventListener("change",i.onChange)),t&&this.addTween.apply(this,t),this.setLabels(e),i&&i.paused?this._paused=!0:createjs.Tween._register(this,!0),i&&null!=i.position&&this.setPosition(i.position,createjs.Tween.NONE)}var e=createjs.extend(t,createjs.EventDispatcher);e.addTween=function(t){var e=arguments.length;if(e>1){for(var i=0;e>i;i++)this.addTween(arguments[i]);return arguments[0]}return 0==e?null:(this.removeTween(t),this._tweens.push(t),t.setPaused(!0),t._paused=!1,t._useTicks=this._useTicks,t.duration>this.duration&&(this.duration=t.duration),this._prevPos>=0&&t.setPosition(this._prevPos,createjs.Tween.NONE),t)},e.removeTween=function(t){var e=arguments.length;if(e>1){for(var i=!0,s=0;e>s;s++)i=i&&this.removeTween(arguments[s]);return i}if(0==e)return!1;for(var r=this._tweens,s=r.length;s--;)if(r[s]==t)return r.splice(s,1),t.duration>=this.duration&&this.updateDuration(),!0;return!1},e.addLabel=function(t,e){this._labels[t]=e;var i=this._labelList;if(i){for(var s=0,r=i.length;r>s&&!(e<i[s].position);s++);i.splice(s,0,{label:t,position:e})}},e.setLabels=function(t){this._labels=t?t:{}},e.getLabels=function(){var t=this._labelList;if(!t){t=this._labelList=[];var e=this._labels;for(var i in e)t.push({label:i,position:e[i]});t.sort(function(t,e){return t.position-e.position})}return t},e.getCurrentLabel=function(){var t=this.getLabels(),e=this.position,i=t.length;if(i){for(var s=0;i>s&&!(e<t[s].position);s++);return 0==s?null:t[s-1].label}return null},e.gotoAndPlay=function(t){this.setPaused(!1),this._goto(t)},e.gotoAndStop=function(t){this.setPaused(!0),this._goto(t)},e.setPosition=function(t,e){0>t&&(t=0);var i=this.loop?t%this.duration:t,s=!this.loop&&t>=this.duration;if(i==this._prevPos)return s;this._prevPosition=t,this.position=this._prevPos=i;for(var r=0,n=this._tweens.length;n>r;r++)if(this._tweens[r].setPosition(i,e),i!=this._prevPos)return!1;return s&&this.setPaused(!0),this.dispatchEvent("change"),s},e.setPaused=function(t){this._paused=!!t,createjs.Tween._register(this,!t)},e.updateDuration=function(){this.duration=0;for(var t=0,e=this._tweens.length;e>t;t++){var i=this._tweens[t];i.duration>this.duration&&(this.duration=i.duration)}},e.tick=function(t){this.setPosition(this._prevPosition+t)},e.resolve=function(t){var e=Number(t);return isNaN(e)&&(e=this._labels[t]),e},e.toString=function(){return"[Timeline]"},e.clone=function(){throw"Timeline can not be cloned."},e._goto=function(t){var e=this.resolve(t);null!=e&&this.setPosition(e)},createjs.Timeline=createjs.promote(t,"EventDispatcher")}(),this.createjs=this.createjs||{},function(){"use strict";function t(){throw"Ease cannot be instantiated."}t.linear=function(t){return t},t.none=t.linear,t.get=function(t){return-1>t&&(t=-1),t>1&&(t=1),function(e){return 0==t?e:0>t?e*(e*-t+1+t):e*((2-e)*t+(1-t))}},t.getPowIn=function(t){return function(e){return Math.pow(e,t)}},t.getPowOut=function(t){return function(e){return 1-Math.pow(1-e,t)}},t.getPowInOut=function(t){return function(e){return(e*=2)<1?.5*Math.pow(e,t):1-.5*Math.abs(Math.pow(2-e,t))}},t.quadIn=t.getPowIn(2),t.quadOut=t.getPowOut(2),t.quadInOut=t.getPowInOut(2),t.cubicIn=t.getPowIn(3),t.cubicOut=t.getPowOut(3),t.cubicInOut=t.getPowInOut(3),t.quartIn=t.getPowIn(4),t.quartOut=t.getPowOut(4),t.quartInOut=t.getPowInOut(4),t.quintIn=t.getPowIn(5),t.quintOut=t.getPowOut(5),t.quintInOut=t.getPowInOut(5),t.sineIn=function(t){return 1-Math.cos(t*Math.PI/2)},t.sineOut=function(t){return Math.sin(t*Math.PI/2)},t.sineInOut=function(t){return-.5*(Math.cos(Math.PI*t)-1)},t.getBackIn=function(t){return function(e){return e*e*((t+1)*e-t)}},t.backIn=t.getBackIn(1.7),t.getBackOut=function(t){return function(e){return--e*e*((t+1)*e+t)+1}},t.backOut=t.getBackOut(1.7),t.getBackInOut=function(t){return t*=1.525,function(e){return(e*=2)<1?.5*e*e*((t+1)*e-t):.5*((e-=2)*e*((t+1)*e+t)+2)}},t.backInOut=t.getBackInOut(1.7),t.circIn=function(t){return-(Math.sqrt(1-t*t)-1)},t.circOut=function(t){return Math.sqrt(1- --t*t)},t.circInOut=function(t){return(t*=2)<1?-.5*(Math.sqrt(1-t*t)-1):.5*(Math.sqrt(1-(t-=2)*t)+1)},t.bounceIn=function(e){return 1-t.bounceOut(1-e)},t.bounceOut=function(t){return 1/2.75>t?7.5625*t*t:2/2.75>t?7.5625*(t-=1.5/2.75)*t+.75:2.5/2.75>t?7.5625*(t-=2.25/2.75)*t+.9375:7.5625*(t-=2.625/2.75)*t+.984375},t.bounceInOut=function(e){return.5>e?.5*t.bounceIn(2*e):.5*t.bounceOut(2*e-1)+.5},t.getElasticIn=function(t,e){var i=2*Math.PI;return function(s){if(0==s||1==s)return s;var r=e/i*Math.asin(1/t);return-(t*Math.pow(2,10*(s-=1))*Math.sin((s-r)*i/e))}},t.elasticIn=t.getElasticIn(1,.3),t.getElasticOut=function(t,e){var i=2*Math.PI;return function(s){if(0==s||1==s)return s;var r=e/i*Math.asin(1/t);return t*Math.pow(2,-10*s)*Math.sin((s-r)*i/e)+1}},t.elasticOut=t.getElasticOut(1,.3),t.getElasticInOut=function(t,e){var i=2*Math.PI;return function(s){var r=e/i*Math.asin(1/t);return(s*=2)<1?-.5*t*Math.pow(2,10*(s-=1))*Math.sin((s-r)*i/e):t*Math.pow(2,-10*(s-=1))*Math.sin((s-r)*i/e)*.5+1}},t.elasticInOut=t.getElasticInOut(1,.3*1.5),createjs.Ease=t}(),this.createjs=this.createjs||{},function(){"use strict";function t(){throw"MotionGuidePlugin cannot be instantiated."}t.priority=0,t._rotOffS,t._rotOffE,t._rotNormS,t._rotNormE,t.install=function(){return createjs.Tween.installPlugin(t,["guide","x","y","rotation"]),createjs.Tween.IGNORE},t.init=function(t,e,i){var s=t.target;return s.hasOwnProperty("x")||(s.x=0),s.hasOwnProperty("y")||(s.y=0),s.hasOwnProperty("rotation")||(s.rotation=0),"rotation"==e&&(t.__needsRot=!0),"guide"==e?null:i},t.step=function(e,i,s,r,n){if("rotation"==i&&(e.__rotGlobalS=s,e.__rotGlobalE=r,t.testRotData(e,n)),"guide"!=i)return r;var a,o=r;o.hasOwnProperty("path")||(o.path=[]);var h=o.path;if(o.hasOwnProperty("end")||(o.end=1),o.hasOwnProperty("start")||(o.start=s&&s.hasOwnProperty("end")&&s.path===h?s.end:0),o.hasOwnProperty("_segments")&&o._length)return r;var c=h.length,u=10;if(!(c>=6&&(c-2)%4==0))throw"invalid 'path' data, please see documentation for valid paths";o._segments=[],o._length=0;for(var l=2;c>l;l+=4){for(var d,_,p=h[l-2],f=h[l-1],g=h[l+0],m=h[l+1],v=h[l+2],E=h[l+3],b=p,y=f,S=0,j=[],T=1;u>=T;T++){var x=T/u,w=1-x;d=w*w*p+2*w*x*g+x*x*v,_=w*w*f+2*w*x*m+x*x*E,S+=j[j.push(Math.sqrt((a=d-b)*a+(a=_-y)*a))-1],b=d,y=_}o._segments.push(S),o._segments.push(j),o._length+=S}a=o.orient,o.orient=!0;var P={};return t.calc(o,o.start,P),e.__rotPathS=Number(P.rotation.toFixed(5)),t.calc(o,o.end,P),e.__rotPathE=Number(P.rotation.toFixed(5)),o.orient=!1,t.calc(o,o.end,n),o.orient=a,o.orient?(e.__guideData=o,t.testRotData(e,n),r):r},t.testRotData=function(t,e){if(void 0===t.__rotGlobalS||void 0===t.__rotGlobalE){if(t.__needsRot)return;t.__rotGlobalS=t.__rotGlobalE=void 0!==t._curQueueProps.rotation?t._curQueueProps.rotation:e.rotation=t.target.rotation||0}if(void 0!==t.__guideData){var i=t.__guideData,s=t.__rotGlobalE-t.__rotGlobalS,r=t.__rotPathE-t.__rotPathS,n=s-r;if("auto"==i.orient)n>180?n-=360:-180>n&&(n+=360);else if("cw"==i.orient){for(;0>n;)n+=360;0==n&&s>0&&180!=s&&(n+=360)}else if("ccw"==i.orient){for(n=s-(r>180?360-r:r);n>0;)n-=360;0==n&&0>s&&-180!=s&&(n-=360)}i.rotDelta=n,i.rotOffS=t.__rotGlobalS-t.__rotPathS,t.__rotGlobalS=t.__rotGlobalE=t.__guideData=t.__needsRot=void 0}},t.tween=function(e,i,s,r,n,a,o){var h=n.guide;if(void 0==h||h===r.guide)return s;if(h.lastRatio!=a){var c=(h.end-h.start)*(o?h.end:a)+h.start;switch(t.calc(h,c,e.target),h.orient){case"cw":case"ccw":case"auto":e.target.rotation+=h.rotOffS+h.rotDelta*a;break;case"fixed":default:e.target.rotation+=h.rotOffS}h.lastRatio=a}return"rotation"!=i||h.orient&&"false"!=h.orient?e.target[i]:s},t.calc=function(e,i,s){void 0==e._segments&&t.validate(e),void 0==s&&(s={x:0,y:0,rotation:0});for(var r=e._segments,n=e.path,a=e._length*i,o=r.length-2,h=0;a>r[h]&&o>h;)a-=r[h],h+=2;var c=r[h+1],u=0;for(o=c.length-1;a>c[u]&&o>u;)a-=c[u],u++;var l=u/++o+a/(o*c[u]);h=2*h+2;var d=1-l;return s.x=d*d*n[h-2]+2*d*l*n[h+0]+l*l*n[h+2],s.y=d*d*n[h-1]+2*d*l*n[h+1]+l*l*n[h+3],e.orient&&(s.rotation=57.2957795*Math.atan2((n[h+1]-n[h-1])*d+(n[h+3]-n[h+1])*l,(n[h+0]-n[h-2])*d+(n[h+2]-n[h+0])*l)),s},createjs.MotionGuidePlugin=t}(),this.createjs=this.createjs||{},function(){"use strict";var t=createjs.TweenJS=createjs.TweenJS||{};t.version="0.6.0",t.buildDate="Thu, 11 Dec 2014 23:32:09 GMT"}(),this.createjs=this.createjs||{},function(){"use strict";function t(t){this.Container_constructor(),this.spriteSheet=t}var e=createjs.extend(t,createjs.Container);e.addChild=function(t){return null==t?t:arguments.length>1?this.addChildAt.apply(this,Array.prototype.slice.call(arguments).concat([this.children.length])):this.addChildAt(t,this.children.length)},e.addChildAt=function(t,e){var i=arguments.length,s=arguments[i-1];if(0>s||s>this.children.length)return arguments[i-2];if(i>2){for(var r=0;i-1>r;r++)this.addChildAt(arguments[r],s+r);return arguments[i-2]}if(!(t._spritestage_compatibility>=1))return console&&console.log("Error: You can only add children of type SpriteContainer, Sprite, BitmapText, or DOMElement ["+t.toString()+"]"),t;if(t._spritestage_compatibility<=4){var n=t.spriteSheet;if(!n||!n._images||n._images.length>1||this.spriteSheet&&this.spriteSheet!==n)return console&&console.log("Error: A child's spriteSheet must be equal to its parent spriteSheet and only use one image. ["+t.toString()+"]"),t;this.spriteSheet=n}return t.parent&&t.parent.removeChild(t),t.parent=this,this.children.splice(e,0,t),t},e.toString=function(){return"[SpriteContainer (name="+this.name+")]"},createjs.SpriteContainer=createjs.promote(t,"Container")}(),this.createjs=this.createjs||{},function(){"use strict";function t(t,e,i){this.Stage_constructor(t),this._preserveDrawingBuffer=e||!1,this._antialias=i||!1,this._viewportWidth=0,this._viewportHeight=0,this._projectionMatrix=null,this._webGLContext=null,this._webGLErrorDetected=!1,this._clearColor=null,this._maxTexturesPerDraw=1,this._maxBoxesPointsPerDraw=null,this._maxBoxesPerDraw=null,this._maxIndicesPerDraw=null,this._shaderProgram=null,this._vertices=null,this._verticesBuffer=null,this._indices=null,this._indicesBuffer=null,this._currentBoxIndex=-1,this._drawTexture=null,this._initializeWebGL()}[createjs.SpriteContainer,createjs.Sprite,createjs.BitmapText,createjs.Bitmap,createjs.DOMElement].forEach(function(t,e){t.prototype._spritestage_compatibility=e+1});var e=createjs.extend(t,createjs.Stage);t.NUM_VERTEX_PROPERTIES=5,t.POINTS_PER_BOX=4,t.NUM_VERTEX_PROPERTIES_PER_BOX=t.POINTS_PER_BOX*t.NUM_VERTEX_PROPERTIES,t.INDICES_PER_BOX=6,t.MAX_INDEX_SIZE=Math.pow(2,16),t.MAX_BOXES_POINTS_INCREMENT=t.MAX_INDEX_SIZE/4,e._get_isWebGL=function(){return!!this._webGLContext};try{Object.defineProperties(e,{isWebGL:{get:e._get_isWebGL}})}catch(i){}e.addChild=function(t){return null==t?t:arguments.length>1?this.addChildAt.apply(this,Array.prototype.slice.call(arguments).concat([this.children.length])):this.addChildAt(t,this.children.length)},e.addChildAt=function(t,e){var i=arguments.length,s=arguments[i-1];if(0>s||s>this.children.length)return arguments[i-2];if(i>2){for(var r=0;i-1>r;r++)this.addChildAt(arguments[r],s+r);return arguments[i-2]}return t._spritestage_compatibility>=1?!t.image&&!t.spriteSheet&&t._spritestage_compatibility<=4?(console&&console.log("Error: You can only add children that have an image or spriteSheet defined on them. ["+t.toString()+"]"),t):(t.parent&&t.parent.removeChild(t),t.parent=this,this.children.splice(e,0,t),this._setUpKidTexture(this._webGLContext,t),t):(console&&console.log("Error: You can only add children of type SpriteContainer, Sprite, Bitmap, BitmapText, or DOMElement. ["+t.toString()+"]"),t)},e.update=function(t){if(this.canvas){this.tickOnUpdate&&this.tick(t),this.dispatchEvent("drawstart"),this.autoClear&&this.clear();var e=this._setWebGLContext();e?this.draw(e,!1):(e=this.canvas.getContext("2d"),e.save(),this.updateContext(e),this.draw(e,!1),e.restore()),this.dispatchEvent("drawend")}},e.clear=function(){if(this.canvas){var t=this._setWebGLContext();t?t.clear(t.COLOR_BUFFER_BIT):(t=this.canvas.getContext("2d"),t.setTransform(1,0,0,1,0,0),t.clearRect(0,0,this.canvas.width+1,this.canvas.height+1))}},e.draw=function(t,e){return"undefined"!=typeof WebGLRenderingContext&&(t===this._webGLContext||t instanceof WebGLRenderingContext)?(this._drawWebGLKids(this.children,t),this._drawTexture&&this._drawToGPU(t),!0):this.Stage_draw(t,e)},e.updateViewport=function(t,e){this._viewportWidth=t,this._viewportHeight=e,this._webGLContext&&(this._webGLContext.viewport(0,0,this._viewportWidth,this._viewportHeight),this._projectionMatrix||(this._projectionMatrix=new Float32Array([0,0,0,0,0,1,-1,1,1])),this._projectionMatrix[0]=2/t,this._projectionMatrix[4]=-2/e)},e.clearImageTexture=function(t){t.__easeljs_texture=null},e.toString=function(){return"[SpriteStage (name="+this.name+")]"},e._initializeWebGL=function(){this._clearColor={r:0,g:0,b:0,a:0},this._setWebGLContext()},e._setWebGLContext=function(){return this.canvas?this._webGLContext&&this._webGLContext.canvas===this.canvas||this._initializeWebGLContext():this._webGLContext=null,this._webGLContext},e._initializeWebGLContext=function(){var t={depth:!1,alpha:!0,preserveDrawingBuffer:this._preserveDrawingBuffer,antialias:this._antialias,premultipliedAlpha:!0},e=this._webGLContext=this.canvas.getContext("webgl",t)||this.canvas.getContext("experimental-webgl",t);if(e){if(this._maxTexturesPerDraw=1,this._setClearColor(this._clearColor.r,this._clearColor.g,this._clearColor.b,this._clearColor.a),e.enable(e.BLEND),e.blendFuncSeparate(e.SRC_ALPHA,e.ONE_MINUS_SRC_ALPHA,e.ONE,e.ONE_MINUS_SRC_ALPHA),e.pixelStorei(e.UNPACK_PREMULTIPLY_ALPHA_WEBGL,!1),this._createShaderProgram(e),this._webGLErrorDetected)return void(this._webGLContext=null);this._createBuffers(e),this.updateViewport(this._viewportWidth||this.canvas.width||0,this._viewportHeight||this.canvas.height||0)}},e._setClearColor=function(t,e,i,s){this._clearColor.r=t,this._clearColor.g=e,this._clearColor.b=i,this._clearColor.a=s,this._webGLContext&&this._webGLContext.clearColor(t,e,i,s)},e._createShaderProgram=function(t){var e=this._createShader(t,t.FRAGMENT_SHADER,"precision mediump float;uniform sampler2D uSampler0;varying vec3 vTextureCoord;void main(void) {vec4 color = texture2D(uSampler0, vTextureCoord.st);gl_FragColor = vec4(color.rgb, color.a * vTextureCoord.z);}"),i=this._createShader(t,t.VERTEX_SHADER,"attribute vec2 aVertexPosition;attribute vec3 aTextureCoord;uniform mat3 uPMatrix;varying vec3 vTextureCoord;void main(void) {vTextureCoord = aTextureCoord;gl_Position = vec4((uPMatrix * vec3(aVertexPosition, 1.0)).xy, 0.0, 1.0);}");if(!this._webGLErrorDetected&&e&&i){var s=t.createProgram();if(t.attachShader(s,e),t.attachShader(s,i),t.linkProgram(s),!t.getProgramParameter(s,t.LINK_STATUS))return void(this._webGLErrorDetected=!0);s.vertexPositionAttribute=t.getAttribLocation(s,"aVertexPosition"),s.textureCoordAttribute=t.getAttribLocation(s,"aTextureCoord"),s.sampler0uniform=t.getUniformLocation(s,"uSampler0"),t.enableVertexAttribArray(s.vertexPositionAttribute),t.enableVertexAttribArray(s.textureCoordAttribute),s.pMatrixUniform=t.getUniformLocation(s,"uPMatrix"),t.useProgram(s),this._shaderProgram=s}},e._createShader=function(t,e,i){var s=t.createShader(e);return t.shaderSource(s,i),t.compileShader(s),t.getShaderParameter(s,t.COMPILE_STATUS)?s:(this._webGLErrorDetected=!0,null)},e._createBuffers=function(e){this._verticesBuffer=e.createBuffer(),e.bindBuffer(e.ARRAY_BUFFER,this._verticesBuffer);var i=4*t.NUM_VERTEX_PROPERTIES;e.vertexAttribPointer(this._shaderProgram.vertexPositionAttribute,2,e.FLOAT,e.FALSE,i,0),e.vertexAttribPointer(this._shaderProgram.textureCoordAttribute,3,e.FLOAT,e.FALSE,i,8),this._indicesBuffer=e.createBuffer(),this._setMaxBoxesPoints(e,t.MAX_BOXES_POINTS_INCREMENT)},e._setMaxBoxesPoints=function(e,i){this._maxBoxesPointsPerDraw=i,this._maxBoxesPerDraw=this._maxBoxesPointsPerDraw/t.POINTS_PER_BOX|0,this._maxIndicesPerDraw=this._maxBoxesPerDraw*t.INDICES_PER_BOX,e.bindBuffer(e.ARRAY_BUFFER,this._verticesBuffer),this._vertices=new Float32Array(this._maxBoxesPerDraw*t.NUM_VERTEX_PROPERTIES_PER_BOX),e.bufferData(e.ARRAY_BUFFER,this._vertices,e.DYNAMIC_DRAW),this._indices=new Uint16Array(this._maxIndicesPerDraw);for(var s=0,r=this._indices.length;r>s;s+=t.INDICES_PER_BOX){var n=s*t.POINTS_PER_BOX/t.INDICES_PER_BOX;this._indices[s]=n,this._indices[s+1]=n+1,this._indices[s+2]=n+2,this._indices[s+3]=n,this._indices[s+4]=n+2,this._indices[s+5]=n+3}e.bindBuffer(e.ELEMENT_ARRAY_BUFFER,this._indicesBuffer),e.bufferData(e.ELEMENT_ARRAY_BUFFER,this._indices,e.STATIC_DRAW)},e._setUpKidTexture=function(t,e){if(!t)return null;var i,s=null;return 4===e._spritestage_compatibility?i=e.image:e._spritestage_compatibility<=3&&e.spriteSheet&&e.spriteSheet._images&&(i=e.spriteSheet._images[0]),i&&(s=i.__easeljs_texture,s||(s=i.__easeljs_texture=t.createTexture(),t.bindTexture(t.TEXTURE_2D,s),t.texImage2D(t.TEXTURE_2D,0,t.RGBA,t.RGBA,t.UNSIGNED_BYTE,i),t.texParameteri(t.TEXTURE_2D,t.TEXTURE_MIN_FILTER,t.NEAREST),t.texParameteri(t.TEXTURE_2D,t.TEXTURE_MAG_FILTER,t.LINEAR),t.texParameteri(t.TEXTURE_2D,t.TEXTURE_WRAP_S,t.CLAMP_TO_EDGE),t.texParameteri(t.TEXTURE_2D,t.TEXTURE_WRAP_T,t.CLAMP_TO_EDGE))),s},e._drawWebGLKids=function(e,i,s){for(var r,n,a=this.snapToPixelEnabled,o=null,h=0,c=0,u=0,l=0,d=this._vertices,_=t.NUM_VERTEX_PROPERTIES_PER_BOX,p=t.MAX_INDEX_SIZE,f=this._maxBoxesPerDraw-1,g=0,m=e.length;m>g;g++)if(r=e[g],r.isVisible()){n=r._props.matrix,n=(s?n.copy(s):n.identity()).appendTransform(r.x,r.y,r.scaleX,r.scaleY,r.rotation,r.skewX,r.skewY,r.regX,r.regY);var v=0,E=1,b=0,y=1;if(4===r._spritestage_compatibility)o=r.image,h=0,c=0,u=o.width,l=o.height;else if(2===r._spritestage_compatibility){var S=r.spriteSheet.getFrame(r.currentFrame),j=S.rect;o=S.image,h=-S.regX,c=-S.regY,u=h+j.width,l=c+j.height,v=j.x/o.width,b=j.y/o.height,E=v+j.width/o.width,y=b+j.height/o.height}else o=null,3===r._spritestage_compatibility&&r._updateText();if(!s&&r._spritestage_compatibility<=4){var T=(o||r.spriteSheet._images[0]).__easeljs_texture;T!==this._drawTexture&&(this._drawTexture&&this._drawToGPU(i),this._drawTexture=T,i.activeTexture(i.TEXTURE0),i.bindTexture(i.TEXTURE_2D,T),i.uniform1i(this._shaderProgram.sampler0uniform,0))}if(null!==o){var x=++this._currentBoxIndex*_,w=n.a,P=n.b,L=n.c,A=n.d,R=n.tx,I=n.ty;a&&r.snapToPixel&&(R=R+(0>R?-.5:.5)|0,I=I+(0>I?-.5:.5)|0),d[x]=h*w+c*L+R,d[x+1]=h*P+c*A+I,d[x+5]=h*w+l*L+R,d[x+6]=h*P+l*A+I,d[x+10]=u*w+l*L+R,d[x+11]=u*P+l*A+I,d[x+15]=u*w+c*L+R,d[x+16]=u*P+c*A+I,d[x+2]=d[x+7]=v,d[x+12]=d[x+17]=E,d[x+3]=d[x+18]=b,d[x+8]=d[x+13]=y,d[x+4]=d[x+9]=d[x+14]=d[x+19]=r.alpha,this._currentBoxIndex===f&&(this._drawToGPU(i),this._drawTexture=o.__easeljs_texture,i.activeTexture(i.TEXTURE0),i.bindTexture(i.TEXTURE_2D,this._drawTexture),i.uniform1i(this._shaderProgram.sampler0uniform,0),this._maxBoxesPointsPerDraw<p&&(this._setMaxBoxesPoints(i,this._maxBoxesPointsPerDraw+t.MAX_BOXES_POINTS_INCREMENT),f=this._maxBoxesPerDraw-1))}r.children&&(this._drawWebGLKids(r.children,i,n),f=this._maxBoxesPerDraw-1)}},e._drawToGPU=function(e){var i=this._currentBoxIndex+1;e.bindBuffer(e.ARRAY_BUFFER,this._verticesBuffer),e.bindBuffer(e.ELEMENT_ARRAY_BUFFER,this._indicesBuffer),e.uniformMatrix3fv(this._shaderProgram.pMatrixUniform,!1,this._projectionMatrix),e.bufferSubData(e.ARRAY_BUFFER,0,this._vertices),e.drawElements(e.TRIANGLES,i*t.INDICES_PER_BOX,e.UNSIGNED_SHORT,0),this._currentBoxIndex=-1,this._drawTexture=null},createjs.SpriteStage=createjs.promote(t,"Stage")}();


/**
 * Function.prototype.bind() polyfill
 */
if (!Function.prototype.bind) {
  Function.prototype.bind = function(oThis) {
    if (typeof this !== 'function') {
      // closest thing possible to the ECMAScript 5
      // internal IsCallable function
      throw new TypeError('Function.prototype.bind - what is trying to be bound is not callable');
    }

    var aArgs   = Array.prototype.slice.call(arguments, 1),
        fToBind = this,
        fNOP    = function() {},
        fBound  = function() {
          return fToBind.apply(this instanceof fNOP && oThis
                 ? this
                 : oThis,
                 aArgs.concat(Array.prototype.slice.call(arguments)));
        };

    fNOP.prototype = this.prototype;
    fBound.prototype = new fNOP();

    return fBound;
  };
}

function random(begin, end) {
  var range = end - begin + 1;
  return Math.floor(Math.random() * range + begin);
}

function cubicEaseIn(t,b,c,d) {
  return c*(t/=d)*t*t + b
}


(function() {
	'use strict'

	window.Game = {}
	Game.debug = location.href.indexOf('debug') != -1
	Game.MAX_TIME  = 120000
	Game.MAX_SCORE = 2000
	Game.MAX_SPEED_TIME = 60000
	Game.SPEED = 0.6 //初始化速度

	window.game =  {
		init: function() {
			if (this._initialized) {
				return;
			}
			this._initialized = true
			this._ready = false;

			var clientWidth = Math.min(document.body.clientWidth, document.body.clientHeight),
				clientHeight = Math.max(document.body.clientWidth, document.body.clientHeight)

			//前景层
			this.canvas = document.getElementById('front-stage')
			this.canvas.width = 640
			this.canvas.height = this.canvas.width * clientHeight / clientWidth
			this.stage = new createjs.Stage(this.canvas)
			document.body.insertBefore(this.canvas, document.getElementById('wrapper'))

			//背景层
			this.backCanvas = document.getElementById('back-stage')
			this.backCanvas.width = 640
			this.backCanvas.height = this.backCanvas.width * clientHeight / clientWidth
			this.backStage = new createjs.SpriteStage(this.backCanvas/*, false, true*/)
			document.body.insertBefore(this.backCanvas, this.canvas)

			window.background = new Background()
			window.blocks = new Blocks()
			window.player = new Player()
			window.scoreboard = new Scoreboard()

			createjs.Touch.enable(this.stage)
			createjs.Ticker.timingMode = createjs.Ticker.RAF
			// createjs.Ticker.timingMode = createjs.Ticker.RAF_SYNCHED
			createjs.Ticker.framerate = 60

			//@TODO: 改为匀加速奔跑,在game.update里计算velocity
			this.velocity = background.roadDist / Game.MAX_SPEED_TIME

			Game.debug && this.showFPS() //TEST
			this.bindEvents()
		},
		start: function(role, callback) {
			player.setRole(role || Player.MAN)
			this.callback = callback
			


			createjs.Ticker.addEventListener('tick', this.update.bind(this))


		},
		reset: function() {
			this.finished = false
			this.over = false
			this.end = false
			
			this.miss = {} //躲避统计
			this.distance = 0 //奔跑路程

			background.reset()
			blocks.reset()
			player.reset()
			scoreboard.reset()
			createjs.Ticker.reset()
			this._ready = false
			this._canRun = false
		},
		bindEvents: function() {
			this.stage.addEventListener('stagemousedown', function(event) {
				if (event.isTouch && !this.finished && !this.over) {
					event.stageX < this.canvas.width / 2 ? player.moveLeft() : player.moveRight()
				}
			}.bind(this))

			document.body.addEventListener('keydown', this.onkeydown)
		},
		showFPS: function() {
			var text = new createjs.Text('0 fps', '40px Arial', '#FFF')
			text.textAlign = 'right'
			text.x = this.canvas.width - 20
			text.y = 20
			this.stage.addChild(text)
			this.fpsText = text
		},
		update: function(event) {
			var self = this;
			if (this.fpsText) {
				this.fpsText.text = Math.round(createjs.Ticker.getMeasuredFPS()) + 'fps'
			}

			this.velocity = Game.SPEED + event.runTime / Game.MAX_SPEED_TIME

			if(!this._ready){

				this.distance += event.delta * this.velocity

				scoreboard.update(event)
				background.update(event)
				blocks.update(event)
				player.update(event)

				this.backStage.update(event)
				this.stage.update(event)
				this._ready = true;
				createjs.Ticker.paused = true

				var ani = new CounterAni({onComplete:function(){
					self._canRun = true
					createjs.Ticker.paused = false

				}})


			}

			if(this._canRun){

				this.distance += event.delta * this.velocity

				scoreboard.update(event)
				background.update(event)
				blocks.update(event)
				player.update(event)

				this.backStage.update(event)
				this.stage.update(event)
			}


			if ((this.over || this.finished) && !this.end) {
				this.end = true

				//单独调试游戏时
				if (window.debugGame) {
					if (this.over) {
						createjs.Ticker.removeAllEventListeners('tick')
					}

					setTimeout(function() {
						this.reset()
						this.start()
					}.bind(this), 1000)
					
					return;
				}

				if (this.over) {
					createjs.Ticker.removeAllEventListeners('tick')
				}

				//回调页面显示分数
				this.callback(scoreboard.score, this.miss)
			}
		},
		onkeydown: function(event) {
			if (!this.finished && !this.over) {
				if (event.keyCode == 37) {
					player.moveLeft()
				} else if (event.keyCode == 39) {
					player.moveRight()
				}
			}
		}
	}

})();


window.Scoreboard = (function() {
	'use strict'

	//------------------------------------------------------------

	function Scoreboard() {
		var container = new createjs.Container()
		container.x = (game.canvas.width - 306) / 2
		container.y = 10
		game.stage.addChild(container)

		var board = new createjs.Bitmap(loader.getResult('blocks'))
		// board.sourceRect = new createjs.Rectangle(0, 0, 306, 62) //units
		board.sourceRect = new createjs.Rectangle(0, 2123, 306, 62) //blocks
		container.addChild(board)

		var timer = new createjs.Text('0s', 'bold 40px Arial', '#5E0D0D')
		timer.x = 90
		timer.y = 10
		timer.maxWidth = 120
		timer.textAlign = 'center'
		container.addChild(timer)

		var distance = new createjs.Text('0m', '40px Arial', '#FFFFFF')
		distance.x = 220
		distance.y = 10
		distance.maxWidth = 120
		distance.textAlign = 'center'
		container.addChild(distance)

		this.timer = timer
		this.distance = distance
		this.score = 0 //距离即为最终分数
	}

	Scoreboard.prototype = {
		reset: function() {
			this.timer.text = '0s'
			this.distance.text = '0m'
			this.score = 0
		},
		update: function(event) {
			if (event.runTime >= Game.MAX_TIME) { //满分~
				this.timer.text = parseInt(Game.MAX_TIME / 1000) + 's'
				this.distance.text = Game.MAX_SCORE + 'm'
			} else {
				this.timer.text = (event.runTime / 1000).toFixed(1) + 's'
				this.distance.text = parseInt(event.runTime * 1000 / Game.MAX_SPEED_TIME) + 'm'
			}
			this.score = parseInt(this.distance.text)
		}
	}

	return Scoreboard

})();


window.Player = (function() {
	'use strict'

	var BEAR = 'bear'

	var data = {}

	data[BEAR] = {
		width: 192,
		height: 200
	}

	var frames = [
		//bear
		[0, 0, 192, 200],
		[0, 211, 192, 200],
		[0, 415, 192, 200]
	]

	var animations = {}
	animations[BEAR] = [0, 2]

	//------------------------------------------------------------

	function Player() {
		var spriteSheet = new createjs.SpriteSheet({
			images: [loader.getResult('player')],
			frames: frames,
			framerate: 10,
			animations: animations
		})

		this.sprite = new createjs.Sprite(spriteSheet)
		game.backStage.addChild(this.sprite)

		this.offset = 130 //左右移动距离
		this.speed = 10
	}

	Player.prototype = {
		setRole: function(role) {
			role = role || MAN

			this.sprite.x = (game.backCanvas.width - data[role].width) / 2
			this.sprite.y = game.backCanvas.height - data[role].height - 40
			this.sprite.gotoAndPlay(role)

			this.centerX = this.sprite.x
			this.role = role
		},
		reset: function() {
			this.end = false
			this.moving = undefined //躲避数据,包含direction,position
		},
		update: function(event) {
			//横向移动
			if (this.moving) {
				switch (this.moving.direction) {
					case 'right':
						if (this.sprite.x < this.moving.position) {
							this.sprite.x += event.delta
						} else {
							this.sprite.x = this.moving.position
							this.moving = undefined
						}
						break
					case 'left':
						if (this.sprite.x > this.moving.position) {
							this.sprite.x -= event.delta
						} else {
							this.sprite.x = this.moving.position
							this.moving = undefined
						}
				}
			}

			//骑宝座,撒纸片
			if (this.end) {
				var i, frame, y, seatY = 410

				i = this.role == MAN ? 12 : (this.role == GIRL ? 13 : 14)
				frame = frames[i]
				y = seatY - frame[3] * 2 / 3 //屁股在2/3处

				if (this.sprite.y > y) {
					this.sprite.y -= event.delta * game.velocity / 2
				} else {
					this.sprite.y = y
					this.sprite.x = (game.backCanvas.width - frame[2]) / 2
					this.sprite.gotoAndStop(i)
				}
			}


			if (game.finished && !this.end) {
				this.end = true

				//归位
				if (this.sprite.x < this.centerX) {
					this.moving = {
						'direction': 'right',
						'position' : this.centerX
					}
				} else if (this.sprite.x > this.centerX) {
					this.moving = {
						'direction': 'left',
						'position' : this.centerX
					}
				}

				//confetti.play()
			}
			else if (this._hitTest()) {
				game.over = true
			}
		},
		moveLeft: function() {
			if (!game.finished && !game.over && !this.moving && 
					(this.sprite.x > (this.centerX - this.offset))) {

				this.moving = {
					'direction': 'left',
					'position' : this.sprite.x - this.offset
				}
			}
		},
		moveRight: function() {
			if (!game.finished && !game.over && !this.moving && 
					(this.sprite.x < (this.centerX + this.offset))) {

				this.moving = {
					'direction': 'right',
					'position' : this.sprite.x + this.offset
				}
			}
		},
		_hitTest: function() {
			//return false
			var hitObjs = blocks.container.children
			if (hitObjs.length > 0) {
				for (var i = 0, item; item = hitObjs[i++]; ) {
					for (var j = 0, pt; pt = item.hitPoints && item.hitPoints[j++]; ) {
						if (item.y < game.backCanvas.height - 300) {
							continue;
						}
						var pos = item.localToLocal(pt.x, pt.y, this.sprite)
						if (this.sprite.hitTest(pos.x, pos.y)) {
							return true
						}
					}
				}
			}
			return false
		}
	}

	Player.BEAR = BEAR

	return Player

})();


window.Blocks = (function() {
	'use strict'

	var START_TIME = 2000

	//障碍物的水平位置
	var pos = [
		180, 320, 460
	]

	var data = [
		{
			name: 'block1',
			frame: [0,0,124,171]
		},
		{
			name: 'block2',
			frame: [0,204,106,170]
		},
		{
			name: 'block3',
			frame: [0,417,101,170]
		},
		{
			name: 'block4',
			frame: [0,631,138,170]
		},
		{
			name: 'block5',
			frame: [0,844,138,170]
		},
		{
			name: 'block6',
			frame: [0,1047,146,170]
		}
	]

	//游戏难度设定(随时间推移dist由max变为min)
	var MIN_DIST = 50,
		MAX_DIST = 300

	var firstTime = true

	var noRepeatRandom1 = (function() {
		var last = -1
		return function(start, end) {
			if (start == end) {
				return start
			} else {
				var r
				do {
					r = random(start, end)
				} while (r == last)
				last = r
				return r
			}
		}
	})()

	var noRepeatRandom2 = (function() {
		var last = -1
		return function(start, end) {
			if (start == end) {
				return start
			} else {
				var r
				do {
					r = random(start, end)
				} while (r == last)
				last = r
				return r
			}
		}
	})()

	//------------------------------------------------------------

	function Blocks() {
		this.spriteSheet = new createjs.SpriteSheet({
			images: [loader.getResult('blocks')],
			frames: [
				data[0].frame,
				data[1].frame,
				data[2].frame,
				data[3].frame,
				data[4].frame,
				data[5].frame,
				[80, 620, 20, 20] //碰撞检测调试
			]
		})

		this.container = new createjs.SpriteContainer(this.spriteSheet)
		this.container.x = 0
		this.container.y = 0 //以canvas左上角为原点
		game.backStage.addChild(this.container)

		this.recycleQueue = []
	}

	Blocks.prototype = {
		reset: function() {
			var firstChild
			while (firstChild = this.container.getChildAt(0)) {
				this.container.removeChildAt(0)
				this.recycleQueue.push(firstChild)
			}
		},
		update: function(event) {
			var lastChild

			if (game.finished) {
				return
			}

			//移动障碍物
			for (var i = 0, item; item = this.container.getChildAt(i++); ) {
				item.y += event.delta * game.velocity
			}

			this._removeExpired()

			if (event.runTime > START_TIME) {
				lastChild = this.container.getChildAt(this.container.getNumChildren() - 1)
				
				//当前最后一个全部显示后添加一个新的
				if (!lastChild || lastChild.y >= 0) {
					var distance = random(MIN_DIST, cubicEaseIn(event.runTime, MAX_DIST, MIN_DIST - MAX_DIST, Game.MAX_TIME))
					this._add(distance)
				}

			}
		},
		_add: function(distance) {
			var index = noRepeatRandom1(0, data.length - 1),
				block = data[index],
				x = pos[noRepeatRandom2(0, pos.length - 1)],
				y = -(distance + block.frame[3])

			//蛋疼的计算,为避免遮住CBD
			/*var cbdPosBottom = background.cbdPosBottom - game.distance - game.backCanvas.height,
				cbdPosTop = background.cbdPosTop - game.distance - game.backCanvas.height
			if (-y > cbdPosBottom && distance < cbdPosTop) {
				y = -(cbdPosTop + block.frame[3])
			}*/

			if (firstTime) {
				x = pos[1] //第1次第1个放中间
				firstTime = false
			}

			if (game.distance + game.backCanvas.height - y <= background.roadDist) {


				var sprite = this.recycleQueue.shift() || new createjs.Sprite(this.spriteSheet)
				sprite.gotoAndStop(index)
				sprite.name = block.name
				sprite.regX = block.frame[2] / 2
				sprite.regY = 0
				sprite.x = x
				sprite.y = y
				sprite.pos = block.pos
				sprite.hitPoints = [{ x: block.frame[2] / 2, y: block.frame[3] * 2 / 3}]
				this.container.addChild(sprite)

				if (Game.debug && false) {
					for (var i = 0, point; point = sprite.hitPoints[i++]; ) {
						var debugPos = new createjs.Sprite(this.spriteSheet),
							pt = sprite.localToLocal(point.x, point.y, this.container)

						debugPos.gotoAndStop(7)
						debugPos.regX = debugPos.regY = 10
						debugPos.x = pt.x
						debugPos.y = pt.y

						this.container.addChild(debugPos)
					}
				}
			}
		},
		_removeExpired: function() {
			var firstChild = this.container.children[0]
			if (firstChild && firstChild.y > game.backCanvas.height) {
				//统计总数
				if (!game.miss.total) {
					game.miss.total = 0
				}
				game.miss.total++

				//统计单个
				if (!game.miss[firstChild.name]) {
					game.miss[firstChild.name] = 0
				}
				game.miss[firstChild.name]++

				this.container.removeChildAt(0)
				this.recycleQueue.push(firstChild)
			}
		}
	}

	return Blocks

})();


(function(w){

	var CounterAni = function(options){
		this.counter = 3;
		this.el = document.querySelector('.counter');
		this.onComplete  = options.onComplete || null;
		this.init();

	}

	CounterAni.prototype = {
		init : function(){		
			var self = this;

			this.el.parentNode.style.display = 'block';

			this.play();
			
		    this.el.addEventListener('webkitAnimationEnd',function(){

					this.className = 'counter';
					self.counter--;
					if(self.counter == 0){
						self.hide();
						self.onComplete && self.onComplete();
						return;
					}
					setTimeout(function(){
						self.play();
					},100);
			
			},false);

		},
		play : function(){
			this.el.className = 'counter counter' + this.counter;
		},
		hide : function(){

			this.el.parentNode.style.display = 'none';
			this.reset();

		},
		reset  : function(){
			this.counter = 3;
		}
	}
	w.CounterAni = CounterAni;

})(window);


window.Background = (function() {
	'use strict'

	var data = [		
		{
			name: 'race',
			total: 200,
			count: 0,
			rect: [0, 0, 640, 1134]
		}

	]

	//------------------------------------------------------------

	function Background() {
		this.spriteSheet = new createjs.SpriteSheet({
			images: [loader.getResult('blocks')],
			frames: [
				[0, 1628, 129, 244],
				[0, 1380, 129, 244],
				[0, 1876, 129, 243],
				[0, 1288, 378, 88]
			],
			animations: {
				'balloon0': 0,
				'balloon1': 1,
				'balloon2': 2,
				'cbd': 3
			}
		})

		//距离
		this.roadDist = data[0].rect[3] * data[0].total
		//this.podiumDist = data[3].rect[3]

		//记录下cbd的位置以免被障碍物遮挡
		/*this.cbdPosBottom = data[0].rect[3] * data[0].total + 
							data[1].rect[3] * data[1].total
		this.cbdPosTop = this.cbdPosBottom + (40 + 88 + 40)*/

		this.recycleQueue = []
		this.children = []
	}

	Background.prototype = {
		reset: function() {
			var firstChild
			while (firstChild = this.children[0]) {
				game.backStage.removeChild(firstChild)
				this.recycleQueue.push(this.children.shift())
			}

			this.index = 0

			for (var i = 0, item; item = data[i++]; ) {
				item.count = 0
			}
		},
		update: function(event) {
			
			var scene = data[this.index],
				lastChild = this._getLastBg()



			if (!lastChild) {
				this._addBg() //添加第1个背景
				return
			}

			//背景到顶
			if (!scene && lastChild.y >= 0) {
				lastChild.y = 0
				return
			}


			//移动背景
			for (var i = 0, item; item = this.children[i++]; ) {
				if (item.name.indexOf('balloon') != -1) {
					this._moveBallon(item, event)
				} else {
					item.y += event.delta * game.velocity
				}

			}

			this._removeExpired()

			if (scene) {
				//场景出现次数达上限时切换至下一场景
				if (scene.count >= scene.total) {
					this.index++
					this.index = 0;//只有一个场景
				}
				//当前最后一个全部显示后添加一个新的
				else if (lastChild.y >= 0) {
					this._addBg()
				}
			
			//到达终点(最后一条路消失在屏幕之后)
			} else if (game.distance >= this.roadDist) {
				game.finished = true
			}
		},
		_addBg: function() {
			var scene = data[this.index],
				bitmap = this._getReusedItemIfPossible(scene.name),
				lastBg

			if (!bitmap) {
				bitmap = new createjs.Bitmap(loader.getResult(scene.name))
				bitmap.name = scene.name
			}
			
			lastBg = this._getLastBg()
			if (lastBg) {
				bitmap.y = lastBg.y - scene.rect[3]
			} else {
				bitmap.y = game.backCanvas.height - scene.rect[3]
			}
			bitmap.x = 0

			game.backStage.addChildAt(bitmap, 0)
			this.children.push(bitmap)

			//scene.count++

			/*if (scene.name == 'country' && scene.count == scene.total) {
				this._addBalloons()
			} else if (scene.name == 'city' && scene.count == 1) {
				this._displayCBD()
			}*/
		},
		_removeExpired: function() {
			var firstChild = this.children[0]
			if (firstChild && firstChild.y > game.backCanvas.height) {
				game.backStage.removeChild(firstChild)
				this.recycleQueue.push(this.children.shift())
			}
		},
		_getReusedItemIfPossible: function(name, imageId) {
			name = name || data[this.index].name
			for (var i = 0, item; item = this.recycleQueue[i]; i++) {
				if (item.name == name) {
					return this.recycleQueue.splice(i, 1)[0]
				}
			}
			return null
		},
		_getLastBg: function() {
			for (var i = this.children.length - 1; i >= 0; i--) {
				var item = this.children[i]
				if ('country|town|city|podium|race'.indexOf(item.name) != -1) {
					return item
				}
			}
			return null
		},

		//添加气球
		_addBalloons: function() {
			var lastCountry, lastCountryBounds
			for (var i = this.children.length - 1; i >= 0; i--) {
				var item = this.children[i]
				if (item.name == 'country') {
					lastCountry = item
					lastCountryBounds = item.getBounds()
					break
				}
			}

			var x = [
				game.backCanvas.width / 2 - 140,
				game.backCanvas.width / 2,
				game.backCanvas.width / 2 + 140
			]

			for (var i = 0; i < 3; i++) {
				var name = 'balloon' + i,
					sprite = this._getReusedItemIfPossible(name)
				
				if (!sprite) {
					sprite = new createjs.Sprite(this.spriteSheet)
					sprite.name = name
					sprite.gotoAndStop(name)
				}

				sprite.regX = 129 / 2
				sprite.x = x[i]
				sprite.y = lastCountry.y + lastCountryBounds.height * 2 / 3

				game.backStage.addChild(sprite)
				this.children.push(sprite)
			}
		},
		//移动气球
		_moveBallon: function(balloon, event) {
			balloon.y += (event.delta * game.velocity) / 2

			switch (balloon.name) {
				case 'balloon0':
					balloon.x -= 0.1
					break
				case 'balloon1':
					balloon.y -= 0.1
					break
				case 'balloon2':
					balloon.x += 0.1
				}
		},
		//显示CBD字样
		_displayCBD: function() {
			var name = 'cbd',
				sprite = this._getReusedItemIfPossible(name),
				lastChild = this._getLastBg(),
				bounds = lastChild.getBounds()

			if (!sprite) {
				sprite = new createjs.Sprite(this.spriteSheet)
				sprite.name = name
				sprite.gotoAndStop(name)
			}

			sprite.regX = 378 / 2
			sprite.regY = 88
			sprite.x = game.backCanvas.width / 2
			sprite.y = lastChild.y + bounds.height - 40

			game.backStage.addChildAt(sprite, 0)
			game.backStage.swapChildren(sprite, lastChild)
			this.children.push(sprite)
		}
	}

	return Background

})();


window.loader = (function() {
 	var imagesForPage = [	
		'p1.png',
		'btn_start.png',
		'icon-title.png',
		'icon-people.png'
 	]
 	var imagesForGame = [
 		{ src: 'race.png', id: 'race' },
		{ src: 'blocks.png', id: 'blocks' },
		{ src: 'player.png', id: 'player' }
 	]

 	var manifest
 	if (location.href.indexOf('debug=page') != -1) {
 		manifest = imagesForPage
 		window.debugPage = true
 	} else if (location.href.indexOf('debug=game') != -1) {
 		manifest = imagesForGame
  		window.debugGame = true
	} else {
 		manifest = imagesForPage.concat(imagesForGame)
 	}

 	return {
 		load: function(options) {
 			this.queue = new createjs.LoadQueue(false, 'image/')

 			options = options || {}

 			options.onfileload && this.queue.on("fileload", options.onfileload)
			options.onprogress && this.queue.on("progress", options.onprogress)
		 	options.oncomplete && this.queue.on("complete", options.oncomplete)
		 	options.onerror && this.queue.on("error", options.onerror)

		 	this.queue.loadManifest(manifest)
 		},
 		getResult: function(id) {
 			return this.queue.getResult(id)
 		}
 	}

})();


window.music = (function(){
	
	var sounds = [{
		src:"music.mp3",
		data: {
			audioSprite: [
				{id:"part1", startTime:0, duration:32000},
				{id:"part2", startTime:32500, duration:49000}
			]
		}
	}];

	var soundDidLoad = false

	function loadSound() {
		soundDidLoad = true
		music.play(1)
	}

	//@TODO: 绑定touch事件,若!soundDidLoad,则再次registerSounds
	//@TODO: 添加音乐按钮
	return {
		init: function() {
			createjs.Sound.initializeDefaultPlugins();
			createjs.Sound.alternateExtensions = ["mp3"];
			createjs.Sound.on("fileload", loadSound);
			createjs.Sound.registerSounds(sounds, "./", 1);
		},
		play: function(part) {
			createjs.Sound.stop()
			createjs.Sound.play('part' + part, createjs.Sound.INTERRUPT_ANY, 0, 0, -1/*loop*/)
		}
	}

})();




// wechat share config
window.share = (function() {
	var toTimeline = {
		img_url    : 'http://' + location.host + location.pathname.replace(/\/?(index.html)?$/, '/share-ico-bear.png'),
		img_width  : 80,
		img_heigth : 80,
		link       : location.href,
		title      : '奔跑吧！荣嬷嬷！',
		desc       : '奔跑吧！荣嬷嬷！'
	}
	var toFriends = {
		img_url    : toTimeline.img_url,
		img_width  : toTimeline.img_width,
		img_heigth : toTimeline.img_heigth,
		link       : toTimeline.link,
		title      : '奔跑吧！荣嬷嬷！',
		desc       : '奔跑吧！荣嬷嬷！'
	}
	
	if (typeof WeixinJSBridge == "object" && typeof WeixinJSBridge.invoke == "function") {
		init()
	} else {
		if (document.addEventListener) {
			document.addEventListener("WeixinJSBridgeReady", init, false)
		} else if (document.attachEvent) {
			document.attachEvent("WeixinJSBridgeReady", init)
			document.attachEvent("onWeixinJSBridgeReady", init)
		}
	}
	function init(){
		WeixinJSBridge.on("menu:share:appmessage", shareToFriends)
		WeixinJSBridge.on("menu:share:timeline", shareToTimeline)
	}
	function shareToFriends(){
		WeixinJSBridge.invoke("sendAppMessage", toFriends, function (res) {
		})
	}
	function shareToTimeline(){
		WeixinJSBridge.invoke("shareTimeline", toTimeline, function (res) {
		})
	}

	return {
		set: function(options) {
			options = options || {}
			if (options.title) {
				toTimeline.title = options.title
			}
			if (options.desc) {
				toTimeline.desc = options.desc
			}
			if (options.icon) {
				toTimeline.img_url = toTimeline.img_url.replace(/share-ico-\w+\.png/, 'share-ico-' + options.icon + '.png')
			}
		}
	}
})();

halo.use('uievent', 'warn', 'ios7weixin6hack', 'mi3hack', function(m){
	

	function $(id) {
		return document.getElementById(id)
	}

	var page = {
		init: function() {
			$('wrapper').innerHTML = $('tmpl').innerHTML;
			var tmpl = document.createElement('div');
			tmpl.innerHTML = $('tmpl-popup').innerHTML;
			for(var i=0;i<tmpl.childNodes.length;i++){
				var node = tmpl.childNodes[i]
				if(node.nodeType == 1) document.body.appendChild(node)
			}
			
			
			this.wrapper = $('wrapper')
			this.children = this.wrapper.getElementsByClassName('page')
			this.count = this.children.length
			this.turning = false //正在翻页

			this.current = 0
			m.addClass(this.children[0], 'play')

			this.bindEvents()
		},
		bindEvents: function() {
			var touchData, SWAP_BUFFER = 20
			m.on(this.wrapper, 'touchstart', function(e) {
				var touch = e.targetTouches[0]
				touchData = {
					startX: touch.clientX,
					startY: touch.clientY
				}
			})
			m.on(this.wrapper, 'touchmove', function(e) {
				e.preventDefault()
				e.stopPropagation()
			})
			m.on(this.wrapper, 'touchend', function(e) {
				var touch = e.changedTouches[0]
				if (touchData) {
					if (touchData.startY - touch.clientY > SWAP_BUFFER) {
						this.next()
					} else if (touchData.startY - touch.clientY < -SWAP_BUFFER) {
						this.prev()
					}
				}
			}.bind(this))
		},
		next: function(force) {

			if (!this.turning && this.current < this.count - 1 ) {
				this.updateHeight()
				
				var offset = this.height * (this.current + 1)
				if (this.current >= 2) {
					//offset = this.height * this.current //不用+1是因为角色页被隐藏了
				}

				this.wrapper.style.webkitTransform = 'translate3d(0, -' + offset + 'px, 0)'

				var oldPage = this.wrapper.getElementsByClassName('play')[0]
				this.current++
				m.addClass(this.children[this.current], 'play')
				this.turning = true

				setTimeout(function() {
					m.removeClass(oldPage, 'play')
					this.turning = false
				}.bind(this), 500)
			}
		},
		prev: function(force) {
			if (!this.turning && this.current > 0 ) {
				this.updateHeight()
				
				var offset = this.height * (this.current - 1)
				if (this.current > 2) {
					offset = this.height * (this.current - 2) //-2是因为角色页被隐藏了
				}
				this.wrapper.style.webkitTransform = 'translate3d(0, -' + offset + 'px, 0)'

				var oldPage = this.wrapper.getElementsByClassName('play')[0]
				this.current--
				m.addClass(this.children[this.current], 'play')
				this.turning = true

				setTimeout(function() {
					m.removeClass(oldPage, 'play')
					this.turning = false
				}.bind(this), 500)
			}
		},
		updateHeight: function() {
			var rect = $('wrapper').getBoundingClientRect()
			this.height = Math.max(rect.width, rect.height)
		}
	}

	var gameTipsDidShow = false

	//显示/隐藏角色页
	function showRole(role) {
		$('man').style.display = 'none'
		$('girl').style.display = 'none'
		$('bear').style.display = 'none'

		if (role) {
			$('role').style.display = 'block'
			$(role).style.display = 'block'
		} else {
			$('role').style.display = 'none'
		}
	}

	//显示分数
	function showScore(distance, miss, role) {
		

		$('score').innerHTML = distance;

		
		var shareContent = '我已经跑了' + distance + '米'
		window.share.set({
			title: shareContent,
			desc: shareContent,
			icon: role || 'bear'
		})
	}

	//选择完角色开始游戏
	var selectDisabled;
	var ani;
	function playGame(role) {
		
		selectDisabled = true

		//showRole(role)
		//page.next(true)

		//单独调试页面时
		if (window.debugPage) {
			setTimeout(function() {
				m.addClass($('role'), 'slide')

				setTimeout(function() {
					m.removeClass($('role'), 'slide')

					//隐藏角色页,显示结果页
					//showRole(null)
					page.next(true)
				}, 1000)
			}, 3000)

			return;
		}

		window.game.init()
		window.game.reset()

		//复原canvas元素
		$('back-stage').className = ''
		$('front-stage').className = ''


		



			music.play(2)

			

			//开始游戏！
			window.game.start(role, function(distance, miss) {

				console.log('game over',distance + 'm');
				console.log('time:' + window.scoreboard.timer.text)
				//m.removeClass($('role'), 'slide')

				gameTipsDidShow = true;//暂时不显示tip

				if (!miss.total && !gameTipsDidShow) {
					m.addClass($('game-tips'), 'show')
					gameTipsDidShow = true
			
				} else {
					var delay = game.over ? 1000 : 5000

					//隐藏角色页,显示结果页
					//showRole(null)
					showScore(distance, miss, role)
					$('layer-mask').style.display = 'block'
					m.addClass($('score-tips'), 'show') //显示分数牌
					music.play(1)


					/*setTimeout(function() {
						m.addClass($('back-stage'), 'fade-out')
						m.addClass($('front-stage'), 'fade-out')

						page.next(true)
						music.play(1)
						confetti.hide()

						setTimeout(function() {
							$('back-stage').style.zIndex = 'auto'
							$('front-stage').style.zIndex = 'auto'
						}, 500)
					}, delay)*/
				}

				selectDisabled = false
			})

			//m.addClass($('role'), 'slide')
			

			setTimeout(function() {

				$('back-stage').style.zIndex = 10
				$('front-stage').style.zIndex = 10
				
				
			}, 10)
			

		

		
	}

	function bindEvents() {

		m.on($('btn_start'), 'flick', function(e) {
			playGame('bear')
		})
		

		//回选角色页
		/*m.on($('play-again'), 'flick', function(e) {
			//page.current = 2
			page.prev(true)
			playGame('bear')
		})*/

		//点击“我知道啦”
		m.on($('game-tips'), 'flick', function(e) {
			$('game-tips').style.display = 'none'

			//回选角色页
			page.current = 2
			page.prev(true)

			//隐藏canvas
			m.addClass($('back-stage'), 'slide-out-down')
			m.addClass($('front-stage'), 'slide-out-down')
			setTimeout(function() {
				music.play(1)

				$('back-stage').style.zIndex = 'auto'
				$('front-stage').style.zIndex = 'auto'
			}, 500)
		})

		//点击“错过了..”
		m.on($('share-btn'), 'flick', function(e) {
			$('share-tips').style.display = 'block'
			e.preventDefault()
		})


		


		
		

		

		/*//点击“错过了..”
		m.on($('text-next'), 'flick', function(e) {
			page.next()
		})*/

		/*//点击送礼
		m.on($('send-gift'), 'flick', function(e) {
			location.href = 'http://www.paipai.com/m2/2014/5543/index.shtml#js_mod_container'
			e.preventDefault()
		})*/

		/*m.on(document.querySelector('[data-coupon]'), 'flick', function(e) {			
			e.preventDefault()
			$('layer-mask').style.display = 'block';
			$('form-tips').classList.add('show');
			$('layer-mask').setAttribute('data-role','form-tips');
		})*/

		$('layer-mask').addEventListener('touchend',function(e){
			var role = this.getAttribute('data-role') || '';
			if(role !== ''){
				$(role).classList.remove('show');
				this.style.display = 'none';
				this.removeAttribute('data-role');
			}
		},false);


	}

	function init() {
		window.music.init()

		//单独调试游戏时
		if (window.debugGame) {
			$('wrapper').parentNode.removeChild($('wrapper'))
			window.game.init()
			window.game.reset()
			window.game.start()
			return;
		}

		page.init()
		bindEvents()
	}

	//加载资源
	window.loader.load({
		onfileload: function(ev) {
			var percent = parseInt(ev.target.progress * 100) + '%'
			$('percent').innerHTML = percent;
			$('percent').style.width = (parseInt(percent) + 26) + 'px';
		},
		oncomplete: function() {
			$('loading').className += ' scaleOut'
			init()
			setTimeout(function() {
				$('loading').parentNode.removeChild($('loading'))
			}, 500)
		},
		onerror: function() {
			location.reload()
		}
	})

	//touble tap bugfix
	m.on($('loading'), 'dblclick', function(e) {
		e.preventDefault()
	})
	m.on($('wrapper'), 'dblclick', function(e) {
		e.preventDefault()
	})
	m.on(document.body, 'dblclick', function(e) {
		e.preventDefault()
	})

	//set zoom
	var clientWidth = Math.min(document.body.clientWidth, document.body.clientHeight)
	$('wrapper').style.zoom = Math.ceil(clientWidth * 100 / 320) / 100

	m.warn.set('#bb1c32')

});
/*  |xGv00|799065b0bfb0bdc18674233d3bde4f97 */