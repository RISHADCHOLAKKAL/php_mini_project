const API = 'api.php';
const getMenuItems = cb => fetch(`${API}?action=menu`).then(r => r.json()).then(cb);
const saveMenuItem = (item, cb) => fetch(API, {method: 'POST', body: JSON.stringify({action: 'save_item', item})}).then(r => r.json()).then(cb);
const getSalesHistory = cb => fetch(`${API}?action=sales`).then(r => r.json()).then(cb);
const recordSale = (order, cb) => fetch(API, {method: 'POST', body: JSON.stringify({action: 'record_sale', order})}).then(r => r.json()).then(cb);
