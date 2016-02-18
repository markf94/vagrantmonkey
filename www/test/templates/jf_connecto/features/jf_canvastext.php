<?php
/**
* @version		JF_DTF_078
* @author		JoomForest http://www.joomforest.com
* @copyright	Copyright (C) 2011-2015 JoomForest.com
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

defined('JPATH_BASE') or die();
gantry_import('core.gantryfeature');

class GantryFeatureJf_CanvasText extends GantryFeature {
    var $_feature_name = 'jf_canvastext';

	function init() {
		global $gantry, $option;

		if ($this->get('enabled')) {
		
			JHtml::_('jquery.framework');
			
			// GLOBAL
				$jf_doc = JFactory::getDocument();
				$getapp = JFactory::getApplication();
				$template = $getapp->getTemplate();
				$jf_template_dir = JURI::base().'templates/'.$template;
			// MAIN VARS
				$jf_canvastext_words		= $this->get('jf_canvastext_words');
				$jf_canvastext_height		= $this->get('jf_canvastext_height');
				$jf_canvastext_padding		= $this->get('jf_canvastext_padding');
				$jf_canvastext_fontsize		= $this->get('jf_canvastext_fontsize');
				$jf_canvastext_fontfamily	= $this->get('jf_canvastext_fontfamily');
				$jf_canvastext_color		= $this->get('jf_canvastext_color');
			
			// CALL FUNCTION
				// MAIN GLOBAL
					// CSS
						$jf_doc->addStyleSheet($jf_template_dir.'/features/jf_canvastext/jf_canvastext.min.css');
						$jf_doc->addStyleDeclaration('.nav-circlepop a::before,.nav-circlepop .icon-wrap::before,.nav-circlepop .icon-wrap::after{background:'.$jf_canvastext_color.'}.jf_CanvasText{'.$jf_canvastext_padding.'}');
					// JS
						$jf_doc->addScript($jf_template_dir.'/features/jf_canvastext/modernizr.custom.js');
					
						$gantry->addInlineScript('var self=window;(function(self){var canvas,context,particles=[],text=[],nextText=[],shape={},mouse={x:-99999,y:-99999},currentTransition="",left,right,layout=0,type=['.$jf_canvastext_words.'],FPS=60,words=['.$jf_canvastext_words.'],colors={circle:["#ffffff","#2c3e50"],ovals:["#c0392b","#ff7e15"],drop:["#1d75cf","#3a5945"],ribbon:["#702744","#f98d00"]};function init(){var slideshowContainer=document.querySelector(".ip-slideshow");canvas=document.createElement("canvas");canvas.width=slideshowContainer.offsetWidth;canvas.height='.$jf_canvastext_height.';slideshowContainer.appendChild(canvas);if(!!(capable)){context=canvas.getContext("2d");if("ontouchmove"in window){canvas.addEventListener("touchup",onTouchUp,false);canvas.addEventListener("touchmove",onTouchMove,false)}else{canvas.addEventListener("mousemove",onMouseMove,false)}handleClick("bind","left");handleClick("bind","right");window.onresize=onResize;createParticles();left=document.querySelector(".ip-nav-left");right=document.querySelector(".ip-nav-right");right.classList.add("ip-nav-show")}else{console.error("Sorry, switch to a better browser to see this experiment.")}}function capable(){return canvas.getContext&&canvas.getContext("2d")}function onResize(){var slideshowContainer=document.querySelector(".ip-slideshow");canvas.width=slideshowContainer.offsetWidth;canvas.height='.$jf_canvastext_height.';nextText=[];updateText()}function scrollX(){return window.pageXOffset||window.document.documentElement.scrollLeft}function scrollY(){return window.pageYOffset||window.document.documentElement.scrollTop}function onMouseMove(event){event.preventDefault();mouse.x=event.pageX-(scrollX()+canvas.getBoundingClientRect().left);mouse.y=event.pageY-(scrollY()+canvas.getBoundingClientRect().top)}function onTouchUp(event){event.preventDefault();mouse={x:-99999,y:-99999}}function onTouchMove(event){event.preventDefault();mouse.x=event.touches[0].pageX-(scrollX()+canvas.getBoundingClientRect().left);mouse.y=event.touches[0].pageY-(scrollY()+canvas.getBoundingClientRect().top)}function onLeftClick(event){event.preventDefault();currentTransition=type[Math.max(0,--layout)];nextText=[];updateText();if(layout===0){left.classList.remove("ip-nav-show");handleClick("unbind","left");return}if(layout===type.length-2){right.classList.add("ip-nav-show");handleClick("bind","right")}}function onRightClick(event){event.preventDefault();currentTransition=type[Math.min(type.length,++layout)];nextText=[];updateText();if(layout===1){left.classList.add("ip-nav-show");handleClick("bind","left")}if(layout===type.length-1){right.classList.contains("ip-nav-show")?right.classList.remove("ip-nav-show"):null;handleClick("unbind","right")}}function handleClick(action,type){var direction=type==="left"?onLeftClick:onRightClick;switch(action){case"bind":document.querySelector(".ip-nav-"+type).addEventListener("touchstart",direction,false);document.querySelector(".ip-nav-"+type).addEventListener("click",direction,false);document.querySelector(".ip-nav-"+type).style.cursor="pointer";break;case"unbind":document.querySelector(".ip-nav-"+type).removeEventListener("touchstart",direction,false);document.querySelector(".ip-nav-"+type).removeEventListener("click",direction,false);document.querySelector(".ip-nav-"+type).style.cursor="default";break;default:break}}function createParticles(){for(var quantity=0,len=200;quantity<len;quantity++){var x,y,steps,steps=Math.PI*2*quantity/len;x=canvas.width*0.5+10*Math.cos(steps);y=180+10*Math.sin(steps);var radius=randomBetween(0,12);var hasBorn=radius>0||radius<12?false:true;var color=colors.circle[~~(Math.random()*colors.circle.length)];particles.push({x:x,y:y,hasBorn:hasBorn,ease:0.04+Math.random()*0.06,bornSpeed:0.07+Math.random()*0.07,alpha:0,maxAlpha:0.7+Math.random()*0.4,radius:radius,maxRadius:12,color:color,interactive:false,angle:0,steps:steps})}updateText();loop()}function createTextParticles(seed){for(var quantity=0,len=seed;quantity<len;quantity++){var radius=randomBetween(0,12);var hasBorn=radius>0||radius<12?false:true;var color=colors.circle[~~(Math.random()*colors.circle.length)];text.push({x:canvas.width*0.5,y:canvas.height-70,hasBorn:hasBorn,ease:0.04+Math.random()*0.06,bornSpeed:0.07+Math.random()*0.07,alpha:0,maxAlpha:0.7+Math.random()*0.4,radius:radius,maxRadius:12,color:color,interactive:false})}}function updateText(){clear();context.font='.$jf_canvastext_fontsize.'+"px '.$jf_canvastext_fontfamily.'";context.fillStyle="rgb(255, 255, 255)";context.textAlign="center";var strip=words[layout].toUpperCase().split("").join(String.fromCharCode(8202));context.fillText(strip,canvas.width*0.5,canvas.height-50);var surface=context.getImageData(0,0,canvas.width,canvas.height);for(var width=0;width<surface.width;width+=4){for(var height=0;height<surface.height;height+=4){var color=surface.data[(height*surface.width*4)+(width*4)-1];if(color===255){nextText.push({x:width,y:height,orbit:randomBetween(1,3),angle:0})}}}var seed=nextText.length;createTextParticles(seed)}function updateTransition(){[].forEach.call(particles,function(particle,index){switch(currentTransition){case"circle":shape.x=canvas.width*0.5+140*Math.sin(particle.steps);shape.y=180+140*Math.cos(particle.steps);break;case"ovals":var limit,steps;limit=(particles.length*0.5)-1;steps=Math.PI*2*index/limit;if(index<[].slice.call(particles,0,limit).length){shape.x=canvas.width*0.5+80*Math.cos(steps);shape.y=180+140*Math.sin(steps)}else{limit=(particles.length*0.5);shape.x=canvas.width*0.5+140*Math.cos(steps);shape.y=180+80*Math.sin(steps)}break;case"drop":shape.x=canvas.width*0.5+90*(1-Math.sin(index))*Math.cos(index);shape.y=320+140*(-Math.sin(index)-1);break;case"ribbon":shape.x=canvas.width*0.5+90*(Math.sin(index))*Math.cos(index);shape.y=320+140*(-Math.sin(index)-1);break;default:break}if(!particle.interactive){particle.x+=((shape.x+Math.cos(particle.angle)*5)-particle.x)*0.08;particle.y+=((shape.y+Math.sin(particle.angle)*5)-particle.y)*0.08}else{particle.x+=((mouse.x+Math.sin(particle.angle)*30)-particle.x)*0.08;particle.y+=((mouse.y+Math.cos(particle.angle)*30)-particle.y)*0.08}particle.angle+=0.08});[].forEach.call(nextText,function(particle,index){if(!text[index].interactive){text[index].x+=((particle.x+Math.cos(particle.angle+index)*particle.orbit)-text[index].x)*0.08;text[index].y+=((particle.y+Math.sin(particle.angle+index)*particle.orbit)-text[index].y)*0.08}else{text[index].x+=((mouse.x+Math.sin(particle.angle)*30)-text[index].x)*0.08;text[index].y+=((mouse.y+Math.cos(particle.angle)*30)-text[index].y)*0.08}particle.angle+=0.08});if(nextText.length<text.length){var extra=[].slice.call(text,nextText.length,text.length);for(var index=0;index<extra.length;index++)text.splice(index,1)}}function loop(){clear();update();render();requestAnimFrame(loop)}function clear(){context.clearRect(0,0,canvas.width,canvas.height)}function update(){updateTransition();[].forEach.call(particles,function(particle,index){particle.alpha+=(particle.maxAlpha-particle.alpha)*0.05;if(particle.hasBorn){particle.radius+=(0-particle.radius)*particle.bornSpeed;if(Math.round(particle.radius)===0){switch(currentTransition){case"circle":particle.color=colors.circle[~~(Math.random()*colors.circle.length)];break;case"ovals":particle.color=colors.ovals[~~(Math.random()*colors.ovals.length)];break;case"drop":particle.color=colors.drop[~~(Math.random()*colors.drop.length)];break;case"ribbon":particle.color=colors.ribbon[~~(Math.random()*colors.ribbon.length)];break;case"heart":particle.color=colors.heart[~~(Math.random()*colors.heart.length)];break;default:break}particle.hasBorn=false}}if(!particle.hasBorn){particle.radius+=(particle.maxRadius-particle.radius)*particle.bornSpeed;if(Math.round(particle.radius)===particle.maxRadius)particle.hasBorn=true}distanceTo(mouse,particle)<=particle.radius+30?particle.interactive=true:particle.interactive=false});[].forEach.call(text,function(particle,index){particle.alpha+=(particle.maxAlpha-particle.alpha)*0.05;if(particle.hasBorn){particle.radius+=(0-particle.radius)*particle.bornSpeed;if(Math.round(particle.radius)===0)particle.hasBorn=false}if(!particle.hasBorn){particle.radius+=(particle.maxRadius-particle.radius)*particle.bornSpeed;if(Math.round(particle.radius)===particle.maxRadius)particle.hasBorn=true}distanceTo(mouse,particle)<=particle.radius+30?particle.interactive=true:particle.interactive=false})}function render(){[].forEach.call(particles,function(particle,index){context.save();context.globalAlpha=particle.alpha;context.fillStyle=particle.color;context.beginPath();context.arc(particle.x,particle.y,particle.radius,0,Math.PI*2);context.fill();context.restore()});[].forEach.call(text,function(particle,index){context.save();context.globalAlpha=particle.alpha;context.fillStyle="'.$jf_canvastext_color.'";context.beginPath();context.arc(particle.x,particle.y,particle.radius,0,Math.PI*2);context.fill();context.restore()})}function distanceTo(pointA,pointB){var dx=Math.abs(pointA.x-pointB.x);var dy=Math.abs(pointA.y-pointB.y);return Math.sqrt(dx*dx+dy*dy)}function randomBetween(min,max){return~~(Math.random()*(max-min+1)+min)}window.requestAnimFrame=(function(){return window.requestAnimationFrame||window.webkitRequestAnimationFrame||window.mozRequestAnimationFrame||window.oRequestAnimationFrame||window.msRequestAnimationFrame||function(callback){window.setTimeout(callback,'.$jf_canvastext_fontsize.'0/FPS)}})();window.addEventListener?window.addEventListener("load",init,false):window.onload=init})(self);');
					// IF There is only 1 slide, then hide navigation
						if (strpos($jf_canvastext_words,',') !== false) {
							// there are MORE then 1 slide
						} else {
							// $jf_doc->addScriptDeclaration('alert("1 slide"); ');
							$jf_doc->addStyleDeclaration('.nav-circlepop{display:none}');
						}
		}
		
    }

	function isOrderable() {
		return false;
	}

}