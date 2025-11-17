/* ============================================================
   TESTIMONIALS GRID – MODERN 2025 STYLE
   ============================================================ */
.testimonials-grid {
    padding: 120px 0 160px;
    background: linear-gradient(135deg, #0A2A4D 0%, #104976 40%, #2CBFAE 100%);
    color: #e9f4f3;
    min-height: 100vh;
}

.testimonials-grid .container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 2rem;
}

/* Header */
.testimonials-header {
    text-align: center;
    max-width: 900px;
    margin: 0 auto 100px;
}

.testimonials-header h1 {
    font-size: 3.2rem;
    font-weight: 600;
    margin-bottom: 1rem;
    background: linear-gradient(to right, #fff, #c0f0ec);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.testimonials-header p {
    font-size: 1.3rem;
    line-height: 1.7;
    opacity: 0.92;
}

/* Grid */
.grid-container {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 2.8rem;
    align-items: start;
}

.testimonial-card {
    background: rgba(255, 255, 255, 0.98);
    color: #0A2A4D;
    border-radius: 28px;
    padding: 3rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0,0,0,0.22);
    transition: all 0.4s ease;
    backdrop-filter: blur(10px);
}

.testimonial-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 32px 70px rgba(0,0,0,0.3);
}

/* Card sizes */
.testimonial-card.featured   { grid-column: span 7; }
.testimonial-card.large      { grid-column: span 5; }
.testimonial-card.small      { grid-column: span 4; }

/* Initials badge */
.initials {
    position: absolute;
    top: -34px;
    left: 32px;
    width: 74px;
    height: 74px;
    background: #2CBFAE;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.7rem;
    font-weight: 700;
    box-shadow: 0 10px 30px rgba(44,191,174,0.4);
    z-index: 10;
}

/* Quote */
.quote-text {
    font-size: 1.18rem;
    line-height: 1.85;
    margin: 2rem 0;
}

.more {
    font-weight: 600;
    color: #2CBFAE;
    opacity: 0.9;
}

.expand-link {
    color: #2CBFAE;
    text-decoration: none;
    font-weight: 600;
    border-bottom: 1.5px solid transparent;
    transition: border 0.3s;
}

.expand-link:hover {
    border-bottom: 1.5px solid #2CBFAE;
}

.full-text {
    display: none;
    margin-top: 1.8rem;
    padding-top: 1.8rem;
    border-top: 1px solid #e2e8f0;
    font-size: 1.15rem;
    line-height: 1.9;
    animation: fadeIn 0.5s ease;
}

.testimonial-card.expanded .more { display: none; }
.testimonial-card.expanded .full-text { display: block; }

/* Author */
.author {
    margin-top: 1.5rem;
    font-size: 1.1rem;
}

.author .name {
    display: block;
    font-size: 1.35rem;
    font-weight: 600;
    color: #0A2A4D;
}

.author .role {
    display: block;
    margin-top: 0.3rem;
    opacity: 0.8;
    font-size: 1rem;
}

/* Responsive */
@media (max-width: 1024px) {
    .testimonial-card { grid-column: span 12 !important; }
    .testimonials-header h1 { font-size: 2.8rem; }
    .initials { width: 68px; height: 68px; font-size: 1.5rem; top: -30px; left: 28px; }
}

@media (max-width: 640px) {
    .testimonials-grid { padding: 80px 0 120px; }
    .testimonials-header { margin-bottom: 60px; padding: 0 1rem; }
    .testimonials-header h1 { font-size: 2.4rem; }
    .testimonial-card { padding: 2.5rem; }
}

/* Animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem;
    font-size: 1.3rem;
    opacity: 0.8;
}