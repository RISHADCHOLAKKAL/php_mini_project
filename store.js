// ╔═══════════════════════════════════════════════════════════════╗
// ║           store.js — API Helper Functions                    ║
// ║                                                              ║
// ║  PURPOSE: This file contains helper functions that talk to   ║
// ║  the backend (api.php). Instead of writing long fetch()      ║
// ║  calls everywhere, we call simple functions like             ║
// ║  getMenuItems() or recordSale().                             ║
// ║                                                              ║
// ║  USED BY: menu.html, open-order.html, staff.html,           ║
// ║           sales-report.html                                  ║
// ╚═══════════════════════════════════════════════════════════════╝


// -----------------------------------------------------------
// The URL of our backend API file
// -----------------------------------------------------------
// All our data requests go to this one PHP file.
// The "action" parameter tells api.php WHAT to do.
var API = 'api.php';


// -----------------------------------------------------------
// FUNCTION 1: getMenuItems(callback)
// -----------------------------------------------------------
// Fetches ALL menu items from the database.
//
// HOW IT WORKS:
//   1. Sends a GET request to: api.php?action=menu
//   2. api.php reads all items from the menu_items table
//   3. Sends back a JSON array of items
//   4. We parse the JSON and call the "callback" function with the data
//
// WHAT IS A CALLBACK?
//   A callback is a function you pass as an argument.
//   It gets called AFTER the data arrives from the server.
//   Example usage:
//     getMenuItems(function(items) {
//       console.log(items);  // items = array of menu objects
//     });
//
function getMenuItems(callback) {
    fetch(API + '?action=menu')    // send GET request to api.php?action=menu
        .then(function(response) {
            return response.json();  // convert the response text to a JavaScript object
        })
        .then(function(data) {
            callback(data);          // call the callback with the menu items
        });
}


// -----------------------------------------------------------
// FUNCTION 2: saveMenuItem(item, callback)
// -----------------------------------------------------------
// Saves a new menu item OR updates an existing one.
//
// PARAMETERS:
//   item     = an object like { name: "Tea", price: 15, qty: 10, ... }
//              If item.id exists → UPDATE that item
//              If item.id is empty → INSERT a new item
//   callback = function to call after saving is done
//
// HOW IT WORKS:
//   1. Sends a POST request to api.php
//   2. The body contains: { action: "save_item", item: {...} }
//   3. api.php inserts or updates the item in the database
//   4. Sends back { ok: true } on success
//
function saveMenuItem(item, callback) {
    fetch(API, {
        method: 'POST',                                          // POST = sending data to the server
        body: JSON.stringify({ action: 'save_item', item: item }) // convert object to JSON string
    })
    .then(function(response) {
        return response.json();    // parse the JSON response
    })
    .then(function(data) {
        callback(data);            // call the callback with the result
    });
}


// -----------------------------------------------------------
// FUNCTION 3: getSalesHistory(callback)
// -----------------------------------------------------------
// Fetches ALL past orders (sales history) from the database.
//
// RETURNS (via callback): An array of order objects, each containing:
//   { id, order_code, date, type, total, items: [...] }
//
function getSalesHistory(callback) {
    fetch(API + '?action=sales')    // GET request to api.php?action=sales
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            callback(data);
        });
}


// -----------------------------------------------------------
// FUNCTION 4: recordSale(order, callback)
// -----------------------------------------------------------
// Records a new sale/order in the database.
//
// PARAMETERS:
//   order = an object like:
//     {
//       total: 150,
//       items: [
//         { name: "Masala Tea", qty: 2, price: 15 },
//         { name: "Samosa",     qty: 1, price: 20 }
//       ]
//     }
//   callback = function to call after the order is saved
//
// HOW IT WORKS:
//   1. Sends a POST request to api.php
//   2. api.php creates a new row in the "orders" table
//   3. For each item, it creates a row in "order_items" table
//   4. It also REDUCES the stock quantity in "menu_items"
//
function recordSale(order, callback) {
    fetch(API, {
        method: 'POST',
        body: JSON.stringify({ action: 'record_sale', order: order })
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        callback(data);
    });
}
