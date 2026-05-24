{{--
=============================================================================
ECOBANK026 — UI DESIGN REFERENCE
=============================================================================
This file is a design guide, not a rendered page.
Use these patterns when building new Blade pages.
=============================================================================
--}}


{{-- =========================================================================
1. DESIGN PRINCIPLES
==========================================================================
- Modern eco banking dashboard aesthetic
- Green-themed, clean, professional
- Soft cards with rounded corners and subtle shadows
- Minimal UI — avoid clutter
- Consistent spacing (p-6, gap-6, space-y-6)
- Mobile-first responsive design
- Accessibility: proper contrast, focus states, labels
========================================================================= --}}


{{-- =========================================================================
2. COLOR TOKENS
==========================================================================
Primary Green:     text-emerald-700  bg-emerald-700
Dark Green:        text-emerald-950  bg-emerald-950
Accent Green:      text-green-500    bg-green-500
Soft Green BG:     bg-emerald-50     bg-green-50
Page Background:   bg-slate-50
Card Background:   bg-white
Text Primary:      text-slate-900
Text Muted:        text-slate-500
Text On Primary:   text-white
Border:            border-slate-200
Danger:            text-red-500      bg-red-500
Warning:           text-amber-500    bg-amber-500
Success:           text-emerald-600  bg-emerald-600
========================================================================= --}}


{{-- =========================================================================
3. TYPOGRAPHY STANDARDS
========================================================================= --}}

{{-- Page title --}}
<h1 class="text-2xl font-bold text-slate-900">Page Title</h1>

{{-- Section heading --}}
<h2 class="text-lg font-semibold text-slate-800">Section Heading</h2>

{{-- Card title --}}
<h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Card Title</h3>

{{-- Body text --}}
<p class="text-sm text-slate-700">Body text content.</p>

{{-- Muted/helper text --}}
<p class="text-xs text-slate-500">Helper or secondary text.</p>

{{-- Large number/stat --}}
<p class="text-2xl font-bold text-emerald-700">Rp 1.250.000</p>


{{-- =========================================================================
4. LAYOUT STANDARDS
==========================================================================
- Page background: bg-slate-50 (set on body or outer wrapper)
- Content max width: max-w-7xl mx-auto
- Page padding: px-4 sm:px-6 lg:px-8 py-8
- Section spacing: space-y-6
========================================================================= --}}

{{-- Page container --}}
<div class="min-h-screen bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        {{-- page content here --}}
    </div>
</div>


{{-- =========================================================================
5. CARD STYLES
==========================================================================
Use cards to group related content.
Always: white bg, rounded-xl, soft shadow, p-6.
========================================================================= --}}

{{-- Standard card --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Card Title</h3>
    <p class="text-sm text-slate-600">Card content goes here.</p>
</div>

{{-- Dashboard stat card --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <p class="text-xs font-medium text-slate-500 uppercase">Total Pemasukan</p>
    <p class="text-2xl font-bold text-emerald-700 mt-1">Rp 1.250.000</p>
</div>

{{-- Colored stat card (green accent) --}}
<div class="bg-emerald-50 rounded-xl border border-emerald-200 p-6">
    <p class="text-xs font-medium text-emerald-600 uppercase">Saldo Kas</p>
    <p class="text-2xl font-bold text-emerald-800 mt-1">Rp 3.500.000</p>
</div>

{{-- Danger stat card --}}
<div class="bg-red-50 rounded-xl border border-red-200 p-6">
    <p class="text-xs font-medium text-red-600 uppercase">Total Pengeluaran</p>
    <p class="text-2xl font-bold text-red-800 mt-1">Rp 750.000</p>
</div>


{{-- =========================================================================
6. BUTTON STYLES
========================================================================= --}}

{{-- Primary button — use for main actions (Simpan, Tambah) --}}
<button class="bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 transition">
    Simpan
</button>

{{-- Secondary button — use for cancel, back, less important actions --}}
<button class="bg-slate-100 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 transition">
    Batal
</button>

{{-- Danger button — use for delete actions --}}
<button class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">
    Hapus
</button>

{{-- Ghost/link button — use for inline actions --}}
<button class="text-emerald-700 text-sm font-medium hover:underline">
    Lihat Detail
</button>

{{-- Button with icon (example) --}}
<a href="#" class="inline-flex items-center gap-2 bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 transition">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
    Tambah Data
</a>


{{-- =========================================================================
7. INPUT STYLES
==========================================================================
Use on all form inputs, selects, textareas.
========================================================================= --}}

{{-- Text input --}}
<input type="text" class="w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Nama">

{{-- Select --}}
<select class="w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
    <option>-- Pilih --</option>
</select>

{{-- Textarea --}}
<textarea class="w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500" rows="3"></textarea>

{{-- Label --}}
<label class="block text-sm font-medium text-slate-700 mb-1">Label</label>

{{-- Error message --}}
<p class="mt-1 text-xs text-red-600">Pesan error validasi.</p>

{{-- Form group pattern --}}
<div class="space-y-1">
    <label class="block text-sm font-medium text-slate-700">Nama Warga</label>
    <input type="text" class="w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
</div>


{{-- =========================================================================
8. TABLE STYLES
==========================================================================
Use for data listings (iuran, pengeluaran, buku kas).
Wrap in card. Use rounded-xl on wrapper.
========================================================================= --}}

{{-- Table wrapper --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Tanggal</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Keterangan</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Jumlah</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <tr class="hover:bg-slate-50 transition">
                <td class="px-6 py-4 text-sm text-slate-900">17/05/2026</td>
                <td class="px-6 py-4 text-sm text-slate-700">Iuran bulanan - Budi</td>
                <td class="px-6 py-4 text-sm text-right font-medium text-emerald-700">Rp 50.000</td>
            </tr>
        </tbody>
    </table>
</div>


{{-- =========================================================================
9. BADGE STYLES
========================================================================= --}}

{{-- Success badge --}}
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Aktif</span>

{{-- Danger badge --}}
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Nonaktif</span>

{{-- Warning badge --}}
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Pending</span>

{{-- Neutral badge --}}
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">Info</span>


{{-- =========================================================================
10. SIDEBAR STYLES
==========================================================================
Use if/when switching to sidebar layout in the future.
========================================================================= --}}

{{-- Sidebar container --}}
<aside class="w-64 bg-emerald-950 min-h-screen p-4">
    {{-- Sidebar brand --}}
    <div class="text-white text-lg font-bold mb-8 px-2">Ecobank026</div>

    {{-- Sidebar nav --}}
    <nav class="space-y-1">
        {{-- Active item --}}
        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-emerald-800 text-white text-sm font-medium">
            Dashboard
        </a>
        {{-- Inactive item --}}
        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-emerald-200 hover:bg-emerald-800 hover:text-white text-sm transition">
            Kategori Dana
        </a>
    </nav>
</aside>


{{-- =========================================================================
11. NAVBAR STYLES
==========================================================================
Current app uses top navbar. Keep it clean.
========================================================================= --}}

{{-- Navbar --}}
<nav class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <a href="#" class="text-lg font-bold text-emerald-700">Ecobank026</a>
        {{-- Nav links --}}
        <div class="flex items-center gap-6">
            <a href="#" class="text-sm font-medium text-slate-700 hover:text-emerald-700 transition">Dashboard</a>
            <a href="#" class="text-sm font-medium text-emerald-700 border-b-2 border-emerald-700 pb-0.5">Buku Kas</a>
        </div>
    </div>
</nav>


{{-- =========================================================================
12. DASHBOARD WIDGET STYLES
==========================================================================
Use grid for stat cards. 3 columns on desktop, 1 on mobile.
========================================================================= --}}

{{-- Stats grid --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <p class="text-xs font-medium text-slate-500 uppercase">Total Pemasukan</p>
        <p class="text-2xl font-bold text-emerald-700 mt-1">Rp 1.250.000</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <p class="text-xs font-medium text-slate-500 uppercase">Total Pengeluaran</p>
        <p class="text-2xl font-bold text-red-700 mt-1">Rp 750.000</p>
    </div>
    <div class="bg-emerald-50 rounded-xl border border-emerald-200 p-6">
        <p class="text-xs font-medium text-emerald-600 uppercase">Saldo Kas</p>
        <p class="text-2xl font-bold text-emerald-800 mt-1">Rp 500.000</p>
    </div>
</div>

{{-- Quick action links --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Menu Cepat</h3>
    <div class="flex flex-wrap gap-3">
        <a href="#" class="inline-flex items-center gap-2 bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 transition">+ Catat Iuran</a>
        <a href="#" class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">+ Pengeluaran</a>
        <a href="#" class="inline-flex items-center gap-2 bg-slate-100 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 transition">Buku Kas</a>
    </div>
</div>


{{-- =========================================================================
13. EMPTY STATE STYLES
==========================================================================
Use when a table or list has no data.
========================================================================= --}}

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
    <p class="text-slate-400 text-sm">Belum ada data.</p>
    <a href="#" class="mt-4 inline-flex items-center gap-2 bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 transition">
        + Tambah Data Pertama
    </a>
</div>


{{-- =========================================================================
14. RESPONSIVE RULES
==========================================================================
- Grid: grid-cols-1 md:grid-cols-2 lg:grid-cols-3
- Stats: grid-cols-1 md:grid-cols-3
- Tables: overflow-x-auto on mobile
- Padding: px-4 sm:px-6 lg:px-8
- Font sizes stay consistent (text-sm for body)
- Hide less important columns on mobile with hidden sm:table-cell
========================================================================= --}}

{{-- Responsive table wrapper --}}
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200">
        {{-- ... --}}
    </table>
</div>


{{-- =========================================================================
15. EXAMPLE BLADE SNIPPETS
==========================================================================
Copy-paste ready patterns for new pages.
========================================================================= --}}

{{-- Full page layout example --}}
{{--
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-900">Page Title</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <p class="text-xs font-medium text-slate-500 uppercase">Label</p>
                    <p class="text-2xl font-bold text-emerald-700 mt-1">Value</p>
                </div>
            </div>

            <!-- Content card -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Section</h3>
                <!-- content -->
            </div>

            <!-- Table card -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Col</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 text-sm text-slate-900">Data</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
--}}

{{-- Form page example --}}
{{--
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-900">Tambah Data</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <form method="POST" action="#" class="space-y-4">
                    @csrf

                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-slate-700">Field</label>
                        <input type="text" name="field" class="w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @error('field') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 transition">Simpan</button>
                        <a href="#" class="bg-slate-100 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 transition">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
--}}


{{-- =========================================================================
16. DARK MODE DESIGN STANDARD
==========================================================================
Direction:
- Professional dark eco dashboard
- Not pure black — use dark slate/emerald tones
- Maintain good contrast and readability
- Cards and tables must stay clean
- Green accent remains visible and vibrant
- Forms and inputs must be clearly distinguishable
========================================================================= --}}


{{-- =========================================================================
17. DARK MODE COLOR TOKENS
==========================================================================
Page background:     dark:bg-slate-950
Surface/card:        dark:bg-slate-900
Soft surface:        dark:bg-slate-800
Border:              dark:border-slate-700
Text primary:        dark:text-slate-50
Text secondary:      dark:text-slate-300
Text muted:          dark:text-slate-400
Primary accent:      dark:text-emerald-400
Button background:   dark:bg-emerald-500
Button hover:        dark:hover:bg-emerald-400
Success:             dark:text-emerald-400
Danger:              dark:text-red-400
Warning:             dark:text-amber-400
========================================================================= --}}


{{-- =========================================================================
18. DARK MODE COMPONENT EXAMPLES
==========================================================================
Each example includes both light and dark classes.
========================================================================= --}}

{{-- Page container --}}
<div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        {{-- content --}}
    </div>
</div>

{{-- Card --}}
<div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide mb-4">Title</h3>
    <p class="text-sm text-slate-600 dark:text-slate-300">Content</p>
</div>

{{-- Table wrapper --}}
<div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
        <thead class="bg-slate-50 dark:bg-slate-800">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide">Column</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">Data</td>
            </tr>
        </tbody>
    </table>
</div>

{{-- Navbar --}}
<nav class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <a href="#" class="text-lg font-bold text-emerald-700 dark:text-emerald-400">Ecobank026</a>
        <a href="#" class="text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-emerald-700 dark:hover:text-emerald-400 transition">Link</a>
    </div>
</nav>

{{-- Sidebar --}}
<aside class="w-64 bg-emerald-950 dark:bg-slate-950 min-h-screen p-4">
    <div class="text-white text-lg font-bold mb-8 px-2">Ecobank026</div>
    <nav class="space-y-1">
        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-emerald-800 dark:bg-emerald-900 text-white text-sm font-medium">Active</a>
        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-emerald-200 dark:text-slate-300 hover:bg-emerald-800 dark:hover:bg-slate-800 hover:text-white text-sm transition">Inactive</a>
    </nav>
</aside>

{{-- Input --}}
<input type="text" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:focus:border-emerald-400 dark:focus:ring-emerald-400 placeholder-slate-400 dark:placeholder-slate-500">

{{-- Primary button --}}
<button class="bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">
    Primary
</button>

{{-- Secondary button --}}
<button class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">
    Secondary
</button>

{{-- Badge success --}}
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-300">Aktif</span>

{{-- Badge danger --}}
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-300">Nonaktif</span>


{{-- =========================================================================
19. DARK MODE RULES
==========================================================================
- Every new page MUST include dark: classes on all color utilities.
- Do NOT use pure black (bg-black) except for overlays/modals.
- Avoid low-contrast gray text on dark backgrounds (e.g., text-slate-600 on dark).
  Use dark:text-slate-300 or dark:text-slate-400 minimum.
- Use emerald accent consistently (dark:text-emerald-400, dark:bg-emerald-500).
- Test both light and dark mode before shipping.
- Never hardcode text colors without a dark: variant.
- Borders: always pair border-slate-200 with dark:border-slate-700.
- Backgrounds: always pair bg-white with dark:bg-slate-900.
========================================================================= --}}


{{-- =========================================================================
20. ANIMATION STANDARD
==========================================================================
Tools allowed:
- Tailwind transition/transform classes
- Alpine.js x-transition, x-show, x-intersect

Rules:
- Use subtle fade-up for page sections on scroll.
- Use hover lift (hover:-translate-y-1) for cards.
- Use hover shadow (hover:shadow-lg) for buttons and cards.
- Use duration-200 for small interactions (hover, focus).
- Use duration-500 or duration-700 for page entrance animations.
- Do NOT use heavy animation libraries (GSAP, Framer, etc.) for MVP.
- Animation must be optional — content visible without JS.
- Keep animations lightweight and professional.
========================================================================= --}}

{{-- Fade-up section (triggered on scroll) --}}
<div x-data="{ show: false }" x-intersect.once="show = true">
    <div x-show="show"
         x-transition:enter="transition ease-out duration-700"
         x-transition:enter-start="opacity-0 translate-y-8"
         x-transition:enter-end="opacity-100 translate-y-0">
        {{-- Section content --}}
    </div>
</div>

{{-- Hover card --}}
<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 p-6 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
    {{-- Card content --}}
</div>

{{-- Animated button --}}
<button class="bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
    Action
</button>

{{-- Alpine loaded state (fade-in on mount) --}}
<div x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">
    <div x-show="loaded"
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0">
        {{-- Content that fades in after page load --}}
    </div>
</div>

{{-- Staggered cards (use delay classes) --}}
{{--
x-transition:enter="transition ease-out duration-500 delay-100"
x-transition:enter="transition ease-out duration-500 delay-200"
x-transition:enter="transition ease-out duration-500 delay-300"
--}}


{{-- =========================================================================
21. MVP UI REMINDER
==========================================================================
- Keep UI simple. Do not over-design.
- Prioritize clarity and data readability over decoration.
- Avoid over-engineering components for MVP.
- Use this design system consistently across all pages.
- When in doubt, use a white card with emerald accent.
- Ship clean, iterate later.
========================================================================= --}}
