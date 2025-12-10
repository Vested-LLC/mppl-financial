window.addEventListener('DOMContentLoaded', function () {

    if (document.querySelector('.phone-link .elementor-widget-container')) {
        var phoneNumber = document.querySelector('.phone-link .elementor-widget-container').textContent.trim();
        var phoneNumberLink = phoneNumber.replace(/[^+\d]+/g, "");
        const a = document.createElement('a');
        a.setAttribute('href', `tel:${phoneNumberLink}`);
        a.textContent = phoneNumber;
        document.querySelector('.phone-link .elementor-widget-container').textContent = '';
        document.querySelector('.phone-link .elementor-widget-container').appendChild(a);
    }

    function autoplaySliders(selector, speed) {
        const sliders = document.querySelectorAll(selector);

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (entry.target.swiper) {
                            const swiperInstance = entry.target.swiper;
                            swiperInstance.params.autoplay.delay = speed;
                            entry.target.swiper.update();
                            if (entry.isIntersecting) {
                                entry.target.swiper.autoplay.start();
                            } else {
                                entry.target.swiper.autoplay.stop();
                            }
                        }
                    });
                },
                { threshold: 0.5 }
            );

            sliders.forEach((slider) => {
                if (slider.swiper) {
                    observer.observe(slider);
                }
            });
        }
    }

    autoplaySliders('.posts-swiper', 5000);

    function isDesktop() {
        return window.innerWidth >= 1024 && isTouchDevice() === false;
    }
    
    function isTouchDevice() {
        if ('ontouchstart' in window || navigator.msMaxTouchPoints) {
            return true;
        } else {
            return false;
        }
    }

    const panelsContainer = document.querySelector('.panels-container');
    const panelButtons = document.querySelectorAll('.panel-btn a');
    const panelImage = document.querySelector('.panel-img');
    const panels = document.querySelectorAll('.panel');
    const panelBg = document.querySelector('.panel-bg');

    panelButtons.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            handlePanelInteraction(btn);
            panelsContainer.scrollIntoView({
                behavior: 'smooth', // Optional: adds smooth scrolling
                block: 'start' // Scrolls the top of the element to the top of the visible area
            });
        });

        if (isDesktop()) {
            btn.addEventListener('mouseenter', function () {
                handlePanelInteraction(btn);
            });
        }
    });

    function handlePanelInteraction(btn) {
        const panelToShow = document.getElementById(btn.closest('.panel-btn').getAttribute('aria-controls'));
        const panelColor = panelToShow.getAttribute('data-color');

        if (isDesktop()) {
            panelsContainer.style.transition = 'height .4s ease-in-out';
        }

        if (!panelToShow.classList.contains('js-show')) {
            panelImage.style.opacity = '0';
            setTimeout(function () {
                panelImage.style.display = 'none';
            }, 500);
            panelBg.classList.add('js-show');

            panels.forEach(function (panel) {
                panel.setAttribute('aria-hidden', 'true');
                panel.setAttribute('aria-expanded', 'false');
                panel.classList.remove('js-show');
            });

            panelToShow.setAttribute('aria-expanded', 'true');
            panelToShow.setAttribute('aria-hidden', 'false');
            panelToShow.classList.add('js-show');

            const panelToShowHeight = panelToShow.offsetHeight;
            panelBg.style.backgroundColor = `#${panelColor}`;

            if (window.innerWidth >= 1024) {
                requestAnimationFrame(function () {
                    panelsContainer.style.height = `${panelToShowHeight + 80}px`;
                });
            } else {
                panelsContainer.style.height = `${panelToShowHeight}px`;
                console.log('no 200px offset')
            }
        }
    }

    // Set social icons based on text
    document.querySelectorAll('.footer-social ul li').forEach(function (social) {
        var socialLink = social.querySelector('a')
        var socialText = socialLink.textContent;
        if (socialText) {
            socialLink.classList.add('social-icon', socialText.toLowerCase());
        }
    })

    const shareButtons = document.querySelector('.share-buttons');
    if (shareButtons) {
        const socialShareToggle = document.querySelector('.share-toggle a');
        const copyButton = document.querySelector('.copy-link');
        const copied = document.querySelector('.copied');
        socialShareToggle.addEventListener('click', function (e) {
            socialShareToggle.classList.toggle('js-active');
            e.preventDefault();
            // console.log(shareButtons.classList.contains('js-active'))
            shareButtons.classList.contains('js-active') ? shareButtons.style = 'animation: fadeOut .25s normal ease-in-out forwards;' : shareButtons.style = 'animation: fadeIn .25s normal ease-in-out forwards';
            shareButtons.classList.toggle('js-active');
        });

        copyButton.addEventListener('click', function (e) {
            e.preventDefault();
            copied.style.opacity = '1';
            copied.style.pointerEvents = 'all';
            copied.style.visibility = 'visible';
            navigator.clipboard.writeText(window.location.href).then(() => {
                setTimeout(function () {
                    copied.style.opacity = '0';
                    copied.style.pointerEvents = 'none';
                    copied.style.visibility = 'hidden';
                }, 1250)
            });

        })
    }

    // Handle Filter Dropdown Clicks (Posts, Companies, Team Members)
    const filterDropdowns = document.querySelectorAll('.filter-group.dropdown');

    if (filterDropdowns.length) {
        filterDropdowns.forEach(function (dropdown) {

            const button = dropdown.querySelector('button');

            button.addEventListener('click', function (e) {
                e.preventDefault();
                button.parentNode.classList.contains('js-active') ? button.parentNode.classList.remove('js-active') : button.parentNode.classList.add('js-active');
            });

            document.addEventListener('click', function (e) {
                if (!dropdown.contains(e.target) && !button.contains(e.target)) {
                    dropdown.classList.remove('js-active');
                }
            });
        })
    }

    // INSIGHTS
    if (document.body.classList.contains('single-post')) {
        const categoryParam = sessionStorage.getItem('categoryParam');
        const topicParam = sessionStorage.getItem('topic');
        const insightsLinks = document.querySelectorAll('.insights-link');
        console.log((categoryParam === null), topicParam)

        if (categoryParam !== null && topicParam !== null) {
            insightsLinks.forEach(function (link) {
                link.querySelector('a').setAttribute('href', `/insights?category=${categoryParam}&topic=${topicParam}`)
            })
        } else if (categoryParam === null && topicParam !== null) {
            insightsLinks.forEach(function (link) {
                link.querySelector('a').setAttribute('href', `/insights?topic=${topicParam}`)
            })
        } else if (categoryParam !== null && topicParam === null) {
            insightsLinks.forEach(function (link) {
                link.querySelector('a').setAttribute('href', `/insights?category=${categoryParam}`)
            })
        }
    }
    if (document.body.classList.contains('blog')) {
        const postsContainer = document.getElementById("posts-container");
        const loadMore = document.querySelector('.load-more');
        const insightsParams = new URLSearchParams(window.location.search);

        // if (insightsParams.toString()) {
        //     scrollToPostsContainer();
        // }

        let selectedCategory = insightsParams.get("category");
        let selectedTopic = insightsParams.get("topic");
        let currentPage = 1;

        if (selectedCategory && selectedCategory !== null) sessionStorage.setItem("category", selectedCategory);
        if (selectedTopic && selectedTopic !== null) sessionStorage.setItem("topic", selectedTopic);

        function setActiveFilters(filterId, dataset) {
            const datasetValue = sessionStorage.getItem(`${dataset}`) !== null ? sessionStorage.getItem(`${dataset}`) : insightsParams.get(`${dataset}`)
            if (datasetValue !== null) {
                const filter = document.getElementById(filterId);
                const activeButton = filter.previousElementSibling;
                activeButton.textContent = filter.querySelector(`button[data-${dataset}="${datasetValue}"]`).textContent;
                filter.querySelector(`button[data-${dataset}="${datasetValue}"]`).classList.add('js-active');
            }
        }

        setActiveFilters('category-filter', 'category');
        setActiveFilters('topic-filter', 'topic');

        function filterOnClick(filterId, dataset) {
            document.getElementById(filterId).addEventListener('click', function (e) {
                e.preventDefault();
                const button = e.target.closest('button');

                if (!button) return;
                selected = button.getAttribute(`data-${dataset}`);

                if (selected && selected !== null) {
                    // console.log(paramName);
                    insightsParams.set(dataset, selected);
                    sessionStorage.setItem(dataset, selected);
                } else {
                    insightsParams.delete(dataset);
                    sessionStorage.removeItem(dataset);
                }

                updateActiveClass(document.getElementById(filterId), button);
                // console.log(button.parentNode.parentNode)
                e.target.parentNode.parentNode.previousElementSibling.textContent = e.target.textContent;
                button.parentNode.parentNode.parentNode.classList.remove("js-active");

                loadPosts();
                // scrollToTeamContainer();
            })
        }

        filterOnClick('category-filter', 'category');
        filterOnClick('topic-filter', 'topic');

        function scrollToPostsContainer() {
            const offset = 150;
            const elementPosition = postsContainer.getBoundingClientRect().top + window.pageYOffset;
            const offsetPosition = elementPosition - offset;

            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        }

        loadMore.addEventListener('click', function () {
            fetchPosts(currentPage);
        })

        function animateArticles(pageNumber) {
            const articles = document.querySelectorAll(`article[data-page="${pageNumber}"`);
            console.log(articles)
            articles.forEach(function (article, i = 0) {
                setTimeout(function () {
                    article.classList.remove('elementor-invisible');

                    article.classList.add('animated');

                    article.classList.add('fadeInUp');

                }, i * 300)
                i++;
            })
        }

        function scrollToPage(pageNumber) {
            const firstArticle = document.querySelector(`article[data-page="${pageNumber}"`);
            if (firstArticle) {
                firstArticle.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                });
            }
        }

        function loadPosts() {
            currentPage = 1;
            totalPages = null;
            postsContainer.innerHTML = "";
            fetchPosts(currentPage)
        }

        function fetchPosts(paged = 1, initialLoad = false) {
            let categoryParam = sessionStorage.getItem("category") || "";
            let topicParam = sessionStorage.getItem("topic") || "";

            updateURL();

            const formData = new FormData();
            formData.append("action", "fetch_filtered_posts");
            if (categoryParam !== null) formData.append("category", categoryParam);
            if (topicParam !== null) formData.append("topic", topicParam);
            formData.append("paged", paged);

            fetch(ajaxurl.url, {
                method: "POST",
                body: formData,
            })
                .then((response) => response.text())
                .then((html) => {

                    postsContainer.insertAdjacentHTML("beforeend", html);

                    if (initialLoad = false) {
                        scrollToPage(currentPage)
                    }
                    animateArticles(currentPage);

                    let paginationWrapper = postsContainer.querySelector('.pagination');
                    if (paginationWrapper) {
                        totalPages = parseInt(paginationWrapper.dataset.totalPages, 10);
                        paginationWrapper.remove();
                    }
                    loadMore.dataset.totalPages = totalPages;

                    // console.log(currentPage, totalPages)
                    if (currentPage < totalPages) {
                        loadMore.style.opacity = '1';
                        loadMore.style.visibility = 'visible';
                        loadMore.style.pointerEvents = 'all';
                        loadMore.setAttribute('aria-hidden', false);
                    } else {
                        loadMore.style.opacity = '0';
                        loadMore.style.visibility = 'hidden';
                        loadMore.style.pointerEvents = 'none';
                        loadMore.setAttribute('aria-hidden', true);
                    }

                    currentPage++;

                })
                .catch(() => {
                    postsContainer.innerHTML = "<p>Error loading team members.</p>";
                });
        }

        function updateActiveClass(parent, target) {
            parent.querySelectorAll("button").forEach((btn) => btn.classList.remove("js-active"));
            target.classList.add("js-active");
        }

        function updateURL() {
            let urlParams = new URLSearchParams();
            let categoryParam = sessionStorage.getItem("category");
            let topicParam = sessionStorage.getItem("topic");
            let url;

            if (categoryParam) urlParams.set("category", categoryParam);
            if (topicParam) urlParams.set("topic", topicParam);

            url = urlParams.toString() ? `${window.location.pathname}?${urlParams.toString()}` : window.location.pathname;

            window.history.pushState({}, "", url);
        }

        function resetFilters() {
            const filters = document.querySelectorAll('.filter-group');
            filters.forEach(function (filter) {
                const defaultButtonText = filter.dataset.filter;
                console.log(defaultButtonText)
                filter.querySelector('button').textContent = 'All';
                filter.querySelectorAll('button').forEach(function (btn) {
                    btn.classList.remove('js-active');
                })
            })
            insightsParams.delete("category");
            insightsParams.delete("topic");
            sessionStorage.clear();
            selectedTeam = '';
            selectedLocation = '';
            loadPosts();
        }

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) {
                        const button = node.querySelector('#no-posts button')
                        if (button) {
                            button.addEventListener('click', function () {
                                resetFilters();
                            })
                        }
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });

        loadPosts(paged = currentPage, initialLoad = true);

    }

    // OUR TEAM
    if (document.body.classList.contains('single-team')) {
        const employeesParam = sessionStorage.getItem('employees');
        const locationParam = sessionStorage.getItem('location');
        // console.log(employeesParam, locationParam);
        const teamLinks = document.querySelectorAll('.team-link');

        if (employeesParam !== null && locationParam !== null) {
            teamLinks.forEach(function (link) {
                link.querySelector('a').setAttribute('href', `/our-team?employees=${employeesParam}&location=${locationParam}`)
            })
        } else if (locationParam !== null) {
            teamLinks.forEach(function (link) {
                link.querySelector('a').setAttribute('href', `/our-team?location=${locationParam}`)
            })
        } else if (employeesParam !== null) {
            teamLinks.forEach(function (link) {
                link.querySelector('a').setAttribute('href', `/our-team?employees=${employeesParam}`)
            })
        }
    }

    if (document.body.classList.contains('post-type-archive-team')) {
        const teamContainer = document.getElementById("team-container");
        const loadMore = document.querySelector('.load-more');
        const employeesParams = new URLSearchParams(window.location.search);

        if (employeesParams.toString()) {
            scrollToTeamContainer();
        }

        let selectedTeam = employeesParams.get("employees");
        let selectedLocation = employeesParams.get("location");
        let currentPage = 1;

        if (selectedTeam && selectedTeam !== null) sessionStorage.setItem("employees", selectedTeam);
        if (selectedLocation && selectedLocation !== null) sessionStorage.setItem("location", selectedLocation);

        function setActiveFilters(filterId, dataset) {
            const datasetValue = sessionStorage.getItem(`${dataset}`) !== null ? sessionStorage.getItem(`${dataset}`) : employeesParams.get(`${dataset}`)
            console.log('Dataset Value' + datasetValue)
            if (datasetValue !== null) {
                const filter = document.getElementById(filterId);
                const activeButton = filter.previousElementSibling;
                activeButton.textContent = filter.querySelector(`button[data-${dataset}="${datasetValue}"]`).textContent;
                filter.querySelector(`button[data-${dataset}="${datasetValue}"]`).classList.add('js-active');
            }
        }

        setActiveFilters('employees-filter', 'employees');
        setActiveFilters('location-filter', 'location');

        function filterOnClick(filterId, dataset) {
            document.getElementById(filterId).addEventListener('click', function (e) {
                e.preventDefault();
                const button = e.target.closest('button');

                if (!button) return;
                selected = button.getAttribute(`data-${dataset}`);

                if (selected && selected !== null) {
                    // console.log(paramName);
                    employeesParams.set(dataset, selected);
                    sessionStorage.setItem(dataset, selected);
                } else {
                    employeesParams.delete(dataset);
                    sessionStorage.removeItem(dataset);
                }

                updateActiveClass(document.getElementById(filterId), button);
                // console.log(button.parentNode.parentNode)
                e.target.parentNode.parentNode.previousElementSibling.textContent = e.target.textContent;
                button.parentNode.parentNode.parentNode.classList.remove("js-active");

                loadTeams();
                scrollToTeamContainer();
            })
        }

        filterOnClick('employees-filter', 'employees');
        filterOnClick('location-filter', 'location');

        function scrollToTeamContainer() {
            const offset = 235;
            const elementPosition = teamContainer.getBoundingClientRect().top + window.pageYOffset;
            const offsetPosition = elementPosition - offset;

            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        }

        loadMore.addEventListener('click', function () {
            fetchTeams(currentPage);
        })

        function animateArticles(pageNumber) {
            const articles = document.querySelectorAll(`div[data-page="${pageNumber}"`);
            articles.forEach(function (article, i = 0) {
                setTimeout(function () {
                    article.classList.remove('elementor-invisible');

                    article.classList.add('animated');

                    article.classList.add('fadeInUp');

                }, i * 300)
                i++;
            })
        }

        function scrollToPage(pageNumber) {
            const firstArticle = document.querySelector(`article[data-page="${pageNumber}"`);
            if (firstArticle) {
                firstArticle.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                });
            }
        }

        function loadTeams() {
            currentPage = 1;
            totalPages = null;
            teamContainer.innerHTML = "";
            fetchTeams(currentPage)
        }

        function fetchTeams(paged = 1, initialLoad = false) {
            let employeesParam = sessionStorage.getItem("employees") || "";
            let locationParam = sessionStorage.getItem("location") || "";

            updateURL();

            const formData = new FormData();
            formData.append("action", "fetch_filtered_team_members");
            if (employeesParam !== null) formData.append("employees", employeesParam);
            if (locationParam !== null) formData.append("location", locationParam);
            formData.append("paged", paged);

            fetch(ajaxurl.url, {
                method: "POST",
                body: formData,
            })
                .then((response) => response.text())
                .then((html) => {

                    teamContainer.insertAdjacentHTML("beforeend", html);

                    if (!initialLoad) {
                        scrollToPage(currentPage)
                    }
                    animateArticles(currentPage);

                    let paginationWrapper = teamContainer.querySelector('.pagination');
                    if (paginationWrapper) {
                        totalPages = parseInt(paginationWrapper.dataset.totalPages, 10);
                        paginationWrapper.remove();
                    }
                    loadMore.dataset.totalPages = totalPages;

                    console.log(currentPage, totalPages)
                    if (currentPage < totalPages) {
                        loadMore.style.opacity = '1';
                        loadMore.style.visibility = 'visible';
                        loadMore.style.pointerEvents = 'all';
                        loadMore.setAttribute('aria-hidden', false);
                    } else {
                        loadMore.style.opacity = '0';
                        loadMore.style.visibility = 'hidden';
                        loadMore.style.pointerEvents = 'none';
                        loadMore.setAttribute('aria-hidden', true);
                    }

                    currentPage++;

                })
                .catch(() => {
                    teamContainer.innerHTML = "<p>Error loading team members.</p>";
                });
        }

        function updateActiveClass(parent, target) {
            parent.querySelectorAll("button").forEach((btn) => btn.classList.remove("js-active"));
            target.classList.add("js-active");
        }

        function updateURL() {
            let urlParams = new URLSearchParams();
            let employeesParam = sessionStorage.getItem("employees");
            let locationParam = sessionStorage.getItem("location");
            let url;

            if (employeesParam) urlParams.set("employees", employeesParam);
            if (locationParam) urlParams.set("location", locationParam);

            url = urlParams.toString() ? `${window.location.pathname}?${urlParams.toString()}` : window.location.pathname;

            window.history.pushState({}, "", url);
        }

        function resetFilters() {
            const filters = document.querySelectorAll('.filter-group');
            filters.forEach(function (filter) {
                const defaultButtonText = filter.dataset.filter;
                console.log(defaultButtonText)
                filter.querySelector('button').textContent = 'All';
                filter.querySelectorAll('button').forEach(function (btn) {
                    btn.classList.remove('js-active');
                })
            })
            employeesParams.delete("employees");
            employeesParams.delete("location");
            sessionStorage.clear();
            selectedTeam = '';
            selectedLocation = '';
            loadTeams();
        }

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) {
                        const button = node.querySelector('#no-posts button')
                        if (button) {
                            button.addEventListener('click', function () {
                                resetFilters();
                            })
                        }
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });

        loadTeams();

    }

    // Main Navigation
    var navWrapper = document.getElementById("nav-wrapper");
    var openBtn = document.getElementById("nav-open");
    var menu = document.getElementById("nav");
    var overlay = document.getElementById("nav-overlay");
    var closeBtn = document.getElementById("nav-close");
    const mainMenu = document.querySelector('.menu');
    const dropdowns = mainMenu.querySelectorAll('li.menu-item-has-children');

    if (menu.querySelector('[aria-current]')) {
        menu.querySelector('[aria-current]').addEventListener('click', closeMenu)
    }

    dropdowns.forEach(function (dropdown) {
        dropdown.classList.add('collapse');
        dropdown.setAttribute('aria-expanded', 'false');

        let submenu = dropdown.querySelector('.sub-menu');
        submenu.style.overflow = 'hidden';
        submenu.style.display = 'none';
        submenu.style.maxHeight = '0px';
        submenu.style.transition = 'max-height 0.3s ease-in-out';

        if (dropdown.querySelector('.current_page_item')) {
            dropdown.classList.add('js-current');
        }

        dropdown.addEventListener('click', function () {
            let isExpanded = dropdown.classList.contains('show');

            // Collapse all other dropdowns first
            dropdown.parentElement.querySelectorAll('li.menu-item-has-children').forEach(function (li) {
                if (li !== dropdown && li.classList.contains('show')) {
                    let otherSubmenu = li.querySelector('.sub-menu');
                    if (otherSubmenu) {
                        otherSubmenu.style.maxHeight = otherSubmenu.scrollHeight + 'px'; // Ensure smooth collapse
                        requestAnimationFrame(() => {
                            otherSubmenu.style.maxHeight = '0px';
                        });

                        setTimeout(() => {
                            otherSubmenu.style.display = 'none'; // Hide after transition
                        }, 300);

                        li.classList.remove('show');
                        li.setAttribute('aria-expanded', 'false');
                    }
                }
            });

            if (isExpanded) {
                // Smooth Collapse
                submenu.style.maxHeight = submenu.scrollHeight + 'px'; // Ensure a starting height before collapsing
                requestAnimationFrame(() => {
                    submenu.style.maxHeight = '0px';
                });

                setTimeout(() => {
                    submenu.style.display = 'none'; // Hide after transition completes
                }, 300);

                dropdown.classList.remove('show');
                dropdown.setAttribute('aria-expanded', 'false');
            } else {
                // Expand smoothly
                submenu.style.display = 'block'; // Set display before animating
                let height = submenu.scrollHeight; // Measure height
                submenu.style.maxHeight = '0px'; // Reset before expanding

                requestAnimationFrame(() => {
                    submenu.style.maxHeight = height + 'px'; // Animate open
                });

                dropdown.classList.add('show');
                dropdown.setAttribute('aria-expanded', 'true');

                setTimeout(() => {
                    submenu.style.maxHeight = 'none'; // Allow natural height after expansion
                }, 300);
            }
        });
    });

    function openMenu() {
        scrollPosition = window.scrollY;
        document.body.classList.add('js-fixed');
        navWrapper.style.display = 'block';
        navWrapper.setAttribute('aria-expanded', 'false');
        setTimeout(function() {
            navWrapper.classList.add("js-show");
        }, 300)
        // menu.setAttribute("aria-hidden", "false");
        openBtn.setAttribute("aria-expanded", "true");
        closeBtn.focus();
    }

    function closeMenu() {
        document.body.classList.remove('js-fixed');
        navWrapper.classList.remove("js-show");
        navWrapper.style.display = 'block;'

        navWrapper.setAttribute('aria-expanded', 'true');
        // menu.setAttribute("aria-hidden", "true");
        openBtn.setAttribute("aria-expanded", "false");
        openBtn.focus();
    }

    // Event listeners for opening and closing
    openBtn.addEventListener("click", function (e) {
        e.preventDefault();
        openMenu();
    });
    closeBtn.addEventListener("click", closeMenu);

    // Keyboard navigation: trap focus inside the menu when open
    menu.addEventListener("keydown", function (e) {
        if (e.key === "Tab") {
            var focusable = menu.querySelectorAll("a, button, [tabindex=\"0\"]");
            var first = focusable[0];
            var last = focusable[focusable.length - 1];
            if (focusable.length === 0) return;
            if (e.shiftKey && document.activeElement === first) {
                // Shift+Tab on first element: wrap to last
                last.focus();
                e.preventDefault();
            } else if (!e.shiftKey && document.activeElement === last) {
                // Tab on last element: wrap to first
                first.focus();
                e.preventDefault();
            }
        } else if (e.key === "Escape" || e.key === "Esc") {
            // ESC key closes the menu
            e.preventDefault();
            closeMenu();
        }
    });

    const captchaLabels = document.querySelectorAll('.gfield--type-captcha label');
    captchaLabels.forEach(function(label) {
        label.classList.add('sr-only');
        label.setAttribute('aria-label', 'CAPTCHA (Invisible Verification Method) - No Action Required')
    })

});

window.addEventListener('resize', function () {
    const panelsContainer = document.querySelector('.panels-container')
    const activePanel = document.querySelector('.panel.js-show');

    if (activePanel) {
        const panelHeight = activePanel.scrollHeight;
        if (window.innerWidth >= 1024) {
            requestAnimationFrame(function () {
                panelsContainer.style.height = `${panelToShowHeight + 80}px`;
            });
        } else {
            panelsContainer.style.height = `${panelToShowHeight}px`;
            console.log('no 200px offset')
        }
    }
})

jQuery( document ).on( 'elementor/popup/show', () => {
    setTimeout(() => {
        window.gform.recaptcha.renderRecaptcha();
    }, 100);
});