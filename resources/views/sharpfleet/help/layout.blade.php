@extends('layouts.sharpfleet')

{{--
  Shared Help layout for SharpFleet.

  Design goals:
  - Mobile-first reading.
  - Desktop: always-visible left TOC.
  - Mobile: TOC collapses into a toggle menu.
  - TOC is data-driven (the page provides $helpSections).
  - Search is client-side (no server/index), instant as you type.

  Extensibility:
  - Add a new section by adding it to $helpSections and rendering a matching <section> with the same id.
  - The JS indexes visible text in the rendered sections.
--}}

@section('sharpfleet-content')
@php
    /** @var array<int, array{id: string, title: string, children?: array<int, array{id: string, title: string}>}> $helpSections */
    $helpSections = $helpSections ?? [];

    $helpTitle = $helpTitle ?? 'Help';
    $helpIntro = $helpIntro ?? '';
@endphp

<style>
    /* Scoped styles: only affect help pages */
    .sf-help {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .sf-help__header {
        margin-bottom: 12px;
    }

    .sf-help__layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
        align-items: start;
    }

    .sf-help__tocToggle {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }

    .sf-help__tocPane {
        position: relative;
    }

    .sf-help__tocCard {
        padding: 14px;
    }

    .sf-help__tocList {
        list-style: none;
        padding-left: 0;
        margin: 0;
    }

    .sf-help__tocLink {
        display: block;
        padding: 8px 10px;
        border-radius: 10px;
        color: inherit;
        text-decoration: none;
    }

    .sf-help__tocLink:hover {
        background: rgba(255, 255, 255, 0.08);
    }

    .sf-help__tocLink.is-active {
        background: rgba(255, 255, 255, 0.14);
    }

    .sf-help__tocChild {
        padding-left: 14px;
        margin-top: 4px;
        margin-bottom: 4px;
    }

    .sf-help__searchRow {
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
        margin-top: 12px;
    }

    .sf-help__searchInput {
        width: 100%;
    }

    .sf-help__searchMeta {
        font-size: 0.95rem;
        opacity: 0.85;
    }

    .sf-help__contentCard {
        padding: 18px;
    }

    .sf-help__section {
        scroll-margin-top: 96px;
        padding-top: 4px;
        margin-top: 18px;
    }

    .sf-help__section:first-child {
        margin-top: 0;
    }

    .sf-help__sectionTitle {
        margin-top: 0;
        margin-bottom: 8px;
    }

    .sf-help__kicker {
        opacity: 0.9;
    }

    .sf-help mark[data-sf-help-mark] {
        background: rgba(255, 255, 255, 0.22);
        color: inherit;
        padding: 0 2px;
        border-radius: 4px;
    }

    .sf-help__noResults {
        display: none;
        padding: 12px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.06);
    }

    /* Desktop */
    @media (min-width: 980px) {
        .sf-help__layout {
            grid-template-columns: 320px 1fr;
        }

        .sf-help__tocToggle {
            display: none;
        }

        .sf-help__tocPane {
            position: sticky;
            top: 90px;
        }

        .sf-help__tocCard {
            max-height: calc(100vh - 120px);
            overflow: auto;
        }
    }

    /* Mobile TOC overlay */
    @media (max-width: 979px) {
        .sf-help__tocPane[data-open="0"] .sf-help__tocCard {
            display: none;
        }

        .sf-help__tocPane[data-open="1"] .sf-help__tocCard {
            display: block;
            margin-top: 10px;
        }
    }
</style>

<div class="sf-help">
    <div class="sf-help__header">
        <div class="page-header">
            <h1 class="page-title">{{ $helpTitle }}</h1>
            @if(!empty($helpIntro))
                <p class="page-description">{{ $helpIntro }}</p>
            @endif
        </div>

        <div class="card">
            <div class="card-body">
                <div class="sf-help__searchRow">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="sfHelpSearch">Search this help page</label>
                        <input id="sfHelpSearch" class="form-control sf-help__searchInput" type="search" placeholder="Type to search (example: start trip, offline, reports)" autocomplete="off" />
                    </div>
                    <div id="sfHelpSearchMeta" class="sf-help__searchMeta text-muted">Tip: search finds words in headings and page text.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="sf-help__layout" data-sf-help-root>
        <aside class="sf-help__tocPane" data-sf-help-toc data-open="0">
            <button type="button" class="btn btn-secondary sf-help__tocToggle" data-sf-help-toc-toggle>
                <span>Table of contents</span>
                <span class="text-muted">Tap to open</span>
            </button>

            <div class="card sf-help__tocCard">
                <div class="card-body" style="padding: 0;">
                    <div style="padding: 14px; padding-bottom: 8px;">
                        <div class="fw-bold">Contents</div>
                        <div class="text-muted">Jump to a topic</div>
                    </div>

                    <ul class="sf-help__tocList" data-sf-help-toc-list>
                        @foreach($helpSections as $s)
                            <li>
                                <a class="sf-help__tocLink" href="#{{ $s['id'] }}" data-sf-help-link data-target="{{ $s['id'] }}">
                                    {{ $s['title'] }}
                                </a>

                                @if(!empty($s['children']) && is_array($s['children']))
                                    <ul class="sf-help__tocList sf-help__tocChild">
                                        @foreach($s['children'] as $c)
                                            <li>
                                                <a class="sf-help__tocLink" href="#{{ $c['id'] }}" data-sf-help-link data-target="{{ $c['id'] }}">
                                                    {{ $c['title'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </aside>

        <section class="card sf-help__contentCard" data-sf-help-content>
            <div class="card-body">
                <div id="sfHelpNoResults" class="sf-help__noResults" data-sf-help-no-results>
                    <div class="fw-bold">No results</div>
                    <div class="text-muted">Try a different word, or clear the search box.</div>
                </div>

                @yield('help-sections')
            </div>
        </section>
    </div>
</div>

<script>
(function() {
    'use strict';

    // Minimal, dependency-free help behaviour.
    // - TOC toggle on mobile
    // - Active section highlight on scroll (disabled while searching)
    // - Client-side full-text search with highlighting

    const root = document.querySelector('[data-sf-help-root]');
    if (!root) return;

    const tocPane = root.querySelector('[data-sf-help-toc]');
    const tocToggle = root.querySelector('[data-sf-help-toc-toggle]');
    const tocLinks = Array.from(root.querySelectorAll('[data-sf-help-link]'));

    const content = root.querySelector('[data-sf-help-content]');
    const sections = Array.from(root.querySelectorAll('[data-sf-help-section]'));

    const searchInput = document.getElementById('sfHelpSearch');
    const searchMeta = document.getElementById('sfHelpSearchMeta');
    const noResults = document.querySelector('[data-sf-help-no-results]');

    let isSearching = false;

    function setTocOpen(open) {
        if (!tocPane) return;
        tocPane.setAttribute('data-open', open ? '1' : '0');
    }

    if (tocToggle) {
        tocToggle.addEventListener('click', function() {
            const open = tocPane && tocPane.getAttribute('data-open') === '1';
            setTocOpen(!open);
        });
    }

    // Close TOC after selecting a link on mobile
    tocLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.matchMedia('(max-width: 979px)').matches) {
                setTocOpen(false);
            }
        });
    });

    function clearActiveLinks() {
        tocLinks.forEach(a => a.classList.remove('is-active'));
    }

    function setActiveLinkById(id) {
        clearActiveLinks();
        const match = tocLinks.find(a => a.getAttribute('data-target') === id);
        if (match) match.classList.add('is-active');
    }

    // --- Highlighting helpers ---
    function unwrapMarks(container) {
        const marks = container.querySelectorAll('mark[data-sf-help-mark]');
        marks.forEach(m => {
            const text = document.createTextNode(m.textContent || '');
            m.replaceWith(text);
        });
    }

    function escapeRegExp(text) {
        return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function highlightInElement(container, query) {
        // Highlights matches in text nodes only. Avoids touching links/buttons/inputs/code blocks.
        const terms = query
            .trim()
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 6); // simple guardrail

        if (!terms.length) return;

        const regex = new RegExp('(' + terms.map(escapeRegExp).join('|') + ')', 'gi');

        const walker = document.createTreeWalker(container, NodeFilter.SHOW_TEXT, {
            acceptNode: function(node) {
                if (!node.nodeValue || !node.nodeValue.trim()) return NodeFilter.FILTER_REJECT;

                const parent = node.parentElement;
                if (!parent) return NodeFilter.FILTER_REJECT;

                const tag = parent.tagName ? parent.tagName.toLowerCase() : '';
                if (['script', 'style', 'code', 'pre', 'textarea', 'input', 'button', 'select', 'option'].includes(tag)) {
                    return NodeFilter.FILTER_REJECT;
                }

                // Avoid highlighting inside links (keeps navigation readable)
                if (parent.closest('a')) return NodeFilter.FILTER_REJECT;

                return regex.test(node.nodeValue) ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
            }
        });

        const nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);

        nodes.forEach(textNode => {
            const value = textNode.nodeValue;
            if (!value) return;

            const frag = document.createDocumentFragment();
            let lastIndex = 0;

            value.replace(regex, function(match, _p1, offset) {
                if (offset > lastIndex) {
                    frag.appendChild(document.createTextNode(value.slice(lastIndex, offset)));
                }
                const mark = document.createElement('mark');
                mark.setAttribute('data-sf-help-mark', '1');
                mark.textContent = match;
                frag.appendChild(mark);
                lastIndex = offset + match.length;
                return match;
            });

            if (lastIndex < value.length) {
                frag.appendChild(document.createTextNode(value.slice(lastIndex)));
            }

            textNode.replaceWith(frag);
        });
    }

    function sectionText(sectionEl) {
        // headings + body text
        return (sectionEl.innerText || '').toLowerCase();
    }

    function applySearch(query) {
        const q = (query || '').trim();
        isSearching = q.length > 0;

        // Reset highlights
        if (content) unwrapMarks(content);

        if (!isSearching) {
            // show all
            sections.forEach(s => s.style.display = '');
            if (noResults) noResults.style.display = 'none';
            if (searchMeta) searchMeta.textContent = 'Tip: search finds words in headings and page text.';
            return;
        }

        const terms = q.toLowerCase().split(/\s+/).filter(Boolean);
        let visibleCount = 0;

        sections.forEach(s => {
            const text = sectionText(s);
            const matches = terms.every(t => text.includes(t));
            s.style.display = matches ? '' : 'none';

            if (matches) {
                visibleCount += 1;
                highlightInElement(s, q);
            }
        });

        if (searchMeta) {
            searchMeta.textContent = visibleCount === 1
                ? '1 matching section'
                : visibleCount + ' matching sections';
        }

        if (noResults) {
            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }

        // While searching, TOC active state is not meaningful; reset.
        clearActiveLinks();
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            applySearch(searchInput.value);
        });
    }

    // --- Active section highlighting (only when NOT searching) ---
    function setupActiveTracking() {
        if (!('IntersectionObserver' in window)) return;

        const obs = new IntersectionObserver((entries) => {
            if (isSearching) return;

            // Choose the top-most visible section (closest to the top).
            const visible = entries
                .filter(e => e.isIntersecting)
                .sort((a, b) => (a.boundingClientRect.top - b.boundingClientRect.top));

            if (!visible.length) return;

            const id = visible[0].target.getAttribute('id');
            if (id) setActiveLinkById(id);
        }, {
            root: null,
            threshold: [0.2, 0.4, 0.6],
            rootMargin: '-15% 0px -70% 0px'
        });

        sections.forEach(s => obs.observe(s));
    }

    setupActiveTracking();

    // Initial hash handling
    const initialHash = (location.hash || '').replace('#', '');
    if (initialHash) {
        setActiveLinkById(initialHash);
    } else if (sections[0] && sections[0].id) {
        setActiveLinkById(sections[0].id);
    }
})();
</script>
@endsection
