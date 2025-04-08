window.addEventListener('DOMContentLoaded', event => {
	const helloLanding = document.body.querySelector('.theme-hello');
	if (!helloLanding) {
		return;
	}

    // Navbar shrink function
    var navbarShrink = function () {
        const navbarCollapsible = document.body.querySelector('.theme-hello .main-nav');
        if (!navbarCollapsible) {
            return;
        }
        if (window.scrollY === 0) {
            navbarCollapsible.classList.remove('navbar-shrink')
        } else {
            navbarCollapsible.classList.add('navbar-shrink')
        }
    };

    // Shrink the navbar
    navbarShrink();

    // Shrink the navbar when page is scrolled
    document.addEventListener('scroll', navbarShrink);

    // scroll section
    var section = document.querySelectorAll(".section");
    var sections = {};
    var i = 0;

    Array.prototype.forEach.call(section, function(e) {
        sections[e.id] = e.offsetTop;
    });

    window.onscroll = function() {
        var scrollPosition = document.documentElement.scrollTop || document.body.scrollTop;

        for (i in sections) {
            if (sections[i] - 200 /* custom offest */ <= scrollPosition) {
                var active = document.querySelector('.active');
                if(active) {
                    active.setAttribute('class', 'nav-link')
                }
                active = document.querySelector('.navbar a.nav-link[href*=' + i + ']');
                if(active) {
                    active.setAttribute('class', 'nav-link active')
                }
            }
        }
    };
});