# SEO & Social Metadata

This project implements best‑practice SEO without altering UI/UX.

## What’s Included
- Unique titles & meta descriptions per page (55–60 chars title; 150–160 chars description)
- Canonical link on every page
- Open Graph & Twitter Cards
- Organization JSON‑LD
- robots.txt route
- Dynamic sitemap.xml (public pages + optional blog/news/projects/docs)

## How to Set Page Meta
- Pass variables from controllers or route closures to views:

```
'title', 'description', 'ogTitle', 'ogDescription', 'ogImage', 'twitterTitle', 'twitterDescription', 'twitterImage'
```

- These are used by `resources/views/layouts/app.blade.php`:
  - `<title>{{ $title }}</title>`
  - `<meta name="description" ...>`
  - OG/Twitter tags with sensible fallbacks

## robots.txt
- Route at `/robots.txt` declares the sitemap and allows indexing.

## Sitemap
- Route at `/sitemap.xml` compiles URLs:
  - Static: home, about, contact, privacy, terms, disclaimer
  - Optional: blog/news/projects/docs if models exist with `slug` + `updated_at`

## Social Images
- Default OG/Twitter image: `public/images/logo.jpeg`.
- Override per page via `ogImage`/`twitterImage`.

## Tips
- Keep titles concise and keyword‑relevant.
- Avoid duplicate titles and descriptions.
- Provide high‑quality social images (1200×630).
