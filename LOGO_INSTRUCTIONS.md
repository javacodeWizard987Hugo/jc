# Logo Setup Instructions

## Logo Location

Place your logo image file in the following location:

```
public/images/logo.png
```

## Supported Formats

The logo should be in one of these formats:
- **PNG** (recommended) - `logo.png`
- **JPG/JPEG** - `logo.jpg` or `logo.jpeg`
- **SVG** - `logo.svg`

## Recommended Logo Specifications

- **Format**: PNG with transparent background (recommended)
- **Size**: 200-300px width (height will scale proportionally)
- **Aspect Ratio**: Any (will maintain aspect ratio)
- **File Size**: Keep under 500KB for best performance

## Logo Sizes in Different Locations

- **Sidebar**: Maximum height 64px (h-16), max width 180px
- **Login Page**: Maximum height 80px (h-20), max width 200px
- **Invoice**: Height 50px, max width 150px

## How to Add Your Logo

1. **Copy your logo file** to the `public/images/` directory
2. **Name it** `logo.png` (or update the filename in `resources/views/layouts/app.blade.php` if using a different name)
3. **Refresh your browser** - the logo will appear in the sidebar

## Current Implementation

The logo is displayed in:
- **Sidebar header** (top of the navigation menu) - Size: 64px height (h-16)
- **Login page** (authentication page) - Size: 80px height (h-20)
- **Invoice/Receipt** (printed invoices) - Size: 50px height
- **Fallback**: If the logo image is not found, an emoji (🍗) or placeholder will be displayed instead

## File Path Reference

- **Full path**: `C:\Users\asus\Downloads\POS laravel  finalDone\laravel\laravel\public\images\logo.png`
- **Web URL**: `http://your-domain/images/logo.png` (or `http://localhost:8000/images/logo.png` in development)

## Customization

If you want to use a different filename or location, edit `resources/views/layouts/app.blade.php` around line 902:

```blade
<img src="{{ asset('images/logo.png') }}" 
```

Change `logo.png` to your filename, or change `images/` to a different directory.

## Notes

- The logo will automatically scale to fit (max height: 48px / 3rem)
- The logo maintains its aspect ratio
- If the image fails to load, the emoji fallback will show
- Make sure the file has proper read permissions

