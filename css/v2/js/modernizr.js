/*
*!
*.
* !modificacion 06.06.2016
* ! modernizr 3.3.1 (Custom Build) | MIT *
* https://modernizr.com/download/?-addtest-atrule-domprefixes-hasevent-mq-prefixed-prefixedcss-prefixedcssvalue-prefixes-setclasses-testallprops-testprop-teststyles !*/
!function(e,n,t){function r(e,n){return typeof e===n}function o(){var e,n,t,o,i,s,u;for(var a in C)if(C.hasOwnProperty(a)){if(e=[],n=C[a],n.name&&(e.push(n.name.toLowerCase()),n.options&&n.options.aliases&&n.options.aliases.length))for(t=0;t<n.options.aliases.length;t++)e.push(n.options.aliases[t].toLowerCase());for(o=r(n.fn,"function")?n.fn():n.fn,i=0;i<e.length;i++)s=e[i],u=s.split("."),1===u.length?Modernizr[u[0]]=o:(!Modernizr[u[0]]||Modernizr[u[0]]instanceof Boolean||(Modernizr[u[0]]=new Boolean(Modernizr[u[0]])),Modernizr[u[0]][u[1]]=o),_.push((o?"":"no-")+u.join("-"))}}function i(e){var n=x.className,t=Modernizr._config.classPrefix||"";if(b&&(n=n.baseVal),Modernizr._config.enableJSClass){var r=new RegExp("(^|\\s)"+t+"no-js(\\s|$)");n=n.replace(r,"$1"+t+"js$2")}Modernizr._config.enableClasses&&(n+=" "+t+e.join(" "+t),b?x.className.baseVal=n:x.className=n)}function s(e,n){if("object"==typeof e)for(var t in e)z(e,t)&&s(t,e[t]);else{e=e.toLowerCase();var r=e.split("."),o=Modernizr[r[0]];if(2==r.length&&(o=o[r[1]]),"undefined"!=typeof o)return Modernizr;n="function"==typeof n?n():n,1==r.length?Modernizr[r[0]]=n:(!Modernizr[r[0]]||Modernizr[r[0]]instanceof Boolean||(Modernizr[r[0]]=new Boolean(Modernizr[r[0]])),Modernizr[r[0]][r[1]]=n),i([(n&&0!=n?"":"no-")+r.join("-")]),Modernizr._trigger(e,n)}return Modernizr}function u(){return"function"!=typeof n.createElement?n.createElement(arguments[0]):b?n.createElementNS.call(n,"http://www.w3.org/2000/svg",arguments[0]):n.createElement.apply(n,arguments)}function a(e){return e.replace(/([a-z])-([a-z])/g,function(e,n,t){return n+t.toUpperCase()}).replace(/^-/,"")}function f(e){return e.replace(/([A-Z])/g,function(e,n){return"-"+n.toLowerCase()}).replace(/^ms-/,"-ms-")}function l(){var e=n.body;return e||(e=u(b?"svg":"body"),e.fake=!0),e}function c(e,t,r,o){var i,s,a,f,c="modernizr",p=u("div"),d=l();if(parseInt(r,10))for(;r--;)a=u("div"),a.id=o?o[r]:c+(r+1),p.appendChild(a);return i=u("style"),i.type="text/css",i.id="s"+c,(d.fake?d:p).appendChild(i),d.appendChild(p),i.styleSheet?i.styleSheet.cssText=e:i.appendChild(n.createTextNode(e)),p.id=c,d.fake&&(d.style.background="",d.style.overflow="hidden",f=x.style.overflow,x.style.overflow="hidden",x.appendChild(d)),s=t(p,e),d.fake?(d.parentNode.removeChild(d),x.style.overflow=f,x.offsetHeight):p.parentNode.removeChild(p),!!s}function p(e,n){return!!~(""+e).indexOf(n)}function d(n,r){var o=n.length;if("CSS"in e&&"supports"in e.CSS){for(;o--;)if(e.CSS.supports(f(n[o]),r))return!0;return!1}if("CSSSupportsRule"in e){for(var i=[];o--;)i.push("("+f(n[o])+":"+r+")");return i=i.join(" or "),c("@supports ("+i+") { #modernizr { position: absolute; } }",function(e){return"absolute"==getComputedStyle(e,null).position})}return t}function v(e,n){return function(){return e.apply(n,arguments)}}function m(e,n,t){var o;for(var i in e)if(e[i]in n)return t===!1?e[i]:(o=n[e[i]],r(o,"function")?v(o,t||n):o);return!1}function h(e,n,o,i){function s(){l&&(delete k.style,delete k.modElem)}if(i=r(i,"undefined")?!1:i,!r(o,"undefined")){var f=d(e,o);if(!r(f,"undefined"))return f}for(var l,c,v,m,h,y=["modernizr","tspan","samp"];!k.style&&y.length;)l=!0,k.modElem=u(y.shift()),k.style=k.modElem.style;for(v=e.length,c=0;v>c;c++)if(m=e[c],h=k.style[m],p(m,"-")&&(m=a(m)),k.style[m]!==t){if(i||r(o,"undefined"))return s(),"pfx"==n?m:!0;try{k.style[m]=o}catch(g){}if(k.style[m]!=h)return s(),"pfx"==n?m:!0}return s(),!1}function y(e,n,t,o,i){var s=e.charAt(0).toUpperCase()+e.slice(1),u=(e+" "+T.join(s+" ")+s).split(" ");return r(n,"string")||r(n,"undefined")?h(u,n,o,i):(u=(e+" "+E.join(s+" ")+s).split(" "),m(u,n,t))}function g(e,n,r){return y(e,t,t,n,r)}var _=[],C=[],S={_version:"3.3.1",_config:{classPrefix:"",enableClasses:!0,enableJSClass:!0,usePrefixes:!0},_q:[],on:function(e,n){var t=this;setTimeout(function(){n(t[e])},0)},addTest:function(e,n,t){C.push({name:e,fn:n,options:t})},addAsyncTest:function(e){C.push({name:null,fn:e})}},Modernizr=function(){};Modernizr.prototype=S,Modernizr=new Modernizr;var w=S._config.usePrefixes?" -webkit- -moz- -o- -ms- ".split(" "):["",""];S._prefixes=w;var x=n.documentElement,b="svg"===x.nodeName.toLowerCase(),P="Moz O ms Webkit",E=S._config.usePrefixes?P.toLowerCase().split(" "):[];S._domPrefixes=E;var z;!function(){var e={}.hasOwnProperty;z=r(e,"undefined")||r(e.call,"undefined")?function(e,n){return n in e&&r(e.constructor.prototype[n],"undefined")}:function(n,t){return e.call(n,t)}}(),S._l={},S.on=function(e,n){this._l[e]||(this._l[e]=[]),this._l[e].push(n),Modernizr.hasOwnProperty(e)&&setTimeout(function(){Modernizr._trigger(e,Modernizr[e])},0)},S._trigger=function(e,n){if(this._l[e]){var t=this._l[e];setTimeout(function(){var e,r;for(e=0;e<t.length;e++)(r=t[e])(n)},0),delete this._l[e]}},Modernizr._q.push(function(){S.addTest=s});var T=S._config.usePrefixes?P.split(" "):[];S._cssomPrefixes=T;var j=function(n){var r,o=w.length,i=e.CSSRule;if("undefined"==typeof i)return t;if(!n)return!1;if(n=n.replace(/^@/,""),r=n.replace(/-/g,"_").toUpperCase()+"_RULE",r in i)return"@"+n;for(var s=0;o>s;s++){var u=w[s],a=u.toUpperCase()+"_"+r;if(a in i)return"@-"+u.toLowerCase()+"-"+n}return!1};S.atRule=j;var A=function(){function e(e,n){var o;return e?(n&&"string"!=typeof n||(n=u(n||"div")),e="on"+e,o=e in n,!o&&r&&(n.setAttribute||(n=u("div")),n.setAttribute(e,""),o="function"==typeof n[e],n[e]!==t&&(n[e]=t),n.removeAttribute(e)),o):!1}var r=!("onblur"in n.documentElement);return e}();S.hasEvent=A;var N=function(e,n){var t=!1,r=u("div"),o=r.style;if(e in o){var i=E.length;for(o[e]=n,t=o[e];i--&&!t;)o[e]="-"+E[i]+"-"+n,t=o[e]}return""===t&&(t=!1),t};S.prefixedCSSValue=N;var L=function(){var n=e.matchMedia||e.msMatchMedia;return n?function(e){var t=n(e);return t&&t.matches||!1}:function(n){var t=!1;return c("@media "+n+" { #modernizr { position: absolute; } }",function(n){t="absolute"==(e.getComputedStyle?e.getComputedStyle(n,null):n.currentStyle).position}),t}}();S.mq=L;var O=(S.testStyles=c,{elem:u("modernizr")});Modernizr._q.push(function(){delete O.elem});var k={style:O.elem.style};Modernizr._q.unshift(function(){delete k.style});S.testProp=function(e,n,r){return h([e],t,n,r)};S.testAllProps=y;var q=S.prefixed=function(e,n,t){return 0===e.indexOf("@")?j(e):(-1!=e.indexOf("-")&&(e=a(e)),n?y(e,n,t):y(e,"pfx"))};S.prefixedCSS=function(e){var n=q(e);return n&&f(n)};S.testAllProps=g,o(),i(_),delete S.addTest,delete S.addAsyncTest;for(var R=0;R<Modernizr._q.length;R++)Modernizr._q[R]();e.Modernizr=Modernizr}(window,document);