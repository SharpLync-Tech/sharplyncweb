@extends('layouts.base')

@section('content')

<link rel="stylesheet" href="{{ asset('css/policies.css') }}">

<div class="policy-wrapper">

    {{-- Sticky Table of Contents (ONLY if the page defines one) --}}
    @hasSection('policy_toc')
        <aside class="policy-toc">
            <h3>On This Page</h3>
            <ul id="policy-toc-list"></ul>
        </aside>
    @endif

    <div class="policy-container">
        <h1 class="policy-title">@yield('policy_title')</h1>
        <div class="policy-version">Version: @yield('policy_version')</div>
        <div class="policy-updated">Last updated: @yield('policy_updated')</div>

        @yield('policy_content')
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    // Prevent TOC JS from running on pages with NO TOC
    const tocList = document.getElementById("policy-toc-list");
    if (!tocList) return;

    const sections = document.querySelectorAll(".policy-section");

    // ==== Auto-generate Table of Contents ====
    sections.forEach((sec, index) => {
        const h2 = sec.querySelector("h2");
        if (!h2) return;

        const id = "section-" + index;
        h2.id = id;

        // Add clickable anchor
        h2.addEventListener("click", () => {
            location.hash = id;
        });

        // Insert into TOC
        const li = document.createElement("li");
        li.innerHTML = `<a href="#${id}">${h2.innerText}</a>`;
        tocList.appendChild(li);
    });

    const tocLinks = tocList.querySelectorAll("a");

    // ==== Highlight TOC based on scroll position ====
    function updateActive() {
        let current = "";

        sections.forEach(sec => {
            const rect = sec.getBoundingClientRect();
            if (rect.top <= 130 && rect.bottom >= 130) {
                current = sec.querySelector("h2").id;
            }
        });

        tocLinks.forEach(a => {
            a.classList.remove("active");
            if (a.getAttribute("href") === "#" + current) {
                a.classList.add("active");
            }
        });
    }

    window.addEventListener("scroll", updateActive);
    updateActive();

    // ==== Fade-in animation on scroll ====
    function reveal() {
        sections.forEach(sec => {
            if (sec.getBoundingClientRect().top < window.innerHeight - 80) {
                sec.classList.add("visible");
            }
        });
    }

    window.addEventListener("scroll", reveal);
    reveal();
});
</script>

@endsection
