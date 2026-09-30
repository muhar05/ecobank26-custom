# EcoBank026 Design System

> **Status:** Draft v1.0 — based on the design decisions agreed during the UI/UX workshop.
>
> **Product:** EcoBank026 / Bank Sampah
>
> **Core direction:** Modern Operational × Warm Human

---

## 1. Design Vision

EcoBank026 should feel:

- Friendly
- Modern
- Easy to use for all ages
- Readable
- Operationally efficient
- Familiar without looking generic
- Consistent across desktop and mobile

The interface should not feel like a generic admin dashboard template. It should have a recognizable visual language while keeping the interface practical for daily operational work.

### Core UX motto

> **One Click**

The user should be able to understand what to do next with minimal friction.

---

## 2. Core UX Principles

### 2.1 Progressive Familiarity

The interface should be immediately understandable to a first-time user while becoming faster for frequent users.

Do not expose unnecessary complexity before it is needed.

### 2.2 Contextual Simplicity

Navigation, actions, search, notifications, feedback, spacing, and visual density may change according to context.

Do not force every page into the same layout pattern.

### 2.3 Balanced Density

The product is an operational system, so information density is important.

However:

> Readability and operational speed must coexist.

Avoid both excessive whitespace and cramped interfaces.

### 2.4 Readability First

- Body text baseline: **16px or larger**
- Strong contrast
- Clear labels
- Clear hierarchy
- Avoid tiny metadata that contains important information
- Never rely on color alone to communicate meaning

---

# 3. Visual Personality

## 3.1 Overall Character

**Modern Operational × Warm Human**

The visual language should combine:

- Modern product UI
- Warm earthy identity
- Soft shapes
- Operational clarity
- Friendly microcopy
- Subtle motion
- Purposeful visual elements

## 3.2 Color Direction

Use an **earthy palette**:

- Green
- Cream / beige
- Dark brown / charcoal

The brand should act as a **visual language**, not as a full-page canvas.

Prefer a neutral foundation with brand colors appearing consistently in:

- Primary actions
- Active states
- Highlights
- Important information
- Status/context where appropriate

Do not make every surface brand-colored.

### Light + Dark Mode

Both light and dark themes must be considered together.

Dark mode should preserve:

- Readability
- Contrast
- Semantic meaning
- Brand identity

Do not simply invert colors.

---

# 4. Shape, Surface & Depth

## 4.1 Shape

Use **slightly rounded / soft** shapes.

Avoid:

- Excessively sharp interfaces
- Excessive pill-shaped controls
- Overly playful rounded UI

## 4.2 Borders & Shadows

Use a combination of:

- Subtle borders
- Very light shadows

Avoid heavy shadows and excessive elevation.

## 4.3 Cards

Cards are **hybrid**:

- Important information → clear card/surface
- Supporting information → may remain flat

Do not turn every section into a card.

## 4.4 Importance × Frequency

Visual prominence should reflect:

> **Importance × Frequency**

Important and frequently used actions/information can receive:

- Larger size
- Stronger contrast
- More prominent placement

Secondary information should not compete visually.

---

# 5. Typography

## 5.1 Personality

Typography should feel:

> **Modern + Friendly**

## 5.2 Size

Use a large, accessible baseline.

- Body: **16px+**
- Important operational information should not be smaller than necessary.

## 5.3 Hierarchy

Use **moderate hierarchy**.

Avoid huge typography jumps between heading levels.

## 5.4 Labels

Form labels are always positioned **above the field**.

Do not use floating labels as the default pattern.

## 5.5 Required Fields

Required fields should explicitly say:

> **Wajib diisi**

Do not depend exclusively on `*`.

---

# 6. Spacing

Spacing is **contextual**.

### Operational contexts

Examples:

- Tables
- Forms
- Data entry
- Dense workflows

Use more compact spacing to support speed.

### Exploratory / overview contexts

Examples:

- Dashboard
- Detail pages
- Empty states
- Onboarding

Use more generous spacing.

The goal is always balanced readability.

---

# 7. Buttons

## 7.1 Primary

Primary buttons use a **strong visual hierarchy**.

The main action must be immediately recognizable.

Examples:

- Simpan
- Simpan & Lanjut
- Import
- Tambah

## 7.2 Secondary

Secondary button styling is **contextual**.

Possible treatments:

- Outline
- Ghost
- Soft background

Choose the treatment based on context rather than forcing one global secondary style.

## 7.3 Shape

Buttons use **slightly rounded rectangles**.

Do not use pill buttons as the default.

## 7.4 Size

Default button size:

> **Medium**

Important/high-frequency actions may become larger when appropriate.

## 7.5 Interaction

Desktop hover states may use:

- Background change
- Border change
- Subtle movement/elevation

Interaction must remain restrained.

---

# 8. Inputs & Forms

## 8.1 Input Style

Use a **hybrid input system**:

- Default → subtle
- Focus → clearly visible state
- Error → clearly visible state
- Interaction → state should be obvious

## 8.2 Focus

The default focus treatment is:

> **Border color change**

Do not create unnecessarily strong focus rings unless required for accessibility/context.

## 8.3 Form Structure

- Label above field
- Clear helper text when necessary
- Clear error near the source
- Required fields explicitly marked `Wajib diisi`

Forms should optimize for completing the task rather than decorative presentation.

---

# 9. Tables

Tables are **dense operational interfaces**.

Use them for:

- Setoran
- Penjualan
- Nasabah
- Harga
- Laporan
- Historical data

Rules:

- Show useful information without unnecessary columns
- Maintain readable row height
- Make primary information visually stronger
- Keep actions discoverable
- Avoid excessive borders
- Preserve scanning speed

### Mobile

Tables should become **adaptive / stacked** rather than forcing horizontal desktop tables everywhere.

---

# 10. Cards & Information Blocks

Use cards selectively.

A card should answer at least one of these:

- What is important here?
- What action belongs here?
- What information needs visual grouping?

If a section does not need grouping, prefer a flat surface.

---

# 11. Modal & Dialog

Modal behavior is **adaptive**.

### Simple task

Use compact dialogs.

Examples:

- Simple confirmation
- Small edit
- Quick Add

### Medium task

Use a medium dialog.

Examples:

- Material mapping
- Multi-field forms

### Complex task

Use full-screen or page-like interaction when the task becomes too complex for a modal, especially on mobile.

---

# 12. Status System

Status should use:

> **Badge + Icon + Text**

Never communicate an important state through color alone.

Examples:

- ✓ Berhasil
- ! Perlu Mapping
- • Diproses
- × Error
- ✓ Aktif
- × Dicabut

The exact icon and semantic color should follow the state meaning.

---

# 13. Feedback System

Feedback is **contextual**.

### Save / successful mutation

Use:

> **Toast + immediate UI update**

Example:

`Setoran berhasil disimpan.`

The UI should also reflect the new state immediately.

### Validation

Use inline feedback close to the affected field.

### Background / async operation

Use a contextual loading state appropriate to the operation.

---

# 14. Loading System

Loading behavior is **contextual**.

Examples:

- Save button → button loading + spinner
- Dashboard data → skeleton
- Excel import → progress/status state
- Large operation → explicit processing state

Do not use one loading pattern everywhere.

---

# 15. Error System

Errors are **contextual**.

Examples:

- Field validation → inline
- Failed save → toast
- Page/data failure → page-level error state
- Import issue → row/section-specific error where possible

Every important error should explain:

1. What happened
2. What the user can do next

---

# 16. Empty States

Empty states use:

> **Purposeful visual + explanation + action**

Example:

> Belum ada setoran hari ini  
> Mulai catat setoran nasabah untuk melihat aktivitas di sini.  
> **+ Catat Setoran**

Do not use decorative illustrations without a clear relationship to the task.

---

# 17. Destructive Actions

Destructive actions are:

> **Red / clearly destructive + confirmation**

Use this for high-risk actions such as:

- Delete important data
- Revoke device
- Irreversible operations

Do not require confirmation for every harmless action.

---

# 18. Navigation

## 18.1 Navigation Model

Navigation is:

> **Hybrid + contextual**

Keep the main navigation familiar while allowing contextual navigation/actions inside pages.

## 18.2 Sidebar

Desktop sidebar:

- Persistent
- Collapsible / expandable

The sidebar should not dominate the interface.

## 18.3 Header

Header is:

> **Contextual + Action**

It can contain:

- Breadcrumb/context
- Page title
- Primary page action

## 18.4 Search

Search is:

> **Hybrid global + contextual**

Use global search only where it genuinely helps.

Within operational pages, contextual search should prioritize the relevant dataset.

## 18.5 User Profile

Use:

> **Avatar + Name**

## 18.6 Notifications

Notifications are:

> **Contextual**

Do not introduce notification UI just for the sake of having a notification system.

---

# 19. Dashboard

Dashboard is **action-first**.

Quick actions should be immediately visible.

Overview/statistics provide context around those actions.

Avoid dashboards that are primarily decorative analytics screens.

The dashboard should answer:

1. What can I do now?
2. What needs attention?
3. What happened recently?

---

# 20. Data Visualization

Charts are **contextual**.

Choose the visualization based on the data and task.

Do not add charts merely because the page is a dashboard.

Every visualization should have an operational purpose.

---

# 21. Motion

Motion is:

> **Subtle**

Allowed:

- Subtle hover transitions
- Small state transitions
- Subtle page fade
- Feedback animations

Avoid:

- Large decorative animations
- Excessive parallax
- Long transitions
- Motion that delays work

Page transitions default to:

> **Subtle fade**

---

# 22. Responsive Design

Responsive behavior is first-class.

## Desktop

- Sidebar available
- Dense operational tables
- Full workflows

## Mobile

- Hamburger navigation
- Contextual drawer
- Adaptive/stacked data
- Contextual sticky actions only when needed

The web application should remain a web application on mobile. Do not redesign it into a mobile-app imitation.

---

# 23. Mobile Rules

### Navigation

> Hamburger + contextual drawer

### Tables

> Adaptive / stacked

### Actions

Use sticky actions only when they materially improve task completion.

### Setoran

The user should be able to complete the main deposit workflow without fighting the mobile layout.

---

# 24. Setoran — Reference Workflow

Setoran is the reference implementation for the EcoBank UX system.

The workflow is:

```text
Cari Nasabah
    ↓
Pilih Nasabah
    ↓
Auto-focus Material
    ↓
Pilih Material
    ↓
Masukkan Berat
    ↓
Harga Master otomatis
    ↓
Optional manual override
    ↓
Total otomatis
    ↓
Tambah Material
    ↓
Total Setoran
    ↓
Simpan
    ↓
Success
    ↓
Ready untuk nasabah berikutnya
```

The design should optimize for repeated daily entry.

## 24.1 Customer Selection

Use a search-centered workflow.

Empty state:

- Show recent customers
- Allow search
- Typing should transition naturally into results

After selecting a customer:

> Auto-focus the material input.

## 24.2 Material Rows

Use a hybrid table-like input:

- Clear row separation
- Material
- Weight
- Price
- Subtotal
- Row actions

## 24.3 Total

Total appears after the material list rather than being permanently sticky.

## 24.4 Save

After successful save:

1. Show success feedback
2. Update relevant UI
3. Return the operator to a ready state for the next customer

This is important because operators may process many customers in one day.

## 24.5 Price

Master price is used by default.

Manual override is allowed.

If transaction price differs from master price, make that difference visible.

Example:

> `Manual · Master Rp2.500`

## 24.6 Row Actions

Desktop:

- Trash action is always visible

Mobile:

- Swipe may be used when appropriate

Editing a row:

> Click row → edit mode

## 24.7 Keyboard

Keyboard flow is a **nice-to-have**, not a mandatory requirement.

---

# 25. Material Resolution

Material resolution follows the canonical material system.

The application has:

> **Canonical Master Material**

External sources may use different names.

Examples:

- `Kertas`
- `Kertas Bekas`
- `Kertas Jelek`
- `Kertas Kusut`

These labels must resolve to the correct canonical material instead of creating duplicates.

## Resolution priority

```text
Existing Master Material
        ↓
Existing scoped Alias
        ↓
Candidate suggestion
        ↓
Manual Mapping
        ↓
Explicit Quick Add
```

### Mapping

Mapping means:

> An external label refers to an existing canonical material.

No new master material is created.

### Add

Add means:

> The material genuinely does not exist in the master.

Creation must be explicit.

### Rule

> **Never automatically create a new material from an unknown external label.**

The user/operator remains the final verifier.

---

# 26. Material Price Behavior

Material aliases contain naming relationships, not prices.

Prices remain separate from alias mapping.

Historical transactions must remain stable through transaction-level snapshots.

Changing a master price or alias must not rewrite historical financial data.

---

# 27. Microcopy

UI language is:

> **Casual**

The interface should feel human and direct.

Prefer:

- `Setoran berhasil disimpan.`
- `Belum ada setoran hari ini.`
- `Perlu Mapping`
- `Tambah Material`

Avoid overly bureaucratic wording where a shorter human phrase is clearer.

However, financial values, destructive actions, and important warnings must remain unambiguous.

---

# 28. Accessibility & Readability

Minimum expectations:

- Body text 16px+
- Strong contrast
- State not communicated by color alone
- Clear focus state
- Labels above fields
- Explicit required-field wording
- Touch targets should be comfortably usable
- Important actions must remain visually distinguishable

Accessibility is part of the operational design, not a later polish step.

---

# 29. Core Design Tokens

The implementation should use **core design tokens** rather than arbitrary values scattered through components.

Core token categories:

- Color
- Typography
- Spacing
- Radius
- Shadow
- Breakpoints
- Motion

Do not over-engineer a token system before it is useful.

The exact values should be finalized during implementation after the existing UI is audited.

---

# 30. Agent Implementation Rules

This document is a **guided design system**, not a rigid component generator.

Agents implementing UI should:

1. Read this document before changing UI.
2. Preserve existing business logic unless the task explicitly changes it.
3. Prefer existing project components/tokens when they already satisfy these rules.
4. Reuse established interaction patterns.
5. Follow contextual rules instead of blindly applying one pattern everywhere.
6. Keep readability and operational speed as primary constraints.
7. Avoid introducing new visual patterns without a clear reason.
8. Do not turn every section into a card.
9. Do not introduce excessive animations.
10. Do not use tiny text to fit more information.
11. Do not create new materials automatically.
12. Do not change historical financial snapshots through UI refactors.
13. Test desktop and mobile behavior.
14. Verify loading, empty, error, success, and destructive states.
15. Run the Anti-Slop guardrails before considering a UI task complete.

### Agent freedom

The design system is:

> **Guided**

Agents may make contextual design decisions where this document does not prescribe a specific implementation, provided the result does not violate the established principles or visual language.

---

# 31. Anti-Slop Guardrails

The UI should not drift into generic AI-generated dashboard patterns.

Avoid:

- Random gradients
- Excessive glassmorphism
- Excessive rounded cards
- Pill buttons everywhere
- Huge decorative headings
- Tiny metadata text
- Low-contrast gray-on-gray interfaces
- Excessive icons without purpose
- Decorative charts
- Excessive whitespace in operational workflows
- Excessive density that hurts readability
- Generic dashboard layouts copied without contextual reasoning
- Animations that do not improve understanding
- Different visual conventions for the same interaction

Every new visual element should answer:

> **Why does this exist?**

Every new component pattern should answer:

> **Why is the existing pattern insufficient?**

---

# 32. Reference Design Philosophy

When making a new page, start from:

1. User goal
2. Most frequent action
3. Most important information
4. Required states
5. Desktop behavior
6. Mobile behavior
7. Existing EcoBank patterns

Then decide the visual composition.

Do not start with:

> “What dashboard layout looks cool?”

---

# 33. Implementation Order

The recommended redesign sequence is:

### Phase 1 — Foundation

- Design tokens
- Typography
- Colors
- Buttons
- Inputs
- Status
- Cards
- Tables
- Modal
- Feedback
- Loading
- Error/empty states

### Phase 2 — Reference Workflow

Redesign:

> **Setoran**

Use it to validate the entire system.

### Phase 3 — Operational Pages

Apply the validated system to:

- Nasabah
- Penjualan
- Agregator
- Harga
- Import
- Laporan

### Phase 4 — Overview

Refine:

- Dashboard
- Navigation
- Header
- Contextual actions

### Phase 5 — Responsive & Polish

Audit:

- Mobile
- Tablet
- Desktop
- Accessibility
- State handling
- Motion
- Anti-Slop compliance

---

# 34. Definition of Done — UI

A UI task is not complete merely because the page renders.

Before completion, verify:

- [ ] Matches EcoBank visual language
- [ ] Primary action is obvious
- [ ] Typography is readable
- [ ] Important states are visible
- [ ] Loading state exists where needed
- [ ] Empty state exists where needed
- [ ] Error state exists where needed
- [ ] Destructive action is protected
- [ ] Desktop works
- [ ] Mobile works
- [ ] No unnecessary visual patterns were introduced
- [ ] Existing business behavior is preserved
- [ ] Anti-Slop guardrails pass

---

# 35. Final Principle

EcoBank026 should feel like a system built for real people doing real operational work.

It should be:

> **Simple enough to understand.  
> Fast enough to operate.  
> Warm enough to feel human.  
> Structured enough to stay reliable.**

The design should reduce cognitive load without reducing capability.
