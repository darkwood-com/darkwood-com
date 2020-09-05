/****************************************************************************
 Copyright (c) 2010-2012 cocos2d-x.org
 Copyright (c) 2008-2010 Ricardo Quesada
 Copyright (c) 2011      Zynga Inc.

 http://www.cocos2d-x.org


 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE.
 ****************************************************************************/

/*function $type(obj){
    if (obj == undefined) return false;
    if (obj.$family) return (obj.$family.name == 'number' && !isFinite(obj)) ? false : obj.$family.name;
    if (obj.nodeName){
        switch (obj.nodeType){
            case 1: return 'element';
            case 3: return (/\S/).test(obj.nodeValue) ? 'textnode' : 'whitespace';
        }
    } else if (typeof obj.length == 'number'){
        if (obj.callee) return 'arguments';
        else if (obj.item) return 'collection';
    }
    return typeof obj;
};

var JSON = {

    $specialChars: {'\b': '\\b', '\t': '\\t', '\n': '\\n', '\f': '\\f', '\r': '\\r', '"' : '\\"', '\\': '\\\\'},

    $replaceChars: function(chr){
        return JSON.$specialChars[chr] || '\\u00' + Math.floor(chr.charCodeAt() / 16).toString(16) + (chr.charCodeAt() % 16).toString(16);
    },

    encode: function(obj){
        switch ($type(obj)){
            case 'string':
                return '"' + obj.replace(/[\x00-\x1f\\"]/g, JSON.$replaceChars) + '"';
            case 'array':
                return '[' + String(obj.map(JSON.encode).filter($defined)) + ']';
            case 'object': case 'hash':
            var string = [];
            $.each(obj, function(value, key){
                var json = JSON.encode(value);
                if (json) string.push(JSON.encode(key) + ':' + json);
            });
            return '{' + string + '}';
            case 'number': case 'boolean': return String(obj);
            case false: return 'null';
        }
        return null;
    },

    decode: function(string, secure){
        if ($type(string) != 'string' || !string.length) return null;
        if (secure && !(/^[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]*$/).test(string.replace(/\\./g, '@').replace(/"[^"\\\n\r]*"/g, ''))) return null;
        return eval('(' + string + ')');
    }

};

JSON.parse = JSON.decode;
JSON.stringify = JSON.encode;*/

var cpz = cpz || {};
cpz.CommonPath = '../../Common/Bin/Data/';
cpz.CommonSrcPath = cpz.CommonPath + 'js/';

document.addEventListener('DOMContentLoaded', function() {
	cc.loader.audioPath = cc.loader.resPath;

	cc.game.onStart = function(){
		var chinesePuzzle = null;
	
		//view resize
		var view = cc.view;
		var callback = function() {
			var w = window,
				d = document,
				e = d.documentElement,
				g = d.getElementsByTagName('body')[0],
				x = w.innerWidth || e.clientWidth || g.clientWidth,
				y = w.innerHeight|| e.clientHeight|| g.clientHeight;
	
			view.setDesignResolutionSize(x, y, cc.ResolutionPolicy.NO_BORDER);
	
			if(chinesePuzzle) {
				chinesePuzzle.reshape();
			}
		};
		//view.resizeWithBrowserSize(true);
		//view.setResizeCallback(callback);
		//callback.call();
	
		//view.setDesignResolutionSize(1024,768,cc.ResolutionPolicy.SHOW_ALL);
	
		cc.LoaderScene.preload(cpz.Resources, function () {
			cpz.GameConfig.getResolutions = function() {
				return [
					'480x320'
				];
			};
		
			setTimeout(function() {
				chinesePuzzle = cpz.GameScene.create();
				cc.director.runScene(chinesePuzzle);
			}, 100)
		}, this);
	};
	cc.game.run();
});
