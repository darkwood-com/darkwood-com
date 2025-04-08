const Darkwood = {
	Ajax: {
		busy: false,
		update(zone, get, post, force = false) {
			if (!this.busy || force) {
				Darkwood.Ajax.on();

				const headers = {
					'X-Requested-With': 'XMLHttpRequest'
				};

				const zoneElement = document.querySelector(zone);
				if (!post || typeof post === 'function') {
					fetch(`/index.php?${new URLSearchParams(get)}`, {
						headers
					})
						.then(response => response.text())
						.then(html => {
							zoneElement.innerHTML = html;
							Darkwood.Ajax.off();
							if (post) post();
							Darkwood.Ajax.clean(zone);
						});
				} else if (typeof post === 'string') {
					const formData = new FormData(document.querySelector(post));
					fetch(`/index.php?${new URLSearchParams(get)}`, {
						method: 'POST',
						headers,
						body: formData
					})
						.then(response => response.text())
						.then(html => {
							zoneElement.innerHTML = html;
							Darkwood.Ajax.clean(zone);
							Darkwood.Ajax.off();
						});
				}
			}
		},
		on() {
			this.busy = true;
		},
		off() {
			this.busy = false;
		},
		clean(zone) {
		}
	},

	Scroll: {
		autoScroll() {
			document.addEventListener('DOMContentLoaded', () => {
				document.querySelectorAll('a[href^="#"]').forEach(anchor => {
					anchor.addEventListener('click', function(e) {
						e.preventDefault();
						const target = document.querySelector(this.getAttribute('href'));
						if (target) {
							target.scrollIntoView({
								behavior: 'smooth',
								duration: 700
							});
						}
					});
				});
			});
		}
	},

	Download: {
		autoUpdateValues(txt) {
			document.addEventListener('DOMContentLoaded', () => {
				this.updateValues(txt);

				document.querySelectorAll('a.download').forEach(el => {
					el.addEventListener('click', function() {
						fetch(`/index.php?template=download/download&download=${this.title}`, {
							headers: {
								'X-Requested-With': 'XMLHttpRequest'
							}
						})
							.then(() => {
								Darkwood.Download.updateValues(txt);
							});
					});
				});
			});
		},

		updateValues(txt) {
			const downloadSpans = document.querySelectorAll('span.download');
			if (!downloadSpans.length) {
				return;
			}

			fetch('/index.php?template=download/index', {
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			})
				.then(response => response.text())
				.then(text => {
					const parser = new DOMParser();
					const xmlDoc = parser.parseFromString(text, 'text/xml');
					const downloads = new Map();

					xmlDoc.querySelectorAll('download').forEach(download => {
						downloads.set(
							download.getAttribute('name'),
							download.getAttribute('count')
						);
					});

					downloadSpans.forEach(el => {
						const count = downloads.get(el.title) || 0;
						el.innerHTML = txt.replace('*', count);
					});
				});
		}
	},

	Applet: {
		call(zone, jarFile, jarFolder, classExec, javaVersion, width, height, title, comments) {
			const container = document.querySelector(zone);
			container.innerHTML = '';

			const object = document.createElement('object');
			object.setAttribute('width', `${width}px`);
			object.setAttribute('height', `${height}px`);
			object.setAttribute('name', `${title}px`);

			if (navigator.userAgent.indexOf("MSIE") >= 0) {
				object.setAttribute('classid', 'clsid:8AD9C840-044E-11D1-B3E9-00805F499D93');

				const params = [
					{ name: 'java_code', value: `${classExec}.class` },
					{ name: 'java_codebase', value: jarFolder },
					{ name: 'java_archive', value: jarFile },
					{ name: 'type', value: `application/x-java-applet;version=${javaVersion}` }
				];

				params.forEach(param => {
					const paramElement = document.createElement('param');
					paramElement.setAttribute('name', param.name);
					paramElement.setAttribute('value', param.value);
					object.appendChild(paramElement);
				});
			} else {
				object.setAttribute('classid', `java:${classExec}.class`);
				object.setAttribute('type', `application/x-java-applet;version=${javaVersion}`);
				object.setAttribute('archive', jarFolder + jarFile);

				const archiveParam = document.createElement('param');
				archiveParam.setAttribute('archive', jarFolder + jarFile);
				object.appendChild(archiveParam);
			}

			const commentsParam = document.createElement('param');
			commentsParam.setAttribute('decrypted-text', comments);
			object.appendChild(commentsParam);

			container.appendChild(object);
		}
	},

	Video: {
		play(zone, src, width, height, title) {
			const container = document.querySelector(zone);
			container.innerHTML = '';

			const object = document.createElement('object');
			object.setAttribute('classid', 'clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B');
			object.setAttribute('codebase', 'http://www.apple.com/qtactivex/qtplugin.cab#version=6,0,2,0');
			object.setAttribute('width', `${width}px`);
			object.setAttribute('height', `${height}px`);

			const params = [
				{ name: 'pluginspage', value: 'http://www.apple.com/quicktime/download/indext.html' },
				{ name: 'type', value: 'video/quicktime' },
				{ name: 'src', value: src },
				{ name: 'controller', value: 'true' },
				{ name: 'autoplay', value: 'true' }
			];

			params.forEach(param => {
				const paramElement = document.createElement('param');
				paramElement.setAttribute('name', param.name);
				paramElement.setAttribute('value', param.value);
				object.appendChild(paramElement);
			});

			const embed = document.createElement('embed');
			const embedAttributes = {
				width,
				height,
				hspace: 0,
				vspace: 5,
				controller: 'true',
				src,
				type: 'video/quicktime',
				bgcolor: '#000000',
				border: 0,
				frameborder: 'no',
				palette: 'foreground',
				pluginspace: 'http://www.apple.com/quicktime/download/indext.html',
				title
			};

			Object.entries(embedAttributes).forEach(([key, value]) => {
				embed.setAttribute(key, value);
			});

			object.appendChild(embed);
			container.appendChild(object);
		}
	}
};
