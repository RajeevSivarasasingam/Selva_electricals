import test from 'node:test';
import assert from 'node:assert/strict';
import { normalizeState, cartTotals, filterProducts, quotationItems } from '../../resources/js/storefront-state.js';

const products = [
    { id: 'wire', category: 'cables', name: 'Pure Copper Wire', brand: 'ACL', specification: '100m Roll', price: 10100 },
    { id: 'drill', category: 'power-tools', name: 'Impact Drill', brand: 'Bosch', specification: '550W', price: 14900 },
    { id: 'socket', category: 'switches', name: 'Double Socket', brand: 'Orange', specification: '13A', price: 980 },
];

test('restored cart data rejects stale products and invalid quantities and deduplicates favorites', () => {
    assert.deepEqual(normalizeState({
        cart: { wire: 200, drill: -1, socket: '3', missing: 8 },
        wishlist: ['wire', 'wire', 'missing', null],
    }, products), { cart: { wire: 99 }, wishlist: ['wire'] });
    assert.deepEqual(normalizeState(null, products), { cart: {}, wishlist: [] });
    assert.deepEqual(normalizeState({ cart: [], wishlist: {} }, products), { cart: {}, wishlist: [] });
});

test('totals use current catalog prices and quantities', () => {
    assert.deepEqual(cartTotals({ wire: 2, socket: 3 }, products), { count: 5, total: 23140 });
    assert.deepEqual(cartTotals({}, products), { count: 0, total: 0 });
});

test('search is case insensitive, matches all words, and intersects categories', () => {
    assert.deepEqual(filterProducts(products, ' ACL  copper ', 'cables').map(p => p.id), ['wire']);
    assert.deepEqual(filterProducts(products, 'bosch', 'cables'), []);
    assert.deepEqual(filterProducts(products, '', 'cables,switches').map(p => p.id), ['wire', 'socket']);
    assert.deepEqual(filterProducts(products, 'not-in-catalog'), []);
});

test('quotation includes selected quantities and line totals only', () => {
    const quote = quotationItems({ wire: 2 }, products);
    assert.match(quote, /2 × Pure Copper Wire/);
    assert.match(quote, /20,200/);
    assert.doesNotMatch(quote, /Impact Drill/);
    assert.equal(quotationItems({}, products), '');
});
