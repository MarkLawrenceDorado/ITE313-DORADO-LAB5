/* ==========================================================
   dashboard-realtime.js
   Polls 3 JSON API endpoints every 30 seconds and updates the
   dashboard in place, with no full page reload.
   ========================================================== */

// Capture the route URLs now, while document.currentScript is still
// valid (this runs synchronously even with the `defer` attribute).
// These get closed over by the functions below, which run later.
const STATS_URL = document.currentScript.dataset.statsUrl;
const LOW_STOCK_URL = document.currentScript.dataset.lowStockUrl;
const RECENT_TRANSACTIONS_URL = document.currentScript.dataset.recentTransactionsUrl;

const POLL_INTERVAL_MS = 30000; // 30 seconds

document.addEventListener('DOMContentLoaded', function () {
    refreshAll(); // run once immediately so the page doesn't sit empty for 30s
    setInterval(refreshAll, POLL_INTERVAL_MS);
});

function refreshAll() {
    refreshStats();
    refreshLowStock();
    refreshRecentTransactions();
    updateLastUpdatedLabel();
}

function updateLastUpdatedLabel() {
    const el = document.getElementById('lastUpdated');
    if (el) el.textContent = 'just now';
}

function refreshStats() {
    fetch(STATS_URL)
        .then(res => {
            if (!res.ok) throw new Error(`Stats API returned ${res.status}`);
            return res.json();
        })
        .then(data => {
            setText('statTotalProducts', data.total_products);
            setText('statInventoryValue', formatCurrency(data.total_inventory_value));
            setText('statLowStock', data.low_stock_count);
            setText('statOutOfStock', data.out_of_stock_count);
        })
        .catch(err => {
            // Fail quietly: the dashboard keeps showing its last-known
            // values rather than breaking the page over a network blip.
            console.warn('[dashboard-realtime] Could not refresh stats:', err.message);
        });
}

function refreshLowStock() {
    fetch(LOW_STOCK_URL)
        .then(res => {
            if (!res.ok) throw new Error(`Low-stock API returned ${res.status}`);
            return res.json();
        })
        .then(data => {
            const tbody = document.getElementById('lowStockTableBody');
            if (!tbody) return;

            if (data.products.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-3">Everything is well stocked. 🎉</td></tr>`;
                return;
            }

            tbody.innerHTML = data.products.map(p => `
                <tr class="table-${p.status_color}">
                    <td><a href="${p.url}">${escapeHtml(p.name)}</a></td>
                    <td>${p.quantity}</td>
                    <td>${p.reorder_level}</td>
                    <td><span class="badge bg-${p.status_color}">${escapeHtml(p.status)}</span></td>
                </tr>
            `).join('');
        })
        .catch(err => {
            console.warn('[dashboard-realtime] Could not refresh low-stock list:', err.message);
        });
}

function refreshRecentTransactions() {
    fetch(RECENT_TRANSACTIONS_URL)
        .then(res => {
            if (!res.ok) throw new Error(`Transactions API returned ${res.status}`);
            return res.json();
        })
        .then(data => {
            const tbody = document.getElementById('recentTransactionsBody');
            if (!tbody) return;

            if (data.transactions.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-3">No transactions recorded yet.</td></tr>`;
                return;
            }

            tbody.innerHTML = data.transactions.map(t => {
                const sign = t.quantity_changed > 0 ? '+' : '';
                const qtyClass = t.quantity_changed > 0 ? 'text-success' : 'text-danger';
                return `
                    <tr>
                        <td><a href="${t.url}">${escapeHtml(t.product_name)}</a></td>
                        <td><span class="badge bg-${t.type_color}">${escapeHtml(t.type_label)}</span></td>
                        <td class="${qtyClass} fw-bold">${sign}${t.quantity_changed}</td>
                        <td>${escapeHtml(t.user_name)}</td>
                        <td>${escapeHtml(t.created_at)}</td>
                    </tr>
                `;
            }).join('');
        })
        .catch(err => {
            console.warn('[dashboard-realtime] Could not refresh recent transactions:', err.message);
        });
}

function setText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
}

function formatCurrency(value) {
    return '$' + Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Basic HTML-escaping so product/user names containing special
// characters can't break the injected markup.
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}