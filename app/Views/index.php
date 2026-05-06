<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Corso E-Learning – Get Certified & Accelerate Your Career</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= base_url('assets/css/styles.css') ?>">
</head>
<body>
  <header class="header">
    <div class="container header-inner">
      <a href="<?= base_url('/') ?>" class="logo">Corso E-Learning</a>
      <nav class="nav">
        <a href="#features">Features</a>
        <a href="#learners">Learners</a>
        
        <a href="<?= base_url('verify') ?>">Verify Certificate</a>
        <label class="dash-theme-toggle nav-theme-toggle" aria-label="Toggle theme">
          <input type="checkbox" class="dash-theme-checkbox" checked aria-checked="true" />
          <span class="dash-theme-track"><span class="dash-theme-thumb" aria-hidden="true">🌙</span></span>
        </label>
        <a class="nav-bell" href="<?= base_url('dashboard') ?>#notifications" aria-label="Notifications">🔔</a>
        <button class="nav-profile" aria-haspopup="true" aria-expanded="false"><span>👤</span><span class="nav-name">Profile</span></button>
        <div class="nav-profile-menu" hidden>
          <a class="btn btn-outline nav-dashboard-link" href="<?= base_url('dashboard') ?>">Dashboard</a>
          <a class="btn btn-outline nav-admin-link" href="<?= base_url('admin') ?>" style="display: none;">Admin Panel</a>
          <button class="btn btn-outline nav-logout">Logout</button>
        </div>
      </nav>
      <button class="menu-toggle" aria-label="Toggle menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>
  

  <main>
    <section class="hero hero-split">
      <div class="container hero-inner hero-grid">
        <div class="hero-content">
          <p class="hero-badge">Instant Certificate</p>
          <h1 class="hero-title">
            Prove your skills in minutes — get a certificate instantly
          </h1>
          <p class="hero-subtitle">
            10 MCQs • 5–8 mins • Verifiable online • Great for freshers & professionals
          </p>
          <div class="hero-cta hero-cta-dual">
            <button class="btn btn-primary" id="startSkillCheck">Start a Skill Check</button>
            <a href="<?= base_url('verify') ?>" class="btn btn-outline btn-ghost">Verify a certificate</a>
          </div>
          
          <div class="value-chips">
            <div class="chip">10 MCQs</div>
            <div class="chip">5–8 mins</div>
            <div class="chip">Instant download</div>
            <div class="chip">Verification link</div>
          </div>
          <div class="assess-search">
            <input class="search-input" type="text" placeholder="Search a course to assess" aria-label="Search a course to assess" />
            
          </div>
        </div>
        <div class="hero-aside">
          <div class="hero-card">
            <img
              class="hero-cert"
              src="<?= base_url('assets/images/hero-certificate.png') ?>"
              alt="Corso certificate preview"
              loading="eager"
              decoding="async"
            />
          </div>
        </div>
      </div>
      <div class="hero-gradient"></div>
    </section>

    
    <section class="features" id="features">
      <div class="container">
        <h2 class="section-title">Certificates that help you get noticed</h2>
        <div class="features-grid">
          <article class="feature-card">
            <div class="feature-icon">🏅</div>
            <h3>Instant certificate</h3>
            <p>Complete a quick skill check and download a recruiter‑ready certificate instantly.</p>
          </article>
          <article class="feature-card">
            <div class="feature-icon">🔗</div>
            <h3>Share on LinkedIn</h3>
            <p>Showcase your certificate and boost your profile credibility in minutes.</p>
          </article>
          <article class="feature-card">
            <div class="feature-icon">🔒</div>
            <h3>Verifiable online</h3>
            <p>Share a verification link that builds trust with recruiters and hiring managers.</p>
          </article>
        </div>
      </div>
    </section>

    <section class="how" id="how">
      <div class="container">
        <h2 class="section-title">How it works</h2>
        <p class="section-sub">Simple. Fast. Add it to your profile the same day.</p>
        <div class="how-steps">
          <article class="feature-card how-step">
            <span class="how-num" aria-hidden="true">1</span>
            <div class="feature-icon">🎓</div>
            <h3>Pick a course</h3>
            <p>Choose a skill assessment that matches your goals. We’ll tailor a quick, focused challenge.</p>
          </article>
          <article class="feature-card how-step">
            <span class="how-num" aria-hidden="true">2</span>
            <div class="feature-icon">📝</div>
            <h3>Take the challenge</h3>
            <p>10 MCQs · 5–8 mins. Stay in the flow with fast, engaging questions.</p>
          </article>
          <article class="feature-card how-step">
            <span class="how-num" aria-hidden="true">3</span>
            <div class="feature-icon">📄</div>
            <h3>Unlock your certificate</h3>
            <p>Showcase on LinkedIn, CV, or portfolio — with a verifiable link.</p>
          </article>
        </div>
      </div>
    </section>

    

    <section class="assessments" id="assessments">
      <div class="container">
        <h2 class="section-title">Search a course to assess</h2>
        <p class="section-sub">Pick a category, then choose a course. Pass the quiz to unlock your certificate.</p>
        <div class="assessments-search-wrap">
          <input type="text" class="input search-input assessments-search-input" placeholder="Search courses…" aria-label="Search courses" />
        </div>
        <div class="category-tabs" role="tablist" aria-label="Course categories">
          <button type="button" class="category-tab is-active" data-category="all" aria-selected="true">All</button>
          <button type="button" class="category-tab" data-category="Data &amp; Analytics">Data &amp; Analytics</button>
          <button type="button" class="category-tab" data-category="Programming">Programming</button>
          <button type="button" class="category-tab" data-category="Marketing">Marketing</button>
        </div>
        <div class="assessments-grid" id="assessments-grid">
          <article class="course-card" data-category="Data &amp; Analytics">
            <div class="course-card-badges"><span class="course-badge">Certificate included</span><span class="course-badge course-badge-pass">Pass 50%+</span></div>
            <div class="course-card-image"><img src="<?= base_url('assets/images/') ?>data-analysis.jpg" alt="Data Science Fundamentals" /></div>
            <div class="course-card-body">
              <h3>Data Science Fundamentals</h3>
              <p>Core concepts, tooling, and data handling.</p>
              <button type="button" class="btn btn-primary course-test-btn" data-course="Data Science Fundamentals">Start →</button>
            </div>
          </article>
          <article class="course-card" data-category="Programming">
            <div class="course-card-badges"><span class="course-badge">Certificate included</span><span class="course-badge course-badge-pass">Pass 50%+</span></div>
            <div class="course-card-image"><img src="<?= base_url('assets/images/') ?>java.jpg" alt="Java Basics" /></div>
            <div class="course-card-body">
              <h3>Java Basics</h3>
              <p>Syntax, OOP fundamentals, and debugging.</p>
              <button type="button" class="btn btn-primary course-test-btn" data-course="Java Basics">Start →</button>
            </div>
          </article>
          <article class="course-card" data-category="Marketing">
            <div class="course-card-badges"><span class="course-badge">Certificate included</span><span class="course-badge course-badge-pass">Pass 50%+</span></div>
            <div class="course-card-image"><img src="<?= base_url('assets/images/') ?>digital marketting.jpg" alt="Digital Marketing" /></div>
            <div class="course-card-body">
              <h3>Digital Marketing</h3>
              <p>SEO, content strategy, and analytics.</p>
              <button type="button" class="btn btn-primary course-test-btn" data-course="Digital Marketing">Start →</button>
            </div>
          </article>
          <article class="course-card" data-category="Data &amp; Analytics">
            <div class="course-card-badges"><span class="course-badge">Certificate included</span><span class="course-badge course-badge-pass">Pass 50%+</span></div>
            <div class="course-card-image"><img src="<?= base_url('assets/images/') ?>excel.png" alt="Excel for Analysis" /></div>
            <div class="course-card-body">
              <h3>Excel for Analysis</h3>
              <p>Formulas, pivot tables, and best practices.</p>
              <button type="button" class="btn btn-primary course-test-btn" data-course="Excel for Analysis">Start →</button>
            </div>
          </article>
          <article class="course-card" data-category="Programming">
            <div class="course-card-badges"><span class="course-badge">Certificate included</span><span class="course-badge course-badge-pass">Pass 50%+</span></div>
            <div class="course-card-image"><img src="<?= base_url('assets/images/') ?>python.jpg" alt="Python Basics" /></div>
            <div class="course-card-body">
              <h3>Python Basics</h3>
              <p>Syntax, data structures &amp; practical scripting.</p>
              <button type="button" class="btn btn-primary course-test-btn" data-course="Python Basics">Start →</button>
            </div>
          </article>
          <article class="course-card" data-category="Data &amp; Analytics">
            <div class="course-card-badges"><span class="course-badge">Certificate included</span><span class="course-badge course-badge-pass">Pass 50%+</span></div>
            <div class="course-card-image"><img src="<?= base_url('assets/images/') ?>sql.jpg" alt="SQL Essentials" /></div>
            <div class="course-card-body">
              <h3>SQL Essentials</h3>
              <p>SELECT, WHERE, joins &amp; data best practices.</p>
              <button type="button" class="btn btn-primary course-test-btn" data-course="SQL Essentials">Start →</button>
            </div>
          </article>
        </div>
      </div>
    </section>

    <section class="what-you-get" id="what-you-get">
      <div class="container">
        <h2 class="section-title">What you get after you pass</h2>
        <ul class="what-you-get-list">
          <li><span class="check" aria-hidden="true">✓</span> Share-ready format for LinkedIn</li>
          <li><span class="check" aria-hidden="true">✓</span> Verification link for recruiters</li>
          <li><span class="check" aria-hidden="true">✓</span> Instant download (PDF-style certificate)</li>
        </ul>
      </div>
    </section>

    <section class="learners" id="learners">
      <div class="container">
        <h2 class="section-title">Trusted by learners and recruiters</h2>
      <div class="trust-bar">
          <div class="brands-marquee">
            <div class="brands-track">
              <figure class="brand-badge">
                <img class="badge-icon" src="<?= base_url('assets/images/') ?>brands/Coursera.png" alt="Coursera" />
                <figcaption>Coursera</figcaption>
              </figure>
              <figure class="brand-badge">
                <img class="badge-icon" src="<?= base_url('assets/images/') ?>brands/udemy.png" alt="Udemy" />
                <figcaption>Udemy</figcaption>
              </figure>
              <figure class="brand-badge">
                <img class="badge-icon" src="<?= base_url('assets/images/') ?>brands/edx.png" alt="edX" />
                <figcaption>edX</figcaption>
              </figure>
              <figure class="brand-badge">
                <img class="badge-icon" src="<?= base_url('assets/images/') ?>brands/linkedin.png" alt="LinkedIn Learning" />
                <figcaption>LinkedIn Learning</figcaption>
              </figure>
              <figure class="brand-badge">
                <img class="badge-icon" src="<?= base_url('assets/images/') ?>brands/skillshare.png" alt="Skillshare" />
                <figcaption>Skillshare</figcaption>
              </figure>
              <figure class="brand-badge">
                <img class="badge-icon" src="<?= base_url('assets/images/') ?>brands/khan academy.png" alt="Khan Academy" />
                <figcaption>Khan Academy</figcaption>
              </figure>
            </div>
          </div>
          <div class="stat-chips">
            <div class="stat-chip">4.8/5 rating</div>
            <div class="stat-chip">900+ hiring partners</div>
            <div class="stat-chip">Hallmark certificate</div>
            <div class="stat-chip">Instant download</div>
            <div class="stat-chip">Shareable verification link</div>
            <div class="stat-chip">10‑minute skill check</div>
          </div>
          <div class="companies-marquee">
            <div class="companies-track">
              <!-- Requested priority: Accenture, Google, Infosys, Meta -->
              <figure class="company-badge">
                <img class="badge-icon" src="<?= base_url('assets/images/') ?>companies/accenture.png" alt="Accenture" />
                <figcaption>Accenture</figcaption>
              </figure>
              <figure class="company-badge">
                <img class="badge-icon" src="<?= base_url('assets/images/') ?>companies/google.png" alt="Google" />
                <figcaption>Google</figcaption>
              </figure>
              <figure class="company-badge">
                <img class="badge-icon" src="<?= base_url('assets/images/') ?>companies/infosys.png" alt="Infosys" />
                <figcaption>Infosys</figcaption>
              </figure>
              <figure class="company-badge">
                <img class="badge-icon" src="<?= base_url('assets/images/') ?>companies/meta.png" alt="Meta" />
                <figcaption>Meta</figcaption>
              </figure>
              <!-- Remaining companies -->
              <figure class="company-badge">
                <img class="badge-icon" src="<?= base_url('assets/images/') ?>companies/amazon.png" alt="Amazon" />
                <figcaption>Amazon</figcaption>
              </figure>
              <figure class="company-badge">
                <img class="badge-icon" src="<?= base_url('assets/images/') ?>companies/microsoft.png" alt="Microsoft" />
                <figcaption>Microsoft</figcaption>
              </figure>
              <figure class="company-badge">
                <img class="badge-icon" src="<?= base_url('assets/images/') ?>companies/ibm.png" alt="IBM" />
                <figcaption>IBM</figcaption>
              </figure>
              <figure class="company-badge">
                <img class="badge-icon" src="<?= base_url('assets/images/') ?>companies/oracle.png" alt="Oracle" />
                <figcaption>Oracle</figcaption>
              </figure>
              <figure class="company-badge">
                <img class="badge-icon" src="<?= base_url('assets/images/') ?>companies/sap.png" alt="SAP" />
                <figcaption>SAP</figcaption>
              </figure>
              <figure class="company-badge">
                <img class="badge-icon" src="<?= base_url('assets/images/') ?>companies/tcs.jpeg" alt="TCS" />
                <figcaption>TCS</figcaption>
              </figure>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="promo" id="promo">
      <div class="container">
        <div class="promo-card">
          <div class="promo-content">
            <h2 class="section-title">Quick proof‑of‑skill. Built to stand out.</h2>
            <p class="promo-sub">
              Prove you can do the work — not just say it. Share a verification link that builds trust.
            </p>
            <ul class="promo-bullets">
              <li>10 questions. Quick challenge. Big payoff.</li>
              <li>Hallmark certificate (download instantly)</li>
              <li>Share‑ready format for LinkedIn</li>
            </ul>
          </div>
          <div class="promo-visual">
            <div class="promo-badge">✓ Recruiter‑ready</div>
            <div class="promo-badge">⚡ Fast validation</div>
            <div class="promo-badge">🔒 Verifiable online</div>
            <div class="promo-shimmer"></div>
          </div>
        </div>
      </div>
      <div class="promo-gradient"></div>
    </section>

    
  </main>

  <footer class="site-footer">
    <div class="footer-gradient"></div>
    <div class="container footer-inner">
      <div class="footer-brand">
        <div class="brand-mark">Corso E‑Learning</div>
        <p class="brand-copy">Fast, focused skill checks with instant, verifiable certificates.</p>
        <form class="newsletter" onsubmit="return false;">
          <input type="email" class="newsletter-input" placeholder="Your email" aria-label="Your email" />
          <button class="btn btn-primary newsletter-btn">Subscribe</button>
        </form>
      </div>
      <nav class="footer-links">
        <div class="footer-col">
          <h4>Explore</h4>
          <a href="#assessments">Popular Assessments</a>
          <a href="#how">How It Works</a>
        </div>
        <div class="footer-col">
          <h4>Follow</h4>
          <a href="#" aria-label="LinkedIn">LinkedIn</a>
          <a href="#" aria-label="Twitter">Twitter</a>
          <a href="#" aria-label="YouTube">YouTube</a>
        </div>
      </nav>
    </div>
    <div class="footer-bottom">
      <div class="container">
        <p>© 2026 ICTRD • All rights reserved</p>
      </div>
    </div>
  </footer>
  <script>window.CORSO_API_BASE = "<?= base_url('api') ?>";</script>
  <script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>
