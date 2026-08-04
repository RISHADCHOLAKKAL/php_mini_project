// Default Initial Menu Items Data
const DEFAULT_MENU_ITEMS = [
  { id: 1, name: "Traditional Kanji", desc: "Served hot with green gram & pickle", price: 40, category: "food", qty: 15, isAvailable: true, icon: "M12 2C8.14 2 5 5.14 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.86-3.14-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" },
  { id: 2, name: "Special Meal Thali", desc: "Rice, Sambar, Aviyal, Thoran & Curd", price: 80, category: "food", qty: 8, isAvailable: true, icon: "M11 9H9V2H7v7H5V2H3v7c0 2.12 1.46 3.9 3.45 4.35V22h2.1v-8.65C10.54 12.9 12 11.12 12 9V2h-1v7zm8-7h-2c-1.1 0-2 .9-2 2v6c0 1.66 1.34 3 3 3v8h2V2z" },
  { id: 3, name: "Special Masala Tea", desc: "Brewed with fresh ginger & cardamom", price: 15, category: "beverage", qty: 25, isAvailable: true, icon: "M20 3H4v10c0 2.21 1.79 4 4 4h6c2.21 0 4-1.79 4-4v-3h2c1.11 0 2-.89 2-2V5c0-1.11-.89-2-2-2zm0 5h-2V5h2v3zM4 19h16v2H4z" },
  { id: 4, name: "Fresh Lime Juice", desc: "Chilled refreshing mint lime", price: 25, category: "beverage", qty: 10, isAvailable: true, icon: "M12 2c-5.52 0-10 4.48-10 10s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" },
  { id: 5, name: "Pazham Pori", desc: "Crispy banana fritters", price: 20, category: "snacks", qty: 0, isAvailable: false, icon: "M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" },
  { id: 6, name: "Samosa", desc: "Spiced potato filling", price: 20, category: "snacks", qty: 12, isAvailable: true, icon: "M12 2l-5.5 9h11zM12 22l5.5-9h-11z" }
];

// Sample Sales History Data (for Sales Report)
const DEFAULT_SALES_HISTORY = [
  { id: "ORD-101", date: "2026-08-04T10:15:00", type: "Table 1", table: 1, total: 120, items: [{ name: "Traditional Kanji", qty: 2, price: 40 }, { name: "Special Masala Tea", qty: 2, price: 15 }] },
  { id: "ORD-102", date: "2026-08-04T11:30:00", type: "Open Order", table: null, total: 160, items: [{ name: "Special Meal Thali", qty: 2, price: 80 }] },
  { id: "ORD-103", date: "2026-08-04T12:10:00", type: "Table 3", table: 3, total: 95, items: [{ name: "Special Meal Thali", qty: 1, price: 80 }, { name: "Special Masala Tea", qty: 1, price: 15 }] },
  { id: "ORD-104", date: "2026-08-04T13:45:00", type: "Table 2", table: 2, total: 200, items: [{ name: "Traditional Kanji", qty: 2, price: 40 }, { name: "Special Meal Thali", qty: 1, price: 80 }, { name: "Fresh Lime Juice", qty: 1, price: 25 }, { name: "Special Masala Tea", qty: 1, price: 15 }] },
  { id: "ORD-105", date: "2026-08-04T15:20:00", type: "Open Order", table: null, total: 40, items: [{ name: "Pazham Pori", qty: 2, price: 20 }] },
  { id: "ORD-106", date: "2026-08-04T17:00:00", type: "Table 5", table: 5, total: 110, items: [{ name: "Special Meal Thali", qty: 1, price: 80 }, { name: "Fresh Lime Juice", qty: 1, price: 25 }, { name: "Samosa", qty: 1, price: 20 }] }
];

// Function to get menu items from localStorage
function getMenuItems() {
  const stored = localStorage.getItem('ecanteen_menu');
  if (!stored) {
    localStorage.setItem('ecanteen_menu', JSON.stringify(DEFAULT_MENU_ITEMS));
    return DEFAULT_MENU_ITEMS;
  }
  return JSON.parse(stored);
}

// Function to save menu items to localStorage
function saveMenuItems(items) {
  localStorage.setItem('ecanteen_menu', JSON.stringify(items));
}

// Function to get sales report data from localStorage
function getSalesHistory() {
  const stored = localStorage.getItem('ecanteen_sales');
  if (!stored) {
    localStorage.setItem('ecanteen_sales', JSON.stringify(DEFAULT_SALES_HISTORY));
    return DEFAULT_SALES_HISTORY;
  }
  return JSON.parse(stored);
}

// Function to record a new sale
function recordSale(orderType, tableNum, itemsList, totalAmount) {
  const sales = getSalesHistory();
  const newOrder = {
    id: 'ORD-' + (100 + sales.length + 1),
    date: new Date().toISOString(),
    type: orderType,
    table: tableNum,
    total: totalAmount,
    items: itemsList
  };
  sales.unshift(newOrder);
  localStorage.setItem('ecanteen_sales', JSON.stringify(sales));
}
