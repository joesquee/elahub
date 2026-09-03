# Advocacy logos (gray)

Drop the gray-style advocacy partner logos in this folder.

## How the seeder uses them

`inc/seed-advocacy-logo-strip.php` reads every image in this directory (jpg, jpeg,
png, svg, webp), imports it into the WordPress media library under
`wp-content/uploads/advocacy-logos-gray/`, and adds (or refreshes) a Logo Strip
page-builder section on the **Speaking & Advocacy** page with
`use_default_logo_strip = false`.

The partner display name is derived from the filename (extension stripped,
underscores/hyphens replaced with spaces). URLs are left blank — fill them in
WP admin afterwards if you want clickable logos.

## Run the seed

Visit (while logged in as admin):

    /speaking-and-advocacy/?elahub_seed_advocacy_logo_strip=1

It's idempotent — re-running it re-uses existing attachments by filename and
replaces the existing section's logos with whatever is currently in this folder.

## Why this lives in the theme (not /uploads)

Files in `wp-content/uploads/` aren't versioned in git. By putting source assets
inside the theme's `assets/seeds/` folder they ship with every deploy — so the
same seed runs cleanly on staging and production without anyone needing to
upload anything separately.
