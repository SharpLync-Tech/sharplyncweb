# SharpLync local SEO review

Reviewed: 20 July 2026

Scope: Laravel routes, public pages, Blade templates, SEO metadata, robots.txt, sitemap, canonical tags, structured data, internal linking, and comparison with the live website at <https://sharplync.com.au>.

No code or website changes were made as part of this review.

## Executive summary

The main reason SharpLync is not appearing prominently for “IT Support Stanthorpe” is straightforward:

- The project contains a dedicated Stanthorpe landing page with a strong local title and content.
- That page explicitly contains `noindex, nofollow`, so Google is instructed not to show it in search.
- It is absent from the sitemap and is not linked from the main website.
- The normal public pages all declare the homepage as their canonical URL. This tells Google that `/services`, `/about`, and `/contact` may be duplicates of the homepage.
- The homepage itself is branded as “SharpLync | Home” and its main heading does not mention IT support or Stanthorpe.
- Several homepage links to service pages return 404 errors.
- A search check did not find SharpLync among the indexed results for the target phrases. Competitors have dedicated, detailed location pages aimed directly at those searches.

This is primarily a technical targeting problem, not evidence of a Google penalty.

## Pages Google should be able to index

| Page | Current targeting | Assessment |
|---|---|---|
| [Homepage](https://sharplync.com.au/) | Broad business IT support, cybersecurity, cloud services, Granite Belt | Indexable, but local targeting is weak in the title and H1. |
| [Services](https://sharplync.com.au/services) | Remote support, cybersecurity, Microsoft 365, on-site IT, Wi-Fi, backups, repairs and hardware | Valuable content, but its canonical points to the homepage and much of its detail only appears after JavaScript interaction. |
| [About](https://sharplync.com.au/about) | Founder experience, IT expertise and trust | Useful supporting page, but not locally optimised and canonicalised to the homepage. |
| [Contact](https://sharplync.com.au/contact) | Contact and support enquiries | Indexable in principle, but canonicalised to the homepage and missing a visible Stanthorpe address or service-area statement. |
| `/testimonials` | Experience and customer endorsements | Useful for trust, but has no canonical, meta description, robots directive or review-related entity structure. |
| `/trend-micro` | Trend Micro and business cybersecurity | Potential cybersecurity landing page, but it inherits the homepage description and canonical. |
| `/vendors` | Technology partnerships | Supports expertise and trust but is missing from the sitemap. |
| `/tools/cyber-glossary` | Cybersecurity definitions | Potential informational search traffic; inherits the homepage canonical. |
| `/tools/cyber-check` | Small-business and home cybersecurity checks | Potential informational traffic; inherits the homepage canonical. |
| `/marketing/sharppulse` | News and security content | Potential freshness and authority content, depending on the published database entries. |
| Policy pages | Terms, privacy, security and support policies | Indexable but not important local landing pages. |

There are also numerous SharpFleet and facilities pages. They serve separate product or application purposes and should not become part of the local IT-support SEO campaign unless intentionally separated by appropriate metadata and sitemaps.

## Is there a dedicated “IT Support Stanthorpe” page?

Yes in the code, but no in organic-search terms.

The page is:

`/ads/regions/stanthorpe`

It has:

- Title: “SharpLync | IT Support Stanthorpe & Granite Belt”
- H1: “Local IT support for Stanthorpe businesses”
- Relevant local content covering remote support, on-site visits, cybersecurity, Microsoft 365, Wi-Fi and backups

However, its shared ads layout contains:

```html
<meta name="robots" content="noindex, nofollow">
```

Google documents that `noindex` prevents a page from appearing in search results. Therefore, this is an advertising landing page, not an indexable organic location page. It is also absent from the sitemap and main navigation. See [Google’s noindex guidance](https://developers.google.com/search/docs/crawling-indexing/control-what-you-share).

The recommended solution is not necessarily to expose every ad page. Create a permanent organic page—such as `/it-support-stanthorpe`—or carefully convert the existing Stanthorpe page into a complete, indexable location page while keeping other campaign pages noindexed.

## Technical SEO issues

### Critical

#### 1. Every base-layout page points its canonical to the homepage

The shared layout hard-codes:

```html
<link rel="canonical" href="https://sharplync.com.au/">
```

Consequently, `/services`, `/about`, `/contact`, `/trend-micro`, and the public tools all claim the homepage is their preferred URL.

A canonical is a strong consolidation signal. Google recommends self-referencing canonicals for distinct pages and consistent canonical URLs in internal links and sitemaps. See [Google canonical guidance](https://developers.google.com/search/docs/crawling-indexing/consolidate-duplicate-urls).

#### 2. The only Stanthorpe landing page is noindexed

This directly prevents the strongest local page from competing organically.

#### 3. Important homepage links return 404

These homepage URLs do not have matching Laravel routes:

- `/services/security`
- `/services/cloud`
- `/services/support`

They should either lead to genuine service pages or link to relevant sections on `/services`. Broken links weaken discovery, user experience and the site’s internal topic structure.

### High priority

#### 4. The sitemap is incomplete and internally inconsistent

The sitemap is a manually maintained static file rather than generated from public routes.

Problems include:

- `/policies/remote-support` is listed but returns 404; the real route is `/policies/support`.
- `/contact` is missing.
- `/trend-micro`, `/vendors`, SharpPulse and public tools are missing.
- The Stanthorpe page is missing.
- `/register` and `/login` are listed even though robots.txt disallows them.
- `/home` is a live duplicate but is not redirected to `/`.

Sitemap priorities and `changefreq` values are much less important than listing accurate, canonical, indexable URLs.

#### 5. Duplicate host and homepage variants remain accessible

- `https://www.sharplync.com.au/` returns 200 instead of redirecting to the non-www host.
- `/home` returns 200 instead of redirecting to `/`.
- HTTP correctly redirects to HTTPS.

The canonical partially mitigates these duplicates, but permanent redirects would provide a clearer signal.

#### 6. Generic metadata is reused across unrelated pages

The base layout gives every page the same description and Open Graph data. Titles vary, but most are generic, such as:

- “SharpLync | Home”
- “SharpLync | Services”
- “SharpLync | About”

Each important page should have its own title, description, canonical and social metadata.

### Medium priority

#### 7. The services page is not structured as strong search landing content

The visible initial HTML contains service names and short descriptions, but detailed descriptions are stored in `data-*` attributes and inserted into an empty panel using JavaScript. Google can often process JavaScript, but important service content should exist as normal server-rendered text and headings.

#### 8. robots.txt over-blocks query-string URLs

`Disallow: /*?*` prevents crawlers from fetching every URL containing a query string, including tagged campaign URLs. This is not the main problem, but it can stop Google from crawling a URL and seeing its canonical. Google advises against using robots.txt as a canonicalisation mechanism.

#### 9. Public test and utility routes exist

Routes such as `/test-services`, `/test-menu`, `/test-page`, `/test-seo` and `/email-preview` are publicly declared and not blocked by the current robots rules. Some may expose database content or create low-quality indexable URLs. These should be removed, protected or noindexed in production.

#### 10. The testimonials layout has malformed and incomplete head markup

It contains two closing `</head>` tags and lacks a meta description, canonical and structured data. This is unlikely to block the whole site, but it should be corrected.

## Homepage local-search strength

The live homepage is technically accessible and Google can read its content. It includes “Granite Belt,” local support, cloud, cybersecurity and on-site support language. Nevertheless, it is not strong enough for the target searches.

### Title

Current:

> SharpLync | Home

This does not explain the service or location. A stronger direction would be:

> IT Support Stanthorpe & Granite Belt | SharpLync

If Queensland-wide business IT remains important, that can be represented in the description and supporting pages rather than diluting the primary local title.

### Description

The current description is reasonably good because it mentions IT support, Stanthorpe and the Granite Belt. It is weakened by being reused across nearly every page.

The homepage needs a unique description focused on local availability, principal services and customer type.

### H1

Current:

> Your Business, Secure & Connected

This is attractive brand copy but weak search copy. It never says what the company does or where it operates.

A better H1 direction would be:

> Local IT Support for Stanthorpe and the Granite Belt

The existing brand statement could remain as a supporting subheading.

### Page content

The page is visually focused but text-light. It does not contain enough locally specific evidence:

- No clear statement that SharpLync is based in or serves Stanthorpe.
- No service-area list.
- No business address or postcode.
- No explanation of on-site coverage.
- No dedicated computer-repair section.
- No local case studies.
- No locally relevant FAQs.
- No contextual links to individual service pages.

The services page mentions laptop and desktop repairs, but the phrase “computer repairs Stanthorpe” is not targeted by any indexable page.

## Structured data assessment

### Existing LocalBusiness schema

A `LocalBusiness` JSON-LD block exists in the shared base layout. That is a good foundation, but it is incomplete.

It currently includes:

- Business name
- URL
- Telephone
- Stanthorpe and Queensland
- Description
- LinkedIn and X profiles

It is missing or should be reviewed for:

- Most specific applicable business type
- Street address, if customers may visit or the address is publicly disclosed
- `postalCode`
- `areaServed`
- `geo`
- `openingHoursSpecification`
- `priceRange`
- Contact point
- Facebook profile in `sameAs`
- Stable business `@id`, ideally using a fragment such as `/#business`
- Logo and image details
- Consistency with the Google Business Profile

Google recommends including as much accurate address and business information as possible. See [Google LocalBusiness documentation](https://developers.google.com/search/docs/appearance/structured-data/local-business).

### Service schema

Missing.

The services page and future dedicated service pages could use `Service` entities linked to the SharpLync business as `provider`, with accurate `serviceType` and `areaServed`. This supports entity understanding, though it does not guarantee a special search display.

### Review schema

Missing, but it should not be treated as a quick route to review stars.

Testimonials can be semantically marked up when they are genuine and visibly displayed. However, Google does not display self-serving review snippets for a business marking up reviews about itself under `LocalBusiness` or `Organization`. See [Google’s self-serving review policy](https://developers.google.com/search/blog/2019/09/making-review-rich-results-more-helpful).

Google Business Profile reviews are more important for local-map visibility.

### FAQ schema

Missing.

Useful FAQ content could be added to the Stanthorpe and repair pages and marked up with `FAQPage` only when the same questions and answers are visibly present. It may help Google understand the content, but FAQ rich results are limited and should not be promised.

## Prioritised action plan

### Phase 1: quick technical fixes

1. Give every indexable page a self-referencing canonical.
2. Add per-page title, meta description, Open Graph title, description and URL controls.
3. Redirect `/home` to `/`.
4. Redirect `www` to the preferred non-www HTTPS host.
5. Fix the three broken homepage service links.
6. Regenerate or replace the sitemap with only canonical, 200-status, indexable URLs.
7. Remove login and register from the public sitemap.
8. Correct the invalid `/policies/remote-support` sitemap entry.
9. Protect or remove public `/test-*` and preview routes.
10. Add appropriate `noindex` controls to private, authentication and application areas independently of robots.txt.
11. Correct the testimonials layout head and metadata.
12. Keep ad-only landing pages noindexed unless one is intentionally converted into an organic page.

### Phase 2: local landing pages

Highest priority:

- `/it-support-stanthorpe`
- `/computer-repairs-stanthorpe`

The IT-support page should cover:

- Local and remote business IT support
- On-site coverage and realistic response arrangements
- Managed IT
- Microsoft 365
- Cybersecurity
- Networking and Wi-Fi
- Backups
- Industries served locally
- Nearby service areas
- Genuine local proof or case studies
- FAQs
- Strong contact action

The computer-repairs page should clearly state whether SharpLync serves residential customers, businesses, or both. It should cover only repairs genuinely offered—screens, batteries, desktops, laptops, upgrades, diagnosis, data transfer and on-site or remote limitations.

Secondary pages can then target:

- Managed IT services Stanthorpe
- Microsoft 365 support Stanthorpe
- Cybersecurity Stanthorpe
- Business Wi-Fi and networking Stanthorpe
- IT support Granite Belt
- IT support Warwick, if genuine service coverage exists

Avoid creating thin pages that merely swap town names.

### Phase 3: homepage and internal linking

1. Change the homepage title and H1 to state service plus location.
2. Add a concise Stanthorpe and Granite Belt introduction.
3. Link prominently to the Stanthorpe IT support and computer-repair pages.
4. Convert major services into crawlable page links rather than JavaScript-only expansion cards.
5. Add a visible service-area section.
6. Add local case studies or project examples.
7. Link relevant SharpPulse articles to service and location pages.
8. Add breadcrumb markup on service and location pages.

### Phase 4: authority and local prominence

Google says local rankings are mainly based on relevance, distance and prominence, including business information, links and reviews. See [Google Business Profile local-ranking guidance](https://support.google.com/business/answer/7091?hl=en).

Build genuine local signals through:

- Granite Belt and Southern Downs business directories
- Local chamber or industry memberships
- Supplier and partner listings
- Local sponsorships or community organisations
- Relevant local news mentions
- Consistent business name, phone and service-area details
- Genuine customer reviews

## Google Search Console checks

After technical changes are approved and deployed:

1. Verify both Domain property and preferred HTTPS property ownership.
2. Inspect `/`, `/services`, and the new Stanthorpe page.
3. Compare “User-declared canonical” with “Google-selected canonical.”
4. Check whether current pages are classified as:
   - Duplicate, Google chose different canonical
   - Crawled – currently not indexed
   - Discovered – currently not indexed
   - Excluded by `noindex`
   - Blocked by robots.txt
5. Submit the corrected sitemap.
6. Request indexing for the homepage and primary Stanthorpe page.
7. Review manual actions and security issues.
8. Examine search performance for:
   - SharpLync
   - IT support Stanthorpe
   - computer repairs Stanthorpe
   - managed IT Stanthorpe
   - IT support Granite Belt
9. Monitor page indexing after Google recrawls the corrected canonicals.

## Google Business Profile checks

Verify that the profile:

- Is claimed and verified.
- Uses exactly the same business name and primary phone as the website.
- Has the correct primary category and relevant secondary categories.
- Accurately identifies Stanthorpe or the service area.
- Does not display a residential address unless customers are genuinely served there.
- Contains complete hours, services, description, website and appointment or contact links.
- Links to the new Stanthorpe page where appropriate.
- Has recent photos and ongoing genuine customer reviews.
- Receives timely responses to reviews.
- Does not claim service areas or opening arrangements that cannot be fulfilled.

## Overall priority

The recommended order is:

1. Repair canonicals, broken links, duplicate URLs and sitemap.
2. Publish one genuinely useful, indexable “IT Support Stanthorpe” page.
3. Strengthen the homepage’s title, H1 and local content.
4. Publish a separate computer-repairs page if that is a real priority service.
5. Complete LocalBusiness and Service structured data.
6. Verify indexing and canonical selection in Search Console.
7. Strengthen the Google Business Profile, reviews and local citations.

The strongest immediate opportunity is already present in the repository: the Stanthorpe copy exists, but it currently sits behind an explicit organic-search exclusion.
