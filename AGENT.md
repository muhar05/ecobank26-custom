# AGENT.md

# ECOBANK026

Modern SaaS platform for:
- Kas RT/RW management
- Community financial transparency
- Bank Sampah operations
- Resident savings management
- Waste transaction management

Tech stack:
- Laravel
- Blade
- TailwindCSS
- Alpine.js
- ApexCharts

---

# CORE PRINCIPLES

The application must feel:
- modern
- minimalist
- operationally efficient
- community-focused
- trustworthy
- fintech-inspired
- not like a generic admin template

Always preserve:
- existing business logic
- routes
- data structure
- validation rules
- financial calculations

---

# UI / UX STYLE GUIDE

## Visual Style

Use:
- modern SaaS dashboard style
- eco-green accent
- soft neutral backgrounds
- rounded modern cards
- subtle shadows
- clean typography
- spacious layouts
- smooth interactions

Avoid:
- overly colorful UI
- crowded layouts
- generic admin template look
- excessive borders
- sharp corners
- inconsistent spacing

---

# DESIGN SYSTEM

## Border Radius

Use consistently:
- cards: rounded-2xl
- inputs: rounded-xl
- buttons: rounded-lg
- modals: rounded-3xl

---

## Shadows

Use soft shadows only.

Preferred:
- shadow-sm
- shadow-md

Avoid:
- heavy/glowing shadows

---

## Colors

Primary:
- emerald / eco green

Neutral:
- zinc/slate gray palette

Use:
- green for positive
- red for negative
- amber for warning

Avoid:
- random color combinations

---

## Typography

Hierarchy:
- page title: bold
- section title: semibold
- labels: medium
- helper text: muted
- table text: clean and compact

Avoid:
- oversized typography
- excessive uppercase text

---

# LAYOUT RULES

## Sidebar

- sidebar width must remain consistent across all roles
- maintain spacing consistency
- preserve active menu style
- responsive drawer on mobile

---

## Content Layout

Use:
- centered content
- spacious section spacing
- proper visual hierarchy
- balanced white space

Avoid:
- cramped tables/forms
- uneven spacing

---

# TABLE RULES

Every table page must include:
- search
- filter
- sorting
- pagination
- loading state
- empty state
- responsive mobile layout

Preferred table style:
- rounded container
- subtle hover rows
- sticky header optional
- soft dividers
- responsive overflow

Mobile:
- convert table into stacked cards if needed

---

# FORM RULES

Forms must:
- feel clean and premium
- use centered layout
- use modern spacing
- preserve simplicity

Include:
- loading submit state
- validation state
- helper text
- responsive spacing

Do NOT:
- add random new fields
- overcomplicate forms

---

# MODAL RULES

Use:
- centered modern modal
- soft overlay
- rounded-3xl
- loading delete state

Delete modals must:
- clearly explain impact
- use subtle danger styling

---

# DARK MODE

Dark mode must:
- maintain readable contrast
- avoid pure black backgrounds
- keep financial numbers readable
- use softer dark surfaces

---

# RESPONSIVE RULES

Desktop:
- spacious layout
- consistent sidebar

Mobile:
- stacked layouts
- touch-friendly spacing
- no horizontal overflow
- responsive cards/tables

---

# COMPONENT RULES

Prefer reusable Blade components.

Reusable components include:
- summary cards
- table toolbar
- status badges
- modern tables
- action buttons
- modals
- skeleton loaders

Avoid duplicated UI patterns.

---

# LOADING & EMPTY STATES

All major pages should include:
- loading state
- skeleton loading
- empty state
- search empty state

Avoid blank screens.

---

# BANK SAMPAH BUSINESS RULES

## Deposits
- deposits increase member savings balance

## Withdrawals
- withdrawals reduce savings balance
- balance must be sufficient
- minimum deposit rules may apply

## Sales
- sales add margin to waste bank cash ledger
- only margin affects bank cash

## Waste Prices
- dual pricing system:
  - member_price
  - collector_price

---

# COMMUNITY CASH RULES

## Contributions
- contributions increase category balance

## Expenses
- expenses reduce category balance
- insufficient balance must be blocked

## Reports
- reports must remain transparent and readable

---

# MEMBER RULES

Members:
- may be RT residents
- may be bank sampah customers
- may be both

Phone number should help prevent duplicates.

---

# DO NOT

Do NOT:
- redesign business flow unnecessarily
- add random fields
- break routes
- change financial logic
- remove validations
- overdesign UI
- make UI overly colorful
- create inconsistent layouts
- expose raw Blade/PHP syntax in UI

---

# CODE STYLE

Use:
- clean Blade structure
- Tailwind utility consistency
- reusable components
- readable spacing
- semantic naming

Avoid:
- inline messy styling
- duplicated code
- hardcoded repeated UI

---

# QA CHECKLIST

Before finishing any task:
- test responsive layout
- test dark mode
- test loading states
- test empty states
- test pagination
- test filters/search
- ensure no Blade syntax leaks into UI
- ensure spacing consistency
- ensure sidebar consistency
- preserve business logic

---

# PRIMARY GOAL

ECOBANK026 should feel like:
- a real modern product
- clean and trustworthy
- operationally efficient
- minimalist but premium
- designed specifically for RT/RW and Bank Sampah workflows