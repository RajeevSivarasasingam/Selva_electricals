export const MAX_QUANTITY = 99;

export function normalizeState(value, products) {
    const knownIds = new Set(products.map(product => product.id));
    const cart = {};
    if (value?.cart && typeof value.cart === 'object' && !Array.isArray(value.cart)) {
        for (const [id, quantity] of Object.entries(value.cart)) {
            if (knownIds.has(id) && Number.isInteger(quantity) && quantity > 0) {
                cart[id] = Math.min(quantity, MAX_QUANTITY);
            }
        }
    }
    const wishlist = Array.isArray(value?.wishlist)
        ? [...new Set(value.wishlist.filter(id => knownIds.has(id)))]
        : [];
    return { cart, wishlist };
}

export function cartTotals(cart, products) {
    return products.reduce((totals, product) => {
        const quantity = cart[product.id] || 0;
        return { count: totals.count + quantity, total: totals.total + quantity * product.price };
    }, { count: 0, total: 0 });
}

export function filterProducts(products, query = '', category = 'all') {
    const terms = query.trim().toLocaleLowerCase().split(/\s+/).filter(Boolean);
    const categories = category.split(',');
    return products.filter(product => {
        const searchable = [product.name, product.brand, product.specification, product.category].join(' ').toLocaleLowerCase();
        return (category === 'all' || categories.includes(product.category))
            && terms.every(term => searchable.includes(term));
    });
}

export function quotationItems(cart, products) {
    return products.filter(product => cart[product.id]).map(product =>
        `${cart[product.id]} × ${product.name} — Rs. ${(cart[product.id] * product.price).toLocaleString('en-LK')}`
    ).join('\n');
}
