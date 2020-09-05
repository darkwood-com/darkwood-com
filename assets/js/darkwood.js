var Darkwood = {};

Darkwood.Ajax = {
	busy:false,
	update:function(zone, get, post, force)
	{
		if(force == undefined)
		{
			force = false;
		}
		
		if(!this.busy || force)
		{
			Darkwood.Ajax.on();
			if(!$type(post) || $type(post) == "function")
			{
				new Request.HTML({
					url: '/index.php',
					method:'get',
					update:zone,
					data:get,
					onComplete:function()
					{
						Darkwood.Ajax.off();
						if(post)post();
						Darkwood.Ajax.clean(zone);
					}
				}).send();
			}
			else if($type(post) == "string")
			{
				post = $(post);
				
				new Request.HTML({
					url:"/index.php?" + Object.toQueryString(get),
					update:zone,
					data:post,
					onComplete:function(){
						Darkwood.Ajax.clean(zone);
						Darkwood.Ajax.off();
					}
				}).send();
			}
		}
	},
	on: function()
	{
		this.busy = true;
	},
	off: function()
	{
		this.busy = false;
	},
	clean: function(zone)
	{
		
	}
}

Darkwood.Scrool = {
	autoScrool:function()
	{
		window.addEvent('domready', function() {
			new SmoothScroll({ duration:700 }, window);
		});
	}
}

Darkwood.Download = {
	autoUpdateValues:function(txt)
	{
		window.addEvent('domready', function() {
			Darkwood.Download.updateValues(txt);
			
			$$('a.download').each(function(el){
				el.addEvent('click', function(){
					new Request.HTML({
						url:"/index.php?template=download/download&download=" + this.title,
						onComplete:function(){
							Darkwood.Download.updateValues(txt);
						},
						async:false
					}).send();
				});
			});
		});
	},
	
	updateValues:function(txt)
	{
		if(!$$('span.download').length)
		{
			return;
		}
		
		new Request.HTML({
			url:"/index.php?template=download/index",
			onComplete:function(){
				var download = new Hash({});
				
				var data = this.response.xml.documentElement.getElementsByTagName("download");
				for(var i=0;i<data.length;i++)
				{
					download.set(data[i].getAttribute('name'), data[i].getAttribute('count'));
				}
				
				$$('span.download').each(function(el) {
					var count = 0;
					if(download.has(el.title))
					{
						count = download[el.title];
					}
					
					el.innerHTML = txt.replace('*', count);
				});
			},
			async:false
		}).send();
	}
}

Darkwood.Applet = {
	call:function(zone, jarFile, jarFolder, classExec, javaVersion, width, height, title, comments)
	{
		zone = $(zone);
		zone.empty();
		
		var o = $('<object />').get(0);
		o.setAttribute('width', width + 'px');
		o.setAttribute('height', height + 'px');
		o.setAttribute('name', title + 'px');
		
		if (navigator.userAgent.indexOf("MSIE") >= 0)
		{
			//Internet explorer
			o.setAttribute('classid', 'clsid:8AD9C840-044E-11D1-B3E9-00805F499D93');

			var param = $('<param />').get(0);
			param.setAttribute('name', 'java_code');
			param.setAttribute('value', classExec + '.class');
			o.appendChild(param);
			
			var param = $('<param />').get(0);
			param.setAttribute('name', 'java_codebase');
			param.setAttribute('value', jarFolder);
			o.appendChild(param);
			
			var param = $('<param />').get(0);
			param.setAttribute('name', 'java_archive');
			param.setAttribute('value', jarFile);
			o.appendChild(param);
			
			var param = $('<param />').get(0);
			param.setAttribute('name', 'type');
			param.setAttribute('value', 'application/x-java-applet;version=' + javaVersion);
			o.appendChild(param);
		}
		else
		{
			//Mozila/Safari/Konqueror
			o.setAttribute('classid', 'java:' + classExec + '.class');
			o.setAttribute('type', 'application/x-java-applet;version=' + javaVersion);
			o.setAttribute('archive', jarFolder + jarFile);
			
			//Konqueror browser needs the following param
			var param = $('<param />').get(0);
			param.setAttribute('archive', jarFolder + jarFile);
			o.appendChild(param);
		}
		
		var param = $('<param />').get(0);
		param.setAttribute('decrypted-text', comments);
		o.appendChild(param);

		zone.append(o);
	}
}

Darkwood.Video = {
	play:function(zone, src, width, height, title)
	{
		zone = $(zone);
		zone.empty();
		
		var o = $('<object />').get(0);
		o.setAttribute('classid', 'clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B');
		o.setAttribute('codebase', 'http://www.apple.com/qtactivex/qtplugin.cab#version=6,0,2,0');
		o.setAttribute('width', width + 'px');
		o.setAttribute('height', height + 'px');
		
		var param = $('<param />').get(0);
		param.setAttribute('name', 'pluginspage');
		param.setAttribute('value', 'http://www.apple.com/quicktime/download/indext.html');
		o.appendChild(param);
		
		var param = $('<param />').get(0);
		param.setAttribute('name', 'type');
		param.setAttribute('value', 'video/quicktime');
		o.appendChild(param);
		
		var param = $('<param />').get(0);
		param.setAttribute('name', 'src');
		param.setAttribute('value', src);
		o.appendChild(param);
		
		var param = $('<param />').get(0);
		param.setAttribute('name', 'controller');
		param.setAttribute('value', 'true');
		o.appendChild(param);
		
		var param = $('<param />').get(0);
		param.setAttribute('name', 'autoplay');
		param.setAttribute('value', 'true');
		o.appendChild(param);

		var embed = new Element('embed');
		embed.setAttribute('width', width);
		embed.setAttribute('height', height);
		embed.setAttribute('hspace', 0);
		embed.setAttribute('vspace', 5);
		embed.setAttribute('controller', 'true');
		embed.setAttribute('src', src);
		embed.setAttribute('type', 'video/quicktime');
		embed.setAttribute('bgcolor', '#000000');
		embed.setAttribute('border', 0);
		embed.setAttribute('frameborder', 'no');
		embed.setAttribute('palette', 'foreground');
		embed.setAttribute('pluginspace', 'http://www.apple.com/quicktime/download/indext.html');
		embed.setAttribute('title', title);
		o.appendChild(embed);
		
		zone.append(o);
	}
};