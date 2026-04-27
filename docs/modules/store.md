# Store

Sell your society's publications, merchandise, and back issues online. Stripe + PayPal checkout (cart-based, not external redirect — buyers stay on your site).

## What you can do

- Sell society-published books from the [Library](library.md) catalog (one toggle on each item).
- Sell non-library merchandise — apparel, lapel pins, maps, anything — from a separate Store Products list.
- Per-item shipping fees (multiplied by quantity at checkout).
- Stock tracking (set a quantity, decrements on purchase, "Sold out" badge when zero).
- Stripe Payment Element (card + Apple Pay + Google Pay + Link, all inline).
- PayPal Smart Buttons (PayPal + Venmo, also inline).
- Per-order shipping address capture and admin order detail with fulfillment status (pending → paid → shipped → completed).

## How to add your first product

Two paths depending on what you're selling:

**Society-published books** (county histories, family genealogies, transcribed records): use the Library. **SocietyPress → Library → [item] → Edit.** In the Store Listing section, set Item Value above 0, set Shipping fee per unit, save. The book appears in the storefront automatically.

**Other merchandise** (T-shirts, pins, maps, anything that isn't a book): **SocietyPress → Store Products → Add New.** Title, SKU, description, price, shipping fee, image URL, store category, optional stock quantity. Save.

The storefront shows both sources merged together — buyers don't know (or care) which source a product came from.

## How to set up a storefront page

**Page builder:** drop the "Store" widget. **Shortcode:** `[sp_store]`. The storefront renders a grid of products with category filters and a search box. Each card has Add to Cart.

You'll also want a cart page. Drop `[sp_cart]` on a `/cart/` page; the cart page handles checkout (Stripe Element + PayPal Smart Buttons appear when the cart has items).

## How shipping fees work

Each product has a per-unit shipping fee. At checkout, line items multiply: `unit_price × qty + shipping_fee × qty`.

Cart total breakdown:

- Subtotal (sum of price × qty per line)
- Shipping (sum of shipping_fee × qty per line, only shown when > 0)
- Total

Buyers see the breakdown live as they adjust quantities. Stripe and PayPal both charge the inclusive total.

This is per-item shipping, not flat-rate or weight-based. If you need flat-rate (e.g., "$8 for any order regardless"), set every product's shipping fee to 0 and add a single fixed-price "Shipping" product the buyer adds to their cart. Hacky but works.

## How orders flow

1. Buyer fills cart, clicks Checkout.
2. Stripe Payment Element or PayPal Smart Button captures payment inline.
3. SocietyPress writes the order in `pending` status with the cart's items.
4. Stripe / PayPal confirms payment via webhook → order flips to `paid`.
5. Stock decrements automatically (only for products with a stock_qty set).
6. You see the order in **SocietyPress → Orders** and ship it. Mark it "shipped" → "completed" as you go.
7. Buyer gets emails at each transition (order received, shipped, etc.).

## How to manage orders

**SocietyPress → Orders.** List with status tabs (All / Pending / Paid / Shipped / Completed / Refunded / Failed). Click an order to see details: customer, items, total, shipping address, payment method, transaction ID.

Per-order actions: mark shipped (with optional tracking number), mark completed, issue a refund (one-click via Stripe / PayPal). Refunds restore stock automatically.

## How stock tracking works

Each product has an optional `stock_qty`. Leave it blank for unlimited (the common case). Set a number to track inventory.

When a product hits stock_qty = 0, the storefront shows "Sold out" instead of Add to Cart. Buyers can't add a sold-out item.

When an order is paid, stock decrements by the purchased quantity. When an order is refunded, stock restores. When an order is failed (payment didn't go through), stock restores too.

## If something looks wrong

**Stripe says "not configured."** **SocietyPress → Settings → Payments**. Drop your publishable + secret keys. Test mode keys work; switch to live when ready.

**PayPal Smart Button doesn't appear.** Check **SocietyPress → Settings → Payments → PayPal**. Both client ID and secret must be filled in. The button is hidden when either is blank.

**Orders are stuck in `pending` after payment.** Webhook isn't reaching your site. Stripe dashboard → Developers → Webhooks → recent attempts shows the failure. Re-copy the signing secret into your settings if it doesn't match.

**Stock isn't decrementing.** Verify the product has stock_qty set (not blank). Blank = unlimited; only set values track stock. If stock IS set and not decrementing, the order may have failed payment — check its status.

**Buyer says they paid but never got an order confirmation email.** **SocietyPress → Settings → Email → Send test email** to verify outbound mail. Buyer's email address is captured during checkout — check the order detail to confirm it's right.

**A library item shows up in the store but shouldn't.** Open the library item, set Item Value to 0 (or blank). Only items with value > 0 appear in the storefront.

## Related guides

- [Library](library.md) — sell society-published books from the catalog
- [Donations](donations.md) — same Stripe path is reused
