
        // API Configuration
        const API_URL = 'http://localhost:5000/api';
        
        // State
        let authmode = 'login'; // 'login' or 'signup'
        let isLogin = true;
        let currentUser = null;
        let allMovies = [];
                let favoritesSet = new Set();
                let categories = ['Trending Now', 'Popular on Netflix', 'Action Movies', 'Comedy', 'Drama', 'Sci-Fi', 'Horror'];

// Hero carousel state
let heroMovies = [];
let heroIndex = 0;
let heroTimer = null;
const HERO_ROTATE_MS = 8000; // 8 seconds auto-rotate


        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            checkAuth();
            setupEventListeners();
        });

        // Check Authentication
        function checkAuth() {
            const token = localStorage.getItem('netflix_token');
            const user = localStorage.getItem('netflix_user');
            
            if (token && user) {
                currentUser = JSON.parse(user);
            }
            // Always show the app first - users can browse without signing in
            showApp();
                // load favorites (after app shows)
                setTimeout(() => loadFavorites(), 300);
        }

        // Setup Event Listeners
        function setupEventListeners() {
            // Auth Form
            document.getElementById('authForm').addEventListener('submit', handleAuth);
            document.getElementById('authSwitch').addEventListener('click', toggleAuthMode);
            
            // Navbar Scroll
            window.addEventListener('scroll', () => {
                const navbar = document.getElementById('navbar');
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });

            // Search
            document.getElementById('searchInput').addEventListener('input', (e) => {
                searchMovies(e.target.value);
            });

            // Modal Close on Overlay Click
            document.getElementById('movieModal').addEventListener('click', (e) => {
                if (e.target.id === 'movieModal') {
                    closeModal();
                }
            });

            // Auth Modal Close on Overlay Click
            document.getElementById('authContainer').addEventListener('click', (e) => {
                if (e.target.id === 'authContainer') {
                    closeAuthModal();
                }
            });
        }

        // Auth Functions
async function handleAuth(e) {
    e.preventDefault();

    const email = document.getElementById('authEmail').value;
    const password = document.getElementById('authPassword').value;

    if (!email || !password) {
        showError("Please fill all fields");
        return;
    }

    const formData = new FormData();
    formData.append("email", email);
    formData.append("password", password);

    // =========================
    // LOGIN MODE
    // =========================
    if (isLogin) {
        let res = await fetch("../backend/login.php", {
            method: "POST",
            body: formData
        });

        let data = await res.json();

        if (data.status === "success") {
            // store user and JWT (if returned)
            localStorage.setItem("netflix_user", JSON.stringify(data.user || data));
            if (data.token) {
                localStorage.setItem("netflix_token", data.token);
            } else {
                localStorage.setItem("netflix_token", "demo");
            }

            closeAuthModal();
            showApp();
        } else {
            showError(data.message || "Invalid credentials");
        }

        return;
    }

    // =========================
    // SIGNUP MODE
    // =========================
// SIGNUP MODE
if (!isLogin) {
    const name = document.getElementById('authName').value;

    if (!name) {
        showError("Please enter your name");
        return;
    }

    formData.append("name", name);

    let res = await fetch("../backend/register.php", {
        method: "POST",
        body: formData
    });

    let data = await res.json();

    if (data.status === "success") {
        // Auto-login after signup: store user and token if provided
        if (data.user) localStorage.setItem('netflix_user', JSON.stringify(data.user));
        if (data.token) localStorage.setItem('netflix_token', data.token);

        closeAuthModal();
        showApp();
    } else {
        showError(data.message || "Signup failed");
    }

    return;
}
}
function toggleAuthMode() {
    isLogin = !isLogin;
    showAuthModal(isLogin ? 'login' : 'signup');
}

        function showError(message) {
            const errorEl = document.getElementById('errorMessage');
            errorEl.textContent = message;
            errorEl.style.display = 'block';
        }

function logout() {
    // 1. Clear auth
    localStorage.removeItem('netflix_token');
    localStorage.removeItem('netflix_user');
    currentUser = null;

    // 2. Close UI elements that block clicks
    document.querySelector('.profile-dropdown')?.classList.remove('active');
    document.querySelector('.overlay')?.classList.remove('active');
    document.querySelector('.modal')?.classList.remove('active');
    document.querySelector('.signin-modal')?.classList.remove('active');

    // 3. Reset UI state
    document.body.classList.remove('no-scroll');

    // 4. Show auth OR redirect
    setTimeout(() => {
        window.location.href = "index.html"; 
    }, 100);
}
        // UI Functions
        function showAuth() {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('authContainer').style.display = 'flex';
            document.getElementById('appContainer').style.display = 'none';
        }

function showAuthModal(mode) {
    isLogin = (mode === 'login');

    const authTitle = document.getElementById('authTitle');
    const authButton = document.getElementById('authButton');
    const authSwitch = document.getElementById('authSwitch');
    const authName = document.getElementById('authName');
    const errorMessage = document.getElementById('errorMessage');
    const authContainer = document.getElementById('authContainer');

    authTitle.textContent = isLogin ? 'Sign In' : 'Create Account';
    authButton.textContent = isLogin ? 'Sign In' : 'Sign Up';

    // 🔥 FULL RESET (IMPORTANT)
    authSwitch.innerHTML = '';

    const text = document.createElement('span');
    text.textContent = isLogin
        ? 'New to Netflix? '
        : 'Already have an account? ';

    const link = document.createElement('a');
    link.href = '#';
    link.textContent = isLogin
        ? 'Sign up now'
        : 'Login';

    link.onclick = (e) => {
        e.preventDefault();
        toggleAuthMode();
    };

    authSwitch.appendChild(text);
    authSwitch.appendChild(link);

    authName.style.display = isLogin ? 'none' : 'block';
    errorMessage.style.display = 'none';

    authContainer.classList.add('active');
}
        function closeAuthModal() {
            document.getElementById('authContainer').classList.remove('active');
        }

  function showApp() {
    document.getElementById('loading').style.display = 'flex';

    fetchMovies()
        .catch(() => {
            console.log("API failed → using sample data");
            allMovies = getSampleMovies();
            renderContent();
        })
        .finally(() => {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('authContainer').classList.remove('active');
            document.getElementById('appContainer').style.display = 'block';

            if (localStorage.getItem("netflix_user")) {
                currentUser = JSON.parse(localStorage.getItem("netflix_user"));

                document.getElementById('authButtons').style.display = 'none';
                document.getElementById('userProfile').style.display = 'flex';

                  // show user email/avatar
                  const avatar = document.getElementById('profileAvatar');
                  const profileName = document.getElementById('profileName');
                  if (currentUser.email) {
                      profileName.textContent = currentUser.email.split('@')[0];
                  }
                  avatar.src = `https://i.pravatar.cc/150?u=${encodeURIComponent(currentUser.email)}`;

              } else {
                  document.getElementById('authButtons').style.display = 'flex';
                  document.getElementById('userProfile').style.display = 'none';
              }
          });
  }
        // Movie Functions
        async function fetchMovies() {
            try {
                const response = await fetch("../backend/get_movies.php");
                const result = await response.json();
                if (result.status === "success") {
                    allMovies = result.data;
                } else {
                    allMovies = getSampleMovies();
                }
                renderContent();
            } catch (err) {
                console.log('Using sample data');
                allMovies = getSampleMovies();
                renderContent();
            }
        }

function getSampleMovies() {
    return [
        { id: 1, title: 'Stranger Things', image_url: 'https://image.tmdb.org/t/p/w500/x2LSRK2Cm7MZhjluni1msVJ3wDF.jpg', genre: 'Drama', release_year: 2016, rating: 8.7, duration: '4 Seasons', description: 'A group of kids uncover supernatural mysteries in their small town.' },

        { id: 2, title: 'Money Heist', image_url: 'https://image.tmdb.org/t/p/w500/reEMJA1uzscCbkpeRJeTT2bjqUp.jpg', genre: 'Crime', release_year: 2017, rating: 8.2, duration: '5 Seasons', description: 'A mastermind leads a group to execute the biggest heist in history.' },

        { id: 3, title: 'Squid Game', image_url: 'https://image.tmdb.org/t/p/w500/dDlEmu3EZ0Pgg93K2SVNLCjCSvE.jpg', genre: 'Drama', release_year: 2021, rating: 8.0, duration: '1 Season', description: 'Players risk their lives in deadly games for a massive cash prize.' },

        { id: 4, title: 'Breaking Bad', image_url: 'https://image.tmdb.org/t/p/w500/ggFHVNu6YYI5L9pCfOacjizRGt.jpg', genre: 'Crime', release_year: 2008, rating: 9.5, duration: '5 Seasons', description: 'A chemistry teacher turns into a drug kingpin.' },

        { id: 5, title: 'The Witcher', image_url: 'https://image.tmdb.org/t/p/w500/7vjaCdMw15FEbXyLQTVa04URsPm.jpg', genre: 'Action', release_year: 2019, rating: 8.2, duration: '3 Seasons', description: 'A monster hunter struggles to find his place in a brutal world.' },

        { id: 6, title: 'Inception', image_url: 'https://image.tmdb.org/t/p/w500/edv5CZvWj09upOsy2Y6IwDhK8bt.jpg', genre: 'Sci-Fi', release_year: 2010, rating: 8.8, duration: '2h 28m', description: 'A thief enters dreams to steal secrets and plant ideas.' },

        { id: 7, title: 'The Dark Knight', image_url: 'https://image.tmdb.org/t/p/w500/qJ2tW6WMUDux911r6m7haRef0WH.jpg', genre: 'Action', release_year: 2008, rating: 9.0, duration: '2h 32m', description: 'Batman faces the Joker in a battle for Gotham’s soul.' },

        { id: 8, title: 'Interstellar', image_url: 'https://image.tmdb.org/t/p/w500/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg', genre: 'Sci-Fi', release_year: 2014, rating: 8.6, duration: '2h 49m', description: 'A team travels through space to save humanity.' },

        { id: 9, title: 'Avengers: Endgame', image_url: 'https://image.tmdb.org/t/p/w500/or06FN3Dka5tukK1e9sl16pB3iy.jpg', genre: 'Action', release_year: 2019, rating: 8.4, duration: '3h', description: 'The Avengers unite to undo the damage caused by Thanos.' },

        { id: 10, title: 'Spider-Man: No Way Home', image_url: 'https://image.tmdb.org/t/p/w500/1g0dhYtq4irTY1GPXvft6k4YLjm.jpg', genre: 'Action', release_year: 2021, rating: 8.2, duration: '2h 28m', description: 'Spider-Man faces villains from different universes.' },

        { id: 11, title: 'Dune', image_url: 'https://image.tmdb.org/t/p/w500/d5NXSklXo0qyIYkgV94XAgMIckC.jpg', genre: 'Sci-Fi', release_year: 2021, rating: 8.0, duration: '2h 35m', description: 'A noble family becomes entangled in a war over a desert planet.' },

        { id: 12, title: 'The Matrix', image_url: 'https://image.tmdb.org/t/p/w500/f89U3ADr1oiB1s9GkdPOEpXUk5H.jpg', genre: 'Sci-Fi', release_year: 1999, rating: 8.7, duration: '2h 16m', description: 'A hacker discovers the world is a simulation.' },

        { id: 13, title: 'Parasite', image_url: 'https://image.tmdb.org/t/p/w500/7IiTTgloJzvGI1TAYymCfbfl3vT.jpg', genre: 'Drama', release_year: 2019, rating: 8.5, duration: '2h 12m', description: 'A poor family schemes to infiltrate a wealthy household.' },

        { id: 14, title: 'Joker', image_url: 'https://image.tmdb.org/t/p/w500/udDclJoHjfjb8Ekgsd4FDteOkCU.jpg', genre: 'Drama', release_year: 2019, rating: 8.4, duration: '2h', description: 'A troubled man descends into madness and becomes Joker.' },

        { id: 15, title: 'Titanic', image_url: 'https://image.tmdb.org/t/p/w500/9xjZS2rlVxm8SFx8kPC3aIGCOYQ.jpg', genre: 'Romance', release_year: 1997, rating: 7.9, duration: '3h 15m', description: 'A love story unfolds aboard the ill-fated Titanic.' },

        { id: 16, title: 'John Wick', image_url: 'https://image.tmdb.org/t/p/w500/fZPSd91yGE9fCcCe6OoQr6E3Bev.jpg', genre: 'Action', release_year: 2014, rating: 7.4, duration: '1h 41m', description: 'A retired hitman seeks revenge.' },

        { id: 17, title: 'Mad Max: Fury Road', image_url: 'https://image.tmdb.org/t/p/w500/8tZYtuWezp8JbcsvHYO0O46tFbo.jpg', genre: 'Action', release_year: 2015, rating: 8.1, duration: '2h', description: 'A high-speed chase across a post-apocalyptic wasteland.' },

        { id: 18, title: 'Gladiator', image_url: 'https://image.tmdb.org/t/p/w500/ty8TGRuvJLPUmAR1H1nRIsgwvim.jpg', genre: 'Action', release_year: 2000, rating: 8.5, duration: '2h 35m', description: 'A betrayed general seeks revenge in ancient Rome.' },

        { id: 19, title: 'Blade Runner 2049', image_url: 'https://image.tmdb.org/t/p/w500/gajva2L0rPYkEWjzgFlBXCAVBE5.jpg', genre: 'Sci-Fi', release_year: 2017, rating: 8.0, duration: '2h 44m', description: 'A blade runner uncovers a long-buried secret.' },

        { id: 20, title: 'Gravity', image_url: 'https://image.tmdb.org/t/p/w500/kZ2nZw8D681aphje8NJi8EfbL1U.jpg', genre: 'Sci-Fi', release_year: 2013, rating: 7.7, duration: '1h 31m', description: 'Astronauts struggle to survive in space after disaster strikes.' },

        { id: 21, title: 'Fight Club', image_url: 'https://image.tmdb.org/t/p/w500/bptfVGEQuv6vDTIMVCHjJ9Dz8PX.jpg', genre: 'Drama', release_year: 1999, rating: 8.8, duration: '2h 19m', description: 'An underground fight club spirals into chaos.' },

        { id: 22, title: 'Forrest Gump', image_url: 'https://image.tmdb.org/t/p/w500/arw2vcBveWOVZr6pxd9XTd1TdQa.jpg', genre: 'Drama', release_year: 1994, rating: 8.8, duration: '2h 22m', description: 'A simple man lives through extraordinary events.' },

        { id: 23, title: 'The Shawshank Redemption', image_url: 'https://image.tmdb.org/t/p/w500/q6y0Go1tsGEsmtFryDOJo3dEmqu.jpg', genre: 'Drama', release_year: 1994, rating: 9.3, duration: '2h 22m', description: 'Two imprisoned men bond over years of hope.' },

        { id: 24, title: 'The Godfather', image_url: 'https://image.tmdb.org/t/p/w500/3bhkrj58Vtu7enYsRolD1fZdja1.jpg', genre: 'Crime', release_year: 1972, rating: 9.2, duration: '2h 55m', description: 'The aging patriarch transfers control of his empire to his son.' },

        { id: 25, title: 'Pulp Fiction', image_url: 'https://image.tmdb.org/t/p/w500/d5iIlFn5s0ImszYzBPb8JPIfbXD.jpg', genre: 'Crime', release_year: 1994, rating: 8.9, duration: '2h 34m', description: 'Interwoven stories of crime in Los Angeles.' },

        { id: 26, title: 'The Lion King', image_url: 'https://image.tmdb.org/t/p/w500/sKCr78MXSLixwmZ8DyJLrpMsd15.jpg', genre: 'Drama', release_year: 1994, rating: 8.5, duration: '1h 28m', description: 'A young lion prince flees his kingdom.' },

        { id: 27, title: 'Avatar', image_url: 'https://image.tmdb.org/t/p/w500/kyeqWdyUXW608qlYkRqosgbbJyK.jpg', genre: 'Sci-Fi', release_year: 2009, rating: 7.9, duration: '2h 42m', description: 'A marine explores an alien world called Pandora.' },

        { id: 28, title: 'Doctor Strange', image_url: 'https://image.tmdb.org/t/p/w500/uGBVj3bEbCoZbDjjl9wTxcygko1.jpg', genre: 'Action', release_year: 2016, rating: 7.5, duration: '1h 55m', description: 'A surgeon learns the mystic arts after an accident.' },

        { id: 29, title: 'Black Panther', image_url: 'https://image.tmdb.org/t/p/w500/uxzzxijgPIY7slzFvMotPv8wjKA.jpg', genre: 'Action', release_year: 2018, rating: 7.3, duration: '2h 14m', description: 'The king of Wakanda rises to defend his nation.' },

        { id: 30, title: 'The Social Network', image_url: 'https://image.tmdb.org/t/p/w500/n0ybibhJtQ5icDqTp8eRytcIHJx.jpg', genre: 'Drama', release_year: 2010, rating: 7.7, duration: '2h', description: 'The story behind the creation of Facebook.' }
    ];
}

        function renderContent() {
            // Setup hero carousel with featured movies
            if (allMovies.length > 0) {
                // pick top 6 (or fewer) as featured
                heroMovies = allMovies.slice(0, Math.min(6, allMovies.length));

                // try to resume last hero index if available
                let lastHeroId = localStorage.getItem('lastHeroId');
                let startIndex = 0;
                if (lastHeroId) {
                    const idx = heroMovies.findIndex(m => String(m.id) === String(lastHeroId));
                    if (idx >= 0) startIndex = idx;
                }

                heroIndex = startIndex;
                updateHero(heroMovies[heroIndex]);
                localStorage.setItem('lastHeroId', heroMovies[heroIndex].id);

                // start auto-rotate
                stopHeroAutoRotate();
                startHeroAutoRotate();
            }
            // Render content rows
            const contentSection = document.getElementById('contentSection');
            let html = '';

            // Trending Now
            html += createContentRow('Trending Now', allMovies.slice(0, 10));
            
            // Popular on Netflix
            html += createContentRow('Popular on Netflix', allMovies.slice(5, 15));
            
            // Action & Adventure
            const actionMovies = allMovies.filter(m => m.genre === 'Action');
            html += createContentRow('Action & Adventure', actionMovies.length ? actionMovies : allMovies.slice(0, 8));
            
            // Sci-Fi
            const scifiMovies = allMovies.filter(m => m.genre === 'Sci-Fi');
            html += createContentRow('Sci-Fi Movies', scifiMovies.length ? scifiMovies : allMovies.slice(3, 11));
            
            // Drama
            const dramaMovies = allMovies.filter(m => m.genre === 'Drama');
            html += createContentRow('Drama', dramaMovies.length ? dramaMovies : allMovies.slice(2, 10));

            contentSection.innerHTML = html;
        }

function createContentRow(title, movies) {
    const limitedMovies = movies.slice(0, 8);

    let posters = limitedMovies.map(movie => `
        <div class="poster-card ${isFavorite(movie.id) ? 'favorited' : ''}" data-id="${movie.id}">
            <img src="${movie.image_url}" 
                 loading="lazy"
                 referrerpolicy="no-referrer"
                 onerror="this.src='https://via.placeholder.com/200x300?text=No+Image'"
                 class="poster-image" alt="${escapeHtml(movie.title)}">
            <div class="poster-overlay">
                <div class="overlay-buttons">
                    <button class="play-btn" onclick="playMovie(${movie.id});event.stopPropagation();" aria-label="Play ${escapeHtml(movie.title)}">▶</button>
                    <button class="fav-btn" onclick="toggleFavorite(${movie.id});event.stopPropagation();" aria-label="Toggle favorite">
                        ${isFavorite(movie.id) ? '♥' : '♡'}
                    </button>
                </div>
                <div class="poster-title">${escapeHtml(movie.title)}</div>
                <div class="poster-info">
                    <span class="poster-match">${Math.round(movie.rating * 10)}% Match</span>
                    <span>${movie.release_year}</span>
                    <span>${movie.duration}</span>
                </div>
            </div>
        </div>
    `).join('');

    return `
        <div class="content-row">
            <div class="row-header">
                <h2 class="row-title">${title}</h2>
                <span class="see-all" onclick="expandRow(this, '${title}')">Explore All</span>
            </div>
            <div class="row-posters">
                ${posters}
            </div>
        </div>
    `;
}

function escapeHtml(s) {
    return (s + '').replace(/[&<>"]/g, function(c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]; });
}
        function updateHero(movie) {
                    // remember hero as selected movie for quick play
                            if (!movie) return;
                            window.selectedMovieId = movie.id;
                            document.getElementById('heroTitle').textContent = movie.title;
                            document.getElementById('heroDescription').textContent = movie.description || '';
                            document.getElementById('hero').style.background = `url('${movie.image_url}') center/cover`;

                            // update indicators
                            try {
                                const indicators = document.getElementById('heroIndicators');
                                if (indicators) {
                                    indicators.innerHTML = '';
                                    (heroMovies || []).forEach((m, idx) => {
                                        const btn = document.createElement('button');
                                        btn.className = idx === heroIndex ? 'active' : '';
                                        btn.setAttribute('aria-label', m.title);
                                        btn.onclick = (e) => { e.stopPropagation(); heroIndex = idx; updateHero(heroMovies[heroIndex]); localStorage.setItem('lastHeroId', heroMovies[heroIndex].id); stopHeroAutoRotate(); startHeroAutoRotate(); };
                                        indicators.appendChild(btn);
                                    });
                                }
                            } catch (e) {
                                // ignore indicator errors
                            }
                        }

        // Hero carousel controls
        function nextHero() {
            if (!heroMovies || heroMovies.length === 0) return;
            heroIndex = (heroIndex + 1) % heroMovies.length;
            updateHero(heroMovies[heroIndex]);
            localStorage.setItem('lastHeroId', heroMovies[heroIndex].id);
        }

        function prevHero() {
            if (!heroMovies || heroMovies.length === 0) return;
            heroIndex = (heroIndex - 1 + heroMovies.length) % heroMovies.length;
            updateHero(heroMovies[heroIndex]);
            localStorage.setItem('lastHeroId', heroMovies[heroIndex].id);
        }

        function startHeroAutoRotate() {
            stopHeroAutoRotate();
            heroTimer = setInterval(() => {
                nextHero();
            }, HERO_ROTATE_MS);
        }

        function stopHeroAutoRotate() {
            if (heroTimer) {
                clearInterval(heroTimer);
                heroTimer = null;
            }
        }

function showMovieDetail(id) {
    const movie = allMovies.find(m => m.id === id);
    if (!movie) return;

    // remember selected movie for player
    window.selectedMovieId = id;

    document.getElementById('modalTitle').textContent = movie.title;
    document.getElementById('modalImage').src = movie.image_url;
    document.getElementById('modalRating').textContent = `${Math.round(movie.rating * 10)}% Match`;
    document.getElementById('modalYear').textContent = movie.release_year;
    document.getElementById('modalDuration').textContent = movie.duration;
    document.getElementById('modalGenre').textContent = movie.genre;

    // ✅ FIX: fallback description
    document.getElementById('modalDescription').textContent =
        movie.description || "No description available for this title.";

    // ✅ FIX: fallback cast
    document.getElementById('modalCast').textContent =
        `Cast: ${movie.cast || 'Not available'}`;

    document.getElementById('movieModal').classList.add('active');
}

        function closeModal() {
            document.getElementById('movieModal').classList.remove('active');
            document.body.classList.remove('no-scroll');
        }

        function playMovie(id) {
            // id optional - if provided, play that movie; otherwise use last selected
            if (id) window.selectedMovieId = id;
            const movieId = window.selectedMovieId;
            const movie = allMovies.find(m => m.id === movieId);
            if (!movie) return alert('Movie not found');
            if (!movie.video_url) return alert('Video stream not available for this title.');

            const overlay = document.getElementById('videoPlayer');
            const videoEl = document.getElementById('videoPlayerTag');
            videoEl.src = movie.video_url;
            videoEl.play().catch(() => {});
            overlay.classList.add('active');
            overlay.setAttribute('aria-hidden', 'false');
            document.body.classList.add('no-scroll');
        }

        function closeVideoPlayer() {
            const overlay = document.getElementById('videoPlayer');
            const videoEl = document.getElementById('videoPlayerTag');
            if (videoEl) {
                try { videoEl.pause(); } catch(e) {}
                videoEl.removeAttribute('src');
            }
            overlay.classList.remove('active');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('no-scroll');
        }

        function goHome() {
            window.scrollTo(0, 0);
        }

        function toggleSearch() {
            const searchBox = document.getElementById('searchBox');
            const searchInput = document.getElementById('searchInput');
            
            if (searchBox.style.display === 'block') {
                searchBox.style.display = 'none';
                searchInput.value = '';
            } else {
                searchBox.style.display = 'block';
                searchInput.focus();
            }
        }

        function searchMovies(query) {
            if (query.length < 2) {
                renderContent();
                return;
            }

            const filtered = allMovies.filter(movie => 
                movie.title.toLowerCase().includes(query.toLowerCase()) ||
                movie.genre.toLowerCase().includes(query.toLowerCase())
            );

            const contentSection = document.getElementById('contentSection');
            contentSection.innerHTML = createContentRow(`Search Results for "${query}"`, filtered);
        }
function expandRow(button, title) {
    const row = button.closest('.content-row');
    const posterContainer = row.querySelector('.row-posters');

    let movies = [];

    // Match category
    if (title === 'Trending Now') movies = allMovies.slice(0, 10);
    else if (title === 'Popular on Netflix') movies = allMovies.slice(5, 15);
    else if (title === 'Action & Adventure') movies = allMovies.filter(m => m.genre === 'Action').slice(0, 10);
    else if (title === 'Sci-Fi Movies') movies = allMovies.filter(m => m.genre === 'Sci-Fi').slice(0, 10);
    else if (title === 'Drama') movies = allMovies.filter(m => m.genre === 'Drama').slice(0, 10);

    // Replace with full list
    posterContainer.innerHTML = movies.map(movie => `
        <div class="poster-card" onclick="showMovieDetail(${movie.id})">
            <img src="${movie.image_url}" 
                 loading="lazy"
                 referrerpolicy="no-referrer"
                 onerror="this.src='https://via.placeholder.com/200x300?text=No+Image'"
                 class="poster-image">
        </div>
    `).join('');

    button.style.display = 'none'; // hide button after click
}
function scrollToSection(id) {
    const el = document.getElementById(id);
    if (el) {
        el.scrollIntoView({ behavior: "smooth" });
    }
}


function showMyList() {
    const container = document.getElementById('contentSection');

    // If user not logged in, fallback to client-side list
    const token = localStorage.getItem('netflix_token');
    if (!token || !localStorage.getItem('netflix_user')) {
        const saved = JSON.parse(localStorage.getItem('my_list') || '[]');
        if (saved.length === 0) {
            container.innerHTML = "<h2 style='color:white;padding:20px;'>Your My List is empty</h2>";
            return;
        }
        container.innerHTML = createContentRow("My List", saved);
        return;
    }

    // Fetch server-side favorites
    fetch('../backend/favorites.php', {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        }
    }).then(r => r.json()).then(data => {
        if (data.status === 'success') {
            const list = data.data || [];
            if (list.length === 0) {
                container.innerHTML = "<h2 style='color:white;padding:20px;'>Your My List is empty</h2>";
                return;
            }
            container.innerHTML = createContentRow("My List", list);
        } else {
            container.innerHTML = "<h2 style='color:white;padding:20px;'>Your My List is empty</h2>";
        }
    }).catch(() => {
        container.innerHTML = "<h2 style='color:white;padding:20px;'>Unable to load My List</h2>";
    });
}

function addToMyList(movieId) {
    const movie = allMovies.find(m => m.id === movieId);
    if (!movie) return;

    const token = localStorage.getItem('netflix_token');
    const user = localStorage.getItem('netflix_user');
    // If logged in with token, add server-side
    if (token && user) {
        fetch('../backend/favorites.php', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ movie_id: movieId })
        }).then(r => r.json()).then(data => {
            if (data.status === 'success') {
                favoritesSet.add(movieId);
                renderContent();
                console.log('Added to My List (server)');
            } else {
                console.warn('Could not add to My List', data.message);
            }
        }).catch(err => console.warn('Add to My List failed', err));
        return;
    }

    // fallback to localStorage
    let list = JSON.parse(localStorage.getItem('my_list') || '[]');
    if (!list.some(m => m.id === movieId)) {
        list.push(movie);
        localStorage.setItem('my_list', JSON.stringify(list));
        favoritesSet.add(movieId);
        renderContent();
    }
}

function removeFromMyList(movieId) {
    const token = localStorage.getItem('netflix_token');
    if (token) {
        fetch('../backend/favorites.php', {
            method: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ movie_id: movieId })
        }).then(r => r.json()).then(data => {
            if (data.status === 'success') {
                favoritesSet.delete(movieId);
                renderContent();
            }
        }).catch(err => console.warn('Remove favorite failed', err));
        return;
    }

    // localStorage fallback
    let list = JSON.parse(localStorage.getItem('my_list') || '[]');
    list = list.filter(m => m.id !== movieId);
    localStorage.setItem('my_list', JSON.stringify(list));
    favoritesSet.delete(movieId);
    renderContent();
}

function toggleFavorite(movieId) {
    if (isFavorite(movieId)) {
        removeFromMyList(movieId);
    } else {
        addToMyList(movieId);
    }
}

function isFavorite(movieId) {
    return favoritesSet.has(movieId);
}

function loadFavorites() {
    favoritesSet = new Set();
    const token = localStorage.getItem('netflix_token');
    if (token) {
        fetch('../backend/favorites.php', {
            method: 'GET',
            headers: { 'Authorization': 'Bearer ' + token }
        }).then(r => r.json()).then(data => {
            if (data.status === 'success') {
                const ids = (data.data || []).map(m => m.id);
                ids.forEach(id => favoritesSet.add(id));
                renderContent();
            }
        }).catch(() => {
            // ignore
        });
    } else {
        const saved = JSON.parse(localStorage.getItem('my_list') || '[]');
        saved.forEach(m => favoritesSet.add(m.id));
    }
}

// duplicate closeModal removed (handled earlier)

function showSection(section, el) {
    document.querySelectorAll('.section').forEach(sec => {
        sec.style.display = 'none';
    });

    const active = document.getElementById(section);
    if (active) active.style.display = 'block';

    document.querySelectorAll('.nav-links a').forEach(a => {
        a.classList.remove('active');
    });

    if (el) el.classList.add('active');
}

