# SharpLync SEO implementation and AI recommendation guide

Updated: 20 July 2026

This document is the hand-off for developers and AI assistants working on SharpLync organic search. Read it together with:

- `docs/stanthorpe-local-seo-review.md`
- `docs/seo-recommendation-context.json`
- `config/seo.php`

## Objective

Improve qualified local visibility for real services SharpLync offers in Stanthorpe, the Granite Belt and the Southern Downs, initially focusing on:

1. IT Support Stanthorpe
2. Computer Repairs Stanthorpe
3. Managed IT Stanthorpe
4. Microsoft 365 Support Stanthorpe
5. Cybersecurity Stanthorpe
6. Business Wi-Fi and Networking Stanthorpe

Recommendations must prioritise useful, accurate pages and genuine local evidence. Do not create pages that only replace one town name with another.

## Truth and safety constraints for AI-generated recommendations

An AI assistant must not invent:

- A public street address
- Opening hours
- Guaranteed response times or same-day service
- Prices, warranties or “no fix, no fee” promises
- Certifications, awards, partner status or staff numbers
- Customer names, reviews, ratings or case-study outcomes
- Geographic coverage beyond verified service areas
- Residential service availability unless the business confirms it

Unknown facts are represented as `null` in `seo-recommendation-context.json`. Ask the business owner to verify those fields before using them on the site or Google Business Profile.

## Implemented technical architecture

### Canonical business data

`config/seo.php` is the single source for:

- Canonical site URL
- Default title and description
- Business identity and contact details
- Verified service areas
- Social profiles
- Canonical sitemap paths

Keep it aligned with Google Business Profile. Do not duplicate these values in new templates when the config value can be used.

### Shared metadata

`resources/views/layouts/base.blade.php` supports these Blade sections:

```blade
@section('title', 'Unique page title')
@section('meta_description', 'Unique and accurate page summary.')
@section('canonical', rtrim(config('seo.site_url'), '/') . route('route.name', [], false))
@section('robots', 'index, follow')
@section('og_image', asset('images/page-social-image.png'))
```

Every distinct public page should supply title, description and canonical sections. The shared layout outputs Open Graph and Twitter metadata from the same values.

### Structured data

The base layout emits a stable `LocalBusiness`/`ProfessionalService` entity and a `WebSite` entity. New local service pages should reference the business rather than defining a competing entity:

```php
'provider' => ['@id' => config('seo.business.id')],
```

Page-specific JSON-LD can be supplied through:

```blade
@push('structured_data')
<script type="application/ld+json">@json($pageSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
@endpush
```

Use `Service` for a genuine service, `BreadcrumbList` for visible navigation hierarchy, and `FAQPage` only when every marked-up question and answer appears visibly on the page.

Do not add `aggregateRating` or self-serving `Review` markup to the SharpLync business entity. Testimonials may remain visible as trust content, but Google does not award organic review stars to a business for reviews about itself.

### Canonical redirects and crawl controls

- `/home` permanently redirects to `/`.
- `www.sharplync.com.au` permanently redirects to the non-www host in production through `CanonicalHost` middleware.
- Authentication, administration, portal and application screens receive `X-Robots-Tag: noindex, nofollow`.
- Advertising landing pages remain `noindex, nofollow` through the ads layout.
- Public database test and email-preview routes were removed.
- Query-string crawling is no longer blocked globally in robots.txt, allowing crawlers to inspect canonical signals.

### Sitemap generation

The source URL list is `config('seo.sitemap')`. Regenerate the static production sitemap after changing that list:

```shell
php artisan seo:generate-sitemap
```

The generated file is `public/sitemap.xml`. Only canonical, indexable, public URLs should be included.

## Implemented landing pages

### `/it-support-stanthorpe`

Primary intent: a Stanthorpe business looking for local or remote IT support.

Included entities and topics:

- Business IT support
- On-site support by arrangement
- Remote support
- Microsoft 365
- Cybersecurity
- Networks and Wi-Fi
- Backup and continuity
- IT planning
- Stanthorpe, Granite Belt and Southern Downs service areas
- Visible FAQs, Service schema and Breadcrumb schema

### `/computer-repairs-stanthorpe`

Primary intent: a local business or professional seeking laptop or desktop diagnosis and repair advice.

Included topics:

- Laptop and desktop diagnosis
- Screens and batteries
- Storage and memory upgrades
- Slow computers
- Windows and software issues
- New computer setup
- Repair-versus-replace advice
- Honest caveats regarding parts availability and specialist data recovery
- Visible FAQs, Service schema and Breadcrumb schema

## Recommended next content decisions

Before creating more pages, obtain Search Console and Google Business Profile evidence. A new page is justified when:

- The query represents a distinct service intent.
- SharpLync genuinely provides that service in the stated location.
- The page can contain substantially different, useful information.
- There is a clear conversion action and internal-link path.
- Claims can be supported by business facts or real customer evidence.

Likely next candidates, subject to evidence:

- `/managed-it-stanthorpe`
- `/microsoft-365-support-stanthorpe`
- `/cybersecurity-stanthorpe`
- `/business-wifi-stanthorpe`
- Local case studies with customer permission
- Practical SharpPulse articles answering actual customer questions

## Data AI needs for better future recommendations

Export or record the following monthly without including customer personal information:

### Google Search Console

- Query
- Landing page
- Country and device
- Impressions
- Clicks
- CTR
- Average position
- Page indexing status
- User-declared and Google-selected canonical
- Crawl date and crawl result for priority URLs

### Google Business Profile

- Verified primary and secondary categories
- Whether the profile is storefront or service-area based
- Verified service areas
- Website and appointment URL
- Calls, website clicks and direction requests
- Review count and average rating
- Common words and services mentioned naturally in reviews

### Website conversions

- Landing page
- Contact-form completions
- Phone-link clicks
- Qualified versus irrelevant enquiries
- Service requested
- General service area only; do not put customer addresses in AI context

## Recommended AI analysis prompt

Use a prompt similar to:

> Review the SharpLync SEO context, current page inventory, Search Console export and Google Business Profile summary. Recommend the smallest set of changes most likely to improve qualified enquiries. Separate technical indexing issues, content gaps, internal-link improvements and off-site local signals. Do not invent business facts. Cite the supplied evidence for every priority and mark any required owner verification.

## Deployment checklist

1. Confirm `APP_URL` and optional `SEO_SITE_URL` resolve to `https://sharplync.com.au`.
2. Run `php artisan optimize:clear`.
3. Run `php artisan seo:generate-sitemap`.
4. Run the automated test suite.
5. Check rendered source for unique title, description, canonical and valid JSON-LD.
6. Verify `/home` and the www host return 301 to their canonical versions.
7. Verify all sitemap URLs return 200 and do not contain `noindex`.
8. Validate local page schema with Google Rich Results Test and Schema.org Validator.
9. Deploy.
10. Submit the sitemap and inspect priority URLs in Google Search Console.
11. Request indexing for `/`, `/it-support-stanthorpe` and `/computer-repairs-stanthorpe`.

## Owner verification still required

- Google Business Profile ownership and exact categories
- Whether a public street address should be displayed
- Normal business or support hours
- Exact boundaries and travel terms for on-site service
- Whether residential customers are accepted
- Any public pricing or response-time commitments
- Permission to publish customer case studies or testimonials with identifying details
