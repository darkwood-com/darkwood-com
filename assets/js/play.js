document.addEventListener('DOMContentLoaded', () => {
	const play = document.getElementById('play');
	if (!play) {
		return;
	}

	const url = play.dataset.playUrl;

	play.addEventListener('click', (e) => {
		const target = e.target.closest('a.go');
		if (!target) return;

		e.preventDefault();

		const data = Object.entries(target.dataset)
			.map(([key, value]) => `${encodeURIComponent(key)}=${encodeURIComponent(value)}`)
			.join('&');

		fetch(`${url}?${data}`)
			.then(response => response.text())
			.then(html => {
				play.innerHTML = html;
			});
	});
});
