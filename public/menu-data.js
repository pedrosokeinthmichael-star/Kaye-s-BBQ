/* =========================================================
   Kaye's Barbeque — shared menu/product catalog store.
   Persists to localStorage (kayesMenu) so items added, price
   changes, and availability toggles made in the admin panel
   stick around and are shared by admin.html and menus.html.
   Units sold / cost are sample figures carried over from the
   original hardcoded catalog — swap for real POS data later.
   ========================================================= */
(function (global) {
  var KEY = 'kayesMenu';

  var DEFAULTS = [
    { name: 'Classic Pork BBQ',        category: 'Skewers & Isaw',       price: 35,  cost: 14,  unitsSold: 1240, available: true, stock: 200 },
    { name: 'Isaw Baboy',              category: 'Skewers & Isaw',       price: 20,  cost: 7,   unitsSold: 980,  available: true, stock: 200 },
    { name: 'Isaw Manok',              category: 'Skewers & Isaw',       price: 20,  cost: 7,   unitsSold: 720,  available: true, stock: 200 },
    { name: 'Betamax',                 category: 'Skewers & Isaw',       price: 25,  cost: 8,   unitsSold: 540,  available: true, stock: 150 },
    { name: 'Chicken Batikulon',       category: 'Skewers & Isaw',       price: 20,  cost: 7,   unitsSold: 340,  available: true, stock: 150 },
    { name: 'Chicken Neck',            category: 'Skewers & Isaw',       price: 20,  cost: 7,   unitsSold: 300,  available: true, stock: 150 },
    { name: 'Chicken Head',            category: 'Skewers & Isaw',       price: 25,  cost: 9,   unitsSold: 150,  available: true, stock: 100 },
    { name: 'Grilled Hotdog',          category: 'Skewers & Isaw',       price: 20,  cost: 9,   unitsSold: 460,  available: true, stock: 150 },
    { name: 'Chicken Inasal',          category: 'Inasal',               price: 165, cost: 78,  unitsSold: 610,  available: true, stock: 100 },
    { name: 'Grilled Chorizo Skewers', category: 'Chorizo & Longganisa', price: 30,  cost: 14,  unitsSold: 410,  available: true, stock: 150 },
    { name: 'Tuna Panga',              category: 'Grilled Seafood',      price: 280, cost: 150, unitsSold: 95,   available: true, stock: 50  },
    { name: 'Atchara',                 category: 'Sides',                price: 35,  cost: 10,  unitsSold: 260,  available: true, stock: 100 },
    { name: "Suka't Toyo",             category: 'Sawsawan (Dips)',      price: 15,  cost: 4,   unitsSold: 380,  available: true, stock: 150 },
    { name: 'Spicy Vinegar',           category: 'Sawsawan (Dips)',      price: 15,  cost: 4,   unitsSold: 290,  available: true, stock: 150 },
    { name: 'Banana Ketchup Glaze',    category: 'Sawsawan (Dips)',      price: 15,  cost: 5,   unitsSold: 220,  available: true, stock: 150 }
  ];

  function uid() {
    return 'p_' + Date.now().toString(36) + Math.random().toString(36).slice(2, 7);
  }

  function readRaw() {
    try { return JSON.parse(localStorage.getItem(KEY)); } catch (e) { return null; }
  }

  function writeRaw(list) {
    localStorage.setItem(KEY, JSON.stringify(list));
  }

  function seedIfEmpty() {
    var existing = readRaw();
    if (existing && existing.length) return;
    var seeded = DEFAULTS.map(function (p) {
      var copy = {};
      for (var k in p) copy[k] = p[k];
      copy.id = uid();
      return copy;
    });
    writeRaw(seeded);
  }

  // Normalizes stored fields and derives revenue/profit/margin.
  // cost may be null (unknown) — margin/profit are then null too.
  function normalize(p) {
    var price = Number(p.price) || 0;
    var unitsSold = Number(p.unitsSold) || 0;
    var hasCost = p.cost !== null && p.cost !== undefined && p.cost !== '';
    var cost = hasCost ? Number(p.cost) : null;
    if (cost !== null && isNaN(cost)) cost = null;

    var stock = Number(p.stock);
    if (isNaN(stock) || stock < 0) stock = 0;

    var out = {
      id: p.id,
      name: p.name || 'Untitled Item',
      category: p.category || 'Uncategorized',
      price: price,
      cost: cost,
      unitsSold: unitsSold,
      available: p.available !== false,
      stock: stock,
      revenue: price * unitsSold
    };
    if (cost !== null) {
      out.profit = (price - cost) * unitsSold;
      out.marginPct = price ? Math.round(((price - cost) / price) * 100) : 0;
    } else {
      out.profit = null;
      out.marginPct = null;
    }
    return out;
  }

  function getProducts() {
    seedIfEmpty();
    var list = readRaw() || [];
    return list.map(normalize);
  }

  function saveProducts(list) {
    writeRaw(list);
  }

  function addProduct(product) {
    var list = readRaw() || [];
    var toStore = {};
    for (var k in product) toStore[k] = product[k];
    toStore.id = uid();
    list.push(toStore);
    writeRaw(list);
    return normalize(toStore);
  }

  function updateProduct(id, changes) {
    var list = readRaw() || [];
    var updated = null;
    list = list.map(function (p) {
      if (p.id !== id) return p;
      var merged = {};
      for (var k in p) merged[k] = p[k];
      for (var k2 in changes) merged[k2] = changes[k2];
      updated = merged;
      return merged;
    });
    writeRaw(list);
    return updated ? normalize(updated) : null;
  }

  function deleteProduct(id) {
    var list = (readRaw() || []).filter(function (p) { return p.id !== id; });
    writeRaw(list);
  }

  // Increases stock for a product by a positive amount. Stock can only
  // ever go up through this method — there is no way to set it to a
  // lower value than it currently holds, so admins can't accidentally
  // (or intentionally) reduce inventory below what's already recorded.
  function addStock(id, amount) {
    amount = Number(amount);
    if (isNaN(amount) || amount <= 0) return null;

    var list = readRaw() || [];
    var updated = null;
    list = list.map(function (p) {
      if (p.id !== id) return p;
      var merged = {};
      for (var k in p) merged[k] = p[k];
      var current = Number(merged.stock);
      if (isNaN(current) || current < 0) current = 0;
      merged.stock = current + amount;
      updated = merged;
      return merged;
    });
    writeRaw(list);
    return updated ? normalize(updated) : null;
  }

  global.KayesMenu = {
    getProducts: getProducts,
    saveProducts: saveProducts,
    addProduct: addProduct,
    updateProduct: updateProduct,
    deleteProduct: deleteProduct,
    addStock: addStock
  };
})(window);