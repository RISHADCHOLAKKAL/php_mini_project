// store.js — thin API layer (no localStorage, no hardcoded data)

const API = 'api.php';

function getMenuItems(cb) {
  fetch(API + '?action=menu').then(r => r.json()).then(cb);
}

function saveMenuItem(item, cb) {
  fetch(API, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'save_item', item }) }).then(r => r.json()).then(cb);
}

function getSalesHistory(cb) {
  fetch(API + '?action=sales').then(r => r.json()).then(cb);
}

function recordSale(order, cb) {
  fetch(API, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'record_sale', order }) }).then(r => r.json()).then(cb);
}
