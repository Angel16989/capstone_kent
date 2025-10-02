# Capstone Poster & Screenshot Checklist

Purpose: a concise list of screenshots and poster ideas (10 posters) to include in your final Capstone deliverable and presentation. Each poster corresponds to a screenshot (or small set of screenshots). Use the suggested filenames, short captions, and capture tips.

General capture settings
- Format: PNG (for poster image), optionally save PDF for printing (A4, 300 DPI recommended).
- Browser: Chrome or Edge for best "Capture full size".
- Include URL bar and timestamp where possible to prove live demo.
- Use `Ctrl+Shift+P` → "Capture full size screenshot" or DevTools responsive device mode set to A4 size.

Poster 01 — Hero / Homepage (Poster file: `poster-01-homepage.png`)
- Screenshot: Full homepage (hero area + CTA)
- Caption: "L9 Fitness — Clean, modern landing with high-conversion hero"
- Tips: Capture top fold and the chat button at bottom-right

Poster 02 — Membership Plans (Poster file: `poster-02-memberships.png`)
- Screenshot: `public/memberships.php` showing plan cards, prices and CTA
- Caption: "Flexible membership plans — Monthly, Quarterly, Yearly"
- Tips: Show pricing comparison and features list. Include checkout button visible.

Poster 03 — Checkout & Payment Success (Poster file: `poster-03-checkout-success.png`)
- Screenshot: `public/checkout.php` (checkout form) and `public/checkout-success.php` after completion
- Caption: "Secure checkout with clear success confirmation"
- Tips: Capture the filled receipt/invoice on success screen.

Poster 04 — AI Chatbot In Action (Poster file: `poster-04-chatbot.png`)
- Screenshot: Chat widget open + sample friendly AI response. Use `test.html` or `test_chatbot.html`.
- Caption: "AI Assistant — 24/7 smart conversational support"
- Tips: Show both the user message and the AI response; include `ai_powered: true` if from API response.

Poster 05 — Admin Dashboard Metrics (Poster file: `poster-05-admin-dashboard.png`)
- Screenshot: `public/admin.php` showing metrics, charts and key analytics
- Caption: "Admin panel — Live metrics & system overview"
- Tips: Show revenue, bookings, active users region; maximize charts area.

Poster 06 — User Profile & Bookings (Poster file: `poster-06-profile-bookings.png`)
- Screenshot: `public/profile.php` showing booking history & profile details
- Caption: "Member dashboard — Profile, bookings, and membership status"
- Tips: Show an example booking and profile photo if present.

Poster 07 — Class Booking Flow (Poster file: `poster-07-class-booking.png`)
- Screenshot: `public/classes.php` + confirmation modal after booking
- Caption: "Class booking — Reserve a spot with waitlist and confirmation"
- Tips: Show booking modal/pop-up and the booking confirmation message.

Poster 08 — Trainer Profile & Management (Poster file: `poster-08-trainer-profile.png`)
- Screenshot: `public/trainer_dashboard.php` or trainers list + the trainer profile page
- Caption: "Trainer portal — Manage classes, messages and schedules"
- Tips: Also capture the trainer delete / edit form (for poster about data lifecycle).

Poster 09 — Security & Validation (Poster file: `poster-09-security.png`)
- Screenshot: `public/test_csrf.php` or forms showing CSRF tokens, and secure login flow
- Caption: "Built with security — CSRF, session handling and input validation"
- Tips: Show hidden CSRF token input (in devtools) and the login page headers.

Poster 10 — Database Before & After (Poster file: `poster-10-db-before-after.png`)
- Screenshot: phpMyAdmin or CLI view showing the record(s) before and after deletion/deactivation
    - `db-membership-before.png` and `db-membership-after.png`
    - `db-trainer-before.png` and `db-trainer-after.png`
- Caption: "Data lifecycle — Safe deletion and membership non-renewal (audit-friendly)"
- Tips: Take a wide-view of the table rows; include row IDs and timestamps to show change.

Extras / Bonus shots (use if you have space)
- Mobile responsive shots: Homepage on mobile (portrait) — filename `poster-mobile-home.png`
- Chatbot transcript printed to PDF — filename `poster-chatbot-pdf.png`
- Payment/invoice PDF exported — filename `poster-invoice.png`

Naming conventions (for your deliverable folder)
```
posters/
  poster-01-homepage.png
  poster-02-memberships.png
  poster-03-checkout-success.png
  poster-04-chatbot.png
  poster-05-admin-dashboard.png
  poster-06-profile-bookings.png
  poster-07-class-booking.png
  poster-08-trainer-profile.png
  poster-09-security.png
  poster-10-db-before-after.png
```

Recommended poster layout notes (A4 print)
- Portrait orientation for detailed pages (admin, tables)
- Landscape for wide UI shots (homepage, charts)
- Keep consistent margins and a title line at top (72pt for headlines when printing), subtitle for page/context, and footer with URL + date

If you'd like, I can generate a printable A4 PDF mockup template (blank with title + caption placeholders) so you can paste each screenshot and export final posters.

---

Open next: `docs/DEMO_SCRIPT.md` for the speaker script and demo story.
