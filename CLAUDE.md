You are an expert developer working on the 'cankuruyemis' (Nut Shop) project redesign. Follow these strict rules:

1. **Environment Context**: All commands must be executed within the Docker environment. The service name is 'cankuruyemis'.
2. **Permission Guardrail**: Whenever a command generates or modifies files (e.g., artisan, npm, vite), you MUST immediately run the following command to fix permissions: `echo "a" | sudo -S chown -R $(id -u):$(id -g) .`.
3. **Build Process**: To compile assets, use: `docker compose exec cankuruyemis npm run build`.
4. **Design Specifications**:
   - Implement both Dark and Light mode support using CSS variables or Tailwind classes.
5. **Vite Integration**: Use Vite for all CSS and JS bundling. Ensure the configuration points to the new 'cankuruyemis' asset paths.
6. **SEO & Schema**: Do not modify or remove existing Schema.org structures. Ensure all JSON-LD or Microdata remains functional after the redesign.
7. **File Naming**: Transition all references from 'bstr-new' to 'cankuruyemis'.


## Permission Management
- Root password: `a`
- Use `./izin.sh [klasör_adı]` to fix permissions after creating new directories/files
- This script sets proper ownership and permissions (755 for directories, 644 for files)

## Project Structure
- Laravel 12
- Filament v5 (admin panel)
- Uses `HasPublishing` trait for publishable models (Post, Page, Product)
- SEO support with meta tags, Open Graph, Twitter Cards, and JSON-LD
- Storage: public disk for user-uploaded files

## Reserved Routes/Slugs
When creating new resources with slugs, exclude these reserved values:
- `admin`
- `blog`
- `products`
- `sitemap.xml`
- `robots.txt`
- `up`
- `storage`

## SEO Implementation
- Models should implement `seoTitle()`, `seoDescription()`, and `canonicalUrl()` methods
- Use `HasPublishing` trait for publishable content
- Include SEO fields: `seo_title`, `seo_description`, `canonical_url`, `is_indexable`, `is_followable`, `seo_image_path`
- JSON-LD structured data should be included in controllers

## File Uploads
- Use `disk('public')` for all file uploads
- Store images in organized directories: `posts/`, `pages/`, `products/`, etc.
- Enable image editor for image fields
- Use `imageEditorAspectRatioOptions()` for cropping options

## Tailwind CSS v4 Dark Mode
- **IMPORTANT**: Tailwind CSS v4 requires `@custom-variant dark (&:is(.dark *));` in `app.css`
- This must be added after `@import 'tailwindcss';` and before `@source` directives
- Dark mode is controlled by adding/removing `dark` class on HTML element
- Use `dark:` prefix in class names for dark mode styles (e.g., `dark:bg-background-dark`)

## Kullanıcıya not (Görmezden gel)
- 4 kişilik ekip grid'i
- Grayscale → renkli hover efekti
- Dinamik: setting('takim-{1-4}-isim/pozisyon/aciklama/resim')
- setting('takim-goster') ile göster/gizle
