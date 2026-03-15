{{-- Shared CSS for Booking Detail pages (admin, agency, user) --}}
<style>
/* ===== Layout ===== */
.bd-page { margin: 0; padding: 0; }

/* ===== Header row ===== */
.bd-header {
    display: flex; align-items: center; flex-wrap: wrap;
    gap: 10px; margin-bottom: 18px;
}
.bd-back {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 14px; border-radius: 8px;
    background: #ff4200; color: #fff !important;
    font-weight: 700; font-size: 12px;
    text-decoration: none !important; border: none; cursor: pointer;
    transition: background .2s; white-space: nowrap;
}
.bd-back:hover { background: #e03800; }
.bd-back i { font-size: 11px; }
.bd-code { font-weight: 800; font-size: 15px; color: #1a1a2e; letter-spacing: .2px; }
.bd-badge {
    display: inline-flex; align-items: center;
    padding: 3px 10px; border-radius: 6px;
    font-weight: 700; font-size: 11px; letter-spacing: .2px; text-transform: capitalize;
}
.bd-badge--success { background: #ecfdf5; color: #059669; }
.bd-badge--warning { background: #fffbeb; color: #d97706; }
.bd-badge--danger  { background: #fef2f2; color: #dc2626; }
.bd-badge--info    { background: #eff6ff; color: #2563eb; }
.bd-trip-date {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 6px;
    font-weight: 700; font-size: 11px;
    background: #f8f9fa; color: #6b7280;
}
.bd-trip-date i { font-size: 10px; }
.bd-header-spacer { flex: 1; }

/* ===== Actions ===== */
.bd-actions { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 18px; }
.bd-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 7px 14px; border-radius: 8px;
    font-weight: 700; font-size: 12px;
    text-decoration: none !important;
    border: 1px solid #e5e7eb; background: #fff;
    color: #374151 !important; cursor: pointer;
    transition: all .15s;
}
.bd-btn:hover { border-color: #ff4200; color: #ff4200 !important; }
.bd-btn i { font-size: 12px; }
.bd-btn--primary { background: #ff4200 !important; border-color: #ff4200 !important; color: #fff !important; }
.bd-btn--primary:hover { background: #e03800 !important; }
.bd-btn--success { background: #059669 !important; border-color: #059669 !important; color: #fff !important; }
.bd-btn--success:hover { background: #047857 !important; }
.bd-btn--danger { background: transparent !important; border-color: #dc2626 !important; color: #dc2626 !important; }
.bd-btn--danger:hover { background: #fef2f2 !important; }

/* ===== Summary strip ===== */
.bd-summary {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 0; border-radius: 10px; overflow: hidden;
    border: 1px solid #e5e7eb; margin-bottom: 18px;
}
.bd-summary-item {
    padding: 12px 16px; background: #fff;
    border-right: 1px solid #e5e7eb;
}
.bd-summary-item:last-child { border-right: none; }
.bd-summary-item__label {
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .5px; color: #9ca3af; margin-bottom: 2px;
}
.bd-summary-item__value { font-size: 16px; font-weight: 800; color: #1a1a2e; }

/* ===== Two-column layout ===== */
.bd-grid-2 {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 16px; margin-bottom: 18px;
}
.bd-grid-full { margin-bottom: 18px; }

/* ===== Card ===== */
.bd-card {
    background: #fff; border: 1px solid #e5e7eb;
    border-radius: 10px; overflow: hidden;
}
.bd-card__hdr {
    padding: 10px 16px; border-bottom: 1px solid #f3f4f6;
    font-weight: 800; font-size: 12px; text-transform: uppercase;
    letter-spacing: .4px; color: #6b7280; background: #fafbfc;
}
.bd-card__body { padding: 12px 16px; }

/* ===== Key-value rows ===== */
.bd-kv { display: flex; padding: 5px 0; gap: 8px; font-size: 13px; }
.bd-kv__k { color: #9ca3af; font-weight: 600; min-width: 100px; flex-shrink: 0; }
.bd-kv__v { color: #1a1a2e; font-weight: 700; word-break: break-word; }

/* ===== Chips ===== */
.bd-chips { display: flex; flex-wrap: wrap; gap: 5px; padding-top: 6px; }
.bd-chip {
    padding: 3px 10px; border-radius: 6px;
    border: 1px solid #e5e7eb; background: #fafbfc;
    font-weight: 700; font-size: 11px; color: #6b7280;
}

/* ===== Notes ===== */
.bd-note {
    padding: 10px 16px; border-radius: 8px;
    background: #f9fafb; border: 1px solid #f3f4f6;
    margin-bottom: 10px;
}
.bd-note__label {
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .4px; color: #9ca3af; margin-bottom: 3px;
}
.bd-note__text { font-size: 13px; font-weight: 600; color: #374151; line-height: 1.45; }

/* ===== Price table ===== */
.bd-price-table { width: 100%; border-collapse: collapse; }
.bd-price-table thead th {
    padding: 8px 16px; font-size: 10px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .5px; color: #9ca3af;
    background: #fafbfc; border-bottom: 1px solid #f3f4f6;
}
.bd-price-table tbody td {
    padding: 8px 16px; font-size: 13px; font-weight: 600; color: #374151;
    border-bottom: 1px solid #f9fafb;
}
.bd-price-table tbody td:last-child,
.bd-price-table thead th:last-child { text-align: right; }
.bd-price-table tbody td:last-child { font-weight: 750; color: #1a1a2e; }
.bd-price-table tfoot td {
    padding: 10px 16px; font-weight: 800; font-size: 14px; color: #1a1a2e;
    background: #fafbfc; border-top: 2px solid #e5e7eb;
}
.bd-price-table tfoot td:last-child { text-align: right; }

/* ===== Responsive ===== */
@media (max-width: 768px) {
    .bd-summary { grid-template-columns: 1fr; }
    .bd-summary-item { border-right: none; border-bottom: 1px solid #e5e7eb; }
    .bd-summary-item:last-child { border-bottom: none; }
    .bd-grid-2 { grid-template-columns: 1fr; }
    .bd-header { gap: 8px; }
    .bd-actions { gap: 5px; }
}
@media (max-width: 480px) {
    .bd-page { padding: 0 6px; }
    .bd-btn { padding: 6px 10px; font-size: 11px; }
    .bd-kv { flex-direction: column; gap: 1px; }
    .bd-kv__k { min-width: unset; }
}
</style>
