import { MAX_QUANTITY, normalizeState, cartTotals, filterProducts, quotationItems } from './storefront-state';

const dataElement = document.querySelector('#storefront-data');

if (dataElement) {
    const data = JSON.parse(dataElement.textContent);
    const productsById = new Map(data.products.map(product => [product.id, product]));
    const storageKey = 'selva-storefront-v1';
    let storedState;
    try {
        storedState = JSON.parse(localStorage.getItem(storageKey));
    } catch {
        storedState = null;
    }
    let state = normalizeState(data.userId ? data.savedState : storedState, data.products);
    let saveQueue = Promise.resolve();
    let currentCategory = new URL(location.href).searchParams.get('category') || 'all';
    let currentQuery = new URL(location.href).searchParams.get('q') || '';
    let activePanel = '';
    let returnFocus;
    let toastTimeout;
    const backToTop = document.querySelector('#back-to-top');
    const carousel = document.querySelector('[data-hero-carousel]');
    const dialog = document.querySelector('#store-dialog');
    const quoteDialog = document.querySelector('#quote-dialog');
    const dialogContent = document.querySelector('#dialog-content');
    const dialogTitle = document.querySelector('#dialog-title');
    const money = amount => 'Rs. ' + amount.toLocaleString('en-LK');
    const whatsappUrl = message => 'https://wa.me/' + data.whatsapp + '?text=' + encodeURIComponent(message);

    function element(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    }

    function actionButton(text, className, key, value) {
        const button = element('button', className, text);
        button.type = 'button';
        button.dataset[key] = value;
        return button;
    }

    function notify(message) {
        const toast = document.querySelector('#store-toast');
        clearTimeout(toastTimeout);
        toast.textContent = message;
        toast.hidden = false;
        toastTimeout = setTimeout(() => { toast.hidden = true; }, 3500);
    }

    function updateBackToTop() {
        backToTop?.classList.toggle('is-visible', window.scrollY > 520);
    }

    if (backToTop) {
        window.addEventListener('scroll', updateBackToTop, { passive: true });
        backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        updateBackToTop();
    }

    if (carousel) {
        const slides = [...carousel.querySelectorAll('.hero-slide')];
        const dots = [...carousel.querySelectorAll('[data-carousel-dot]')];
        let slideIndex = 0;
        let carouselTimer;
        const showSlide = index => {
            slideIndex = (index + slides.length) % slides.length;
            slides.forEach((slide, itemIndex) => slide.classList.toggle('is-active', itemIndex === slideIndex));
            const activeSlide = slides[slideIndex];
            carousel.querySelector('[data-carousel-caption]').textContent = activeSlide.dataset.caption;
            carousel.querySelector('[data-carousel-certification]').textContent = activeSlide.dataset.certification;
            carousel.querySelector('[data-carousel-standard]').textContent = activeSlide.dataset.standard;
            carousel.querySelector('[data-carousel-status]').textContent = activeSlide.dataset.status;
            dots.forEach((dot, itemIndex) => {
                const active = itemIndex === slideIndex;
                dot.classList.toggle('is-active', active);
                dot.setAttribute('aria-selected', String(active));
            });
        };
        const restartCarousel = () => {
            clearInterval(carouselTimer);
            carouselTimer = setInterval(() => showSlide(slideIndex + 1), 5000);
        };
        carousel.querySelector('[data-carousel-prev]')?.addEventListener('click', () => { showSlide(slideIndex - 1); restartCarousel(); });
        carousel.querySelector('[data-carousel-next]')?.addEventListener('click', () => { showSlide(slideIndex + 1); restartCarousel(); });
        dots.forEach(dot => dot.addEventListener('click', () => { showSlide(Number(dot.dataset.carouselDot)); restartCarousel(); }));
        carousel.addEventListener('mouseenter', () => clearInterval(carouselTimer));
        carousel.addEventListener('mouseleave', restartCarousel);
        carousel.addEventListener('focusin', () => clearInterval(carouselTimer));
        carousel.addEventListener('focusout', event => { if (!carousel.contains(event.relatedTarget)) restartCarousel(); });
        restartCarousel();
    }

    function saveState() {
        if (data.userId) {
            const payload = JSON.stringify({ ...state, user_id: data.userId });
            saveQueue = saveQueue.catch(() => {}).then(async () => {
                const response = await fetch(data.stateUrl, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': data.csrfToken },
                    body: payload,
                    keepalive: true,
                });
                if (!response.ok) throw new Error('Account save failed');
            });
            saveQueue.catch(() => notify('Your selections could not be saved. Check your connection and try again before leaving.'));
        } else {
            try {
                localStorage.setItem(storageKey, JSON.stringify(state));
            } catch {
                notify('Browser storage is unavailable. Your selections are saved for this visit only.');
            }
        }
        updateCounters();
    }

    document.querySelectorAll('[data-store-logout]').forEach(form => {
        form.addEventListener('submit', async event => {
            if (!data.userId) return;
            event.preventDefault();
            const button = form.querySelector('button');
            button.disabled = true;
            try {
                saveState();
                await saveQueue;
                form.submit();
            } catch {
                button.disabled = false;
                notify('Could not save your selections. Please try signing out again.');
            }
        });
    });
    function updateCounters() {
        const totals = cartTotals(state.cart, data.products);
        document.querySelectorAll('[data-cart-count]').forEach(node => { node.textContent = totals.count; });
        document.querySelectorAll('[data-cart-total]').forEach(node => { node.textContent = money(totals.total); });
        document.querySelectorAll('[data-wishlist-count]').forEach(node => { node.textContent = state.wishlist.length; });
        document.querySelectorAll('[data-wishlist]').forEach(button => {
            const saved = state.wishlist.includes(button.dataset.wishlist);
            const product = productsById.get(button.dataset.wishlist);
            button.setAttribute('aria-pressed', String(saved));
            button.setAttribute('aria-label', (saved ? 'Remove ' : 'Save ') + product.name + (saved ? ' from wishlist' : ' to wishlist'));
        });
    }

    function addToCart(id) {
        const product = productsById.get(id);
        if (!product) return;
        if (product.in_stock === false) { notify('This product is out of stock. Please contact us for availability.'); return; }
        const quantity = state.cart[id] || 0;
        if (quantity >= MAX_QUANTITY) {
            notify('For quantities above 99, please request a bulk quotation.');
            return;
        }
        state.cart[id] = quantity + 1;
        saveState();
        notify(product.brand + ' item added to your estimate.');
    }

    function closeDialogs(restoreFocus = true) {
        if (dialog.open) dialog.close();
        if (quoteDialog.open) quoteDialog.close();
        document.body.classList.remove('dialog-open');
        if (restoreFocus && returnFocus?.isConnected) returnFocus.focus();
    }

    function openDialog(title, panel) {
        if (!dialog.open && !quoteDialog.open) returnFocus = document.activeElement;
        closeDialogs(false);
        activePanel = panel;
        dialogTitle.textContent = title;
        dialogContent.replaceChildren();
        dialog.showModal();
        document.body.classList.add('dialog-open');
    }

    function emptyPanel(message) {
        const container = element('div', 'empty-state');
        container.append(element('p', '', message), actionButton('Explore products', 'button button-navy', 'continueShopping', ''));
        dialogContent.append(container);
    }

    function productImage(product) {
        const img = element('img');
        img.src = product.image;
        img.alt = product.name;
        img.width = 76;
        img.height = 66;
        return img;
    }

    function renderCart() {
        dialogContent.replaceChildren();
        if (!cartTotals(state.cart, data.products).count) {
            emptyPanel('Your estimate is empty. Add products to prepare a quotation.');
            return;
        }
        const fragment = document.querySelector('#cart-template').content.cloneNode(true);
        const lines = fragment.querySelector('.cart-lines');
        for (const product of data.products.filter(item => state.cart[item.id])) {
            const line = element('article', 'cart-line');
            const detail = element('div');
            detail.append(element('h3', '', product.name), element('p', '', money(product.price) + ' each'));
            const quantity = element('div', 'quantity-controls');
            const decrease = actionButton('−', '', 'quantityDecrease', product.id);
            decrease.setAttribute('aria-label', 'Decrease quantity of ' + product.name);
            const increase = actionButton('+', '', 'quantityIncrease', product.id);
            increase.setAttribute('aria-label', 'Increase quantity of ' + product.name);
            increase.disabled = state.cart[product.id] >= MAX_QUANTITY;
            const output = element('output', '', String(state.cart[product.id]));
            output.setAttribute('aria-label', 'Quantity of ' + product.name);
            quantity.append(decrease, output, increase);
            detail.append(quantity);
            const remove = actionButton('Remove', 'remove-item', 'removeCart', product.id);
            remove.setAttribute('aria-label', 'Remove ' + product.name + ' from cart');
            line.append(productImage(product), detail, remove);
            lines.append(line);
        }
        fragment.querySelector('.cart-total strong').textContent = money(cartTotals(state.cart, data.products).total);
        dialogContent.append(fragment);
    }

    function renderWishlist() {
        dialogContent.replaceChildren();
        if (!state.wishlist.length) {
            emptyPanel('Your wishlist is empty. Save a product using its heart button.');
            return;
        }
        for (const id of state.wishlist) {
            const product = productsById.get(id);
            const line = element('article', 'cart-line');
            const detail = element('div');
            detail.append(element('h3', '', product.name), element('p', '', money(product.price)));
            detail.append(actionButton('Add to estimate', 'text-link', 'add', id));
            const remove = actionButton('Remove', 'remove-item', 'wishlist', id);
            remove.setAttribute('aria-label', 'Remove ' + product.name + ' from wishlist');
            line.append(productImage(product), detail, remove);
            dialogContent.append(line);
        }
    }

    function showProduct(id) {
        const product = productsById.get(id);
        if (!product) return;
        openDialog(product.name, 'product');
        const fragment = document.querySelector('#product-detail-template').content.cloneNode(true);
        const img = fragment.querySelector('.detail-image');
        img.src = product.image;
        img.alt = product.name;
        fragment.querySelector('.detail-brand').textContent = product.brand;
        fragment.querySelector('.detail-certification').textContent = product.certification;
        fragment.querySelector('.detail-specification').textContent = product.specification;
        fragment.querySelector('.product-price strong').textContent = money(product.price);
        fragment.querySelector('.product-price del').textContent = money(product.original_price);
        fragment.querySelector('.product-price del').hidden = product.original_price <= product.price;
        fragment.querySelector('[data-detail-add]').dataset.add = id;
        fragment.querySelector('[data-detail-add]').disabled = product.in_stock === false;
        fragment.querySelector('.detail-whatsapp').href = whatsappUrl('Hello, please confirm price and availability for ' + product.name + '.');
        dialogContent.append(fragment);
    }

    function openQuote(items) {
        if (!dialog.open && !quoteDialog.open) returnFocus = document.activeElement;
        closeDialogs(false);
        const itemsField = document.querySelector('#quote-items');
        if (items !== undefined) itemsField.value = items;
        else if (Object.keys(state.cart).length) itemsField.value = quotationItems(state.cart, data.products);
        quoteDialog.showModal();
        document.body.classList.add('dialog-open');
    }

    function openPanel(panel) {
        if (panel === 'quote') { openQuote(); return; }
        const titles = { cart: 'Your Cart & Quotation', wishlist: 'Your Wishlist', catalog: 'Explore Our Product Catalog', account: 'Customer & Trade Accounts', trade: 'Register Your Trade Interest' };
        openDialog(titles[panel] || 'How can we help?', panel);
        if (panel === 'cart') renderCart();
        else if (panel === 'wishlist') renderWishlist();
        else if (panel === 'catalog') {
            dialogContent.append(element('p', '', 'Browse the featured range by category, or ask our counter for the full inventory and current availability.'));
            const list = element('div', 'dialog-list');
            data.categories.forEach(category => list.append(actionButton(category.name, '', 'category', category.id)));
            dialogContent.append(list);
            const actions = element('div', 'dialog-actions');
            actions.append(actionButton('Request the full catalog', 'button button-green', 'service', 'Please send me your full product catalog.'));
            dialogContent.append(actions);
        } else if (panel === 'account' || panel === 'trade') {
            dialogContent.append(element('p', '', panel === 'account'
                ? 'Online account sign-in is not available yet. You can shop without an account and save your estimate on this device. For an existing trade account, contact our Jaffna counter.'
                : 'Interested in contractor pricing, staged deliveries, or a trade account? Send your business details to our counter to discuss eligibility.'));
            const actions = element('div', 'dialog-actions');
            actions.append(actionButton('Inquire about a trade account', 'button button-green', 'service', 'I would like to inquire about a trade account. Business name: \nBusiness address: \nExpected supplies: '));
            dialogContent.append(actions);
        }
    }

    function applyFilters(scroll = false) {
        if (!document.querySelector('#products')) {
            const url = new URL(data.homeUrl);
            url.searchParams.set('category', currentCategory);
            url.searchParams.set('q', currentQuery);
            url.hash = 'products';
            location.assign(url);
            return;
        }
        const matches = filterProducts(data.products, currentQuery, currentCategory);
        const visibleIds = new Set(matches.map(product => product.id));
        document.querySelectorAll('[data-product-card]').forEach(card => { card.hidden = !visibleIds.has(card.dataset.productCard); });
        document.querySelectorAll('[data-filter]').forEach(button => {
            const selected = button.dataset.filter === currentCategory;
            button.classList.toggle('selected', selected);
            button.setAttribute('aria-pressed', String(selected));
        });
        document.querySelector('#no-products').hidden = matches.length > 0;
        document.querySelector('#search-status').hidden = currentCategory === 'all' && !currentQuery;
        const categoryName = data.categories.find(category => category.id === currentCategory)?.name;
        document.querySelector('#results-label').textContent = matches.length + ' featured product' + (matches.length === 1 ? '' : 's')
            + (categoryName ? ' in ' + categoryName : '')
            + (currentQuery ? ' matching “' + currentQuery + '”' : '');
        if (scroll) document.querySelector('#products').scrollIntoView({ block: 'start' });
    }

    function setCategory(category) {
        currentCategory = category;
        currentQuery = '';
        document.querySelector('#search-query').value = '';
        const select = document.querySelector('#search-category');
        select.value = data.categories.some(item => item.id === category) ? category : 'all';
        document.querySelector('#category-menu').hidden = true;
        document.querySelector('#browse-toggle').setAttribute('aria-expanded', 'false');
        closeDialogs();
        applyFilters(true);
    }

    if (document.querySelector('#products') && (currentQuery || currentCategory !== 'all')) {
        document.querySelector('#search-query').value = currentQuery;
        document.querySelector('#search-category').value = currentCategory;
        applyFilters();
    }

    function restoreCartFocus(id, kind, oldIndex) {
        const candidates = [...dialogContent.querySelectorAll('button')];
        const match = candidates.find(button => button.dataset[kind] === id && !button.disabled);
        (match || candidates[Math.min(oldIndex, candidates.length - 1)] || dialog.querySelector('[data-close]')).focus();
    }

    document.addEventListener('click', event => {
        const target = event.target.closest('button, a');
        if (!target) return;
        if (target.hasAttribute('data-close')) closeDialogs();
        else if (target.dataset.open) openPanel(target.dataset.open);
        else if (target.dataset.add) addToCart(target.dataset.add);
        else if (target.dataset.product) showProduct(target.dataset.product);
        else if (target.hasAttribute('data-wishlist')) {
            const id = target.dataset.wishlist;
            if (!productsById.has(id)) return;
            const wasSaved = state.wishlist.includes(id);
            state.wishlist = wasSaved ? state.wishlist.filter(item => item !== id) : [...state.wishlist, id];
            saveState();
            notify(wasSaved ? 'Product removed from wishlist.' : 'Product saved to wishlist.');
            if (dialog.open && activePanel === 'wishlist') {
                renderWishlist();
                (dialogContent.querySelector('button') || dialog.querySelector('[data-close]')).focus();
            }
        } else if (target.dataset.quantityDecrease || target.dataset.quantityIncrease || target.dataset.removeCart) {
            const kind = target.dataset.quantityDecrease ? 'quantityDecrease' : target.dataset.quantityIncrease ? 'quantityIncrease' : 'removeCart';
            const id = target.dataset[kind];
            const oldIndex = [...dialogContent.querySelectorAll('button')].indexOf(target);
            if (kind === 'removeCart') delete state.cart[id];
            else {
                const next = Math.min(MAX_QUANTITY, (state.cart[id] || 0) + (kind === 'quantityIncrease' ? 1 : -1));
                if (next <= 0) delete state.cart[id]; else state.cart[id] = next;
            }
            saveState();
            renderCart();
            restoreCartFocus(id, kind, oldIndex);
        } else if (target.hasAttribute('data-clear-cart')) {
            state.cart = {};
            saveState();
            renderCart();
            dialogContent.querySelector('button').focus();
        } else if (target.dataset.category) {
            event.preventDefault();
            setCategory(target.dataset.category);
        } else if (target.dataset.filter) {
            currentCategory = target.dataset.filter;
            document.querySelector('#search-category').value = data.categories.some(item => item.id === currentCategory) ? currentCategory : 'all';
            applyFilters();
        } else if (target.hasAttribute('data-reset-products')) {
            event.preventDefault();
            setCategory('all');
        } else if (target.hasAttribute('data-continue-shopping')) {
            closeDialogs();
            if (document.querySelector('#products')) document.querySelector('#products').scrollIntoView(); else location.assign(data.homeUrl + '#products');
        } else if (target.dataset.service) {
            openQuote(target.dataset.service);
        }
        if (target.closest('#main-navigation')) {
            document.querySelector('#main-navigation').classList.remove('is-open');
            document.querySelector('#mobile-menu-toggle').setAttribute('aria-expanded', 'false');
        }
    });

    document.querySelector('#product-search').addEventListener('submit', event => {
        event.preventDefault();
        currentQuery = document.querySelector('#search-query').value.trim();
        currentCategory = document.querySelector('#search-category').value;
        applyFilters(true);
    });
    document.querySelector('#search-query').addEventListener('search', event => {
        currentQuery = event.target.value.trim();
        currentCategory = document.querySelector('#search-category').value;
        applyFilters();
    });
    document.querySelector('#browse-toggle').addEventListener('click', event => {
        const menu = document.querySelector('#category-menu');
        menu.hidden = !menu.hidden;
        event.currentTarget.setAttribute('aria-expanded', String(!menu.hidden));
    });
    document.querySelector('#mobile-menu-toggle').addEventListener('click', event => {
        const expanded = document.querySelector('#main-navigation').classList.toggle('is-open');
        event.currentTarget.setAttribute('aria-expanded', String(expanded));
    });
    document.addEventListener('click', event => {
        if (!event.target.closest('.category-dropdown')) {
            document.querySelector('#category-menu').hidden = true;
            document.querySelector('#browse-toggle').setAttribute('aria-expanded', 'false');
        }
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !document.querySelector('#category-menu').hidden) {
            document.querySelector('#category-menu').hidden = true;
            const toggle = document.querySelector('#browse-toggle');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.focus();
        }
    });

    [dialog, quoteDialog].forEach(modal => {
        modal.addEventListener('close', () => {
            if (!dialog.open && !quoteDialog.open) document.body.classList.remove('dialog-open');
        });
        modal.addEventListener('click', event => {
            if (event.target !== modal) return;
            const rect = modal.getBoundingClientRect();
            if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) closeDialogs();
        });
    });

    document.querySelector('#quotation-form').addEventListener('submit', async event => {
        event.preventDefault();
        const form = event.currentTarget;
        if (!form.reportValidity()) return;
        const fields = new FormData(form);
        for (const field of ['name', 'phone', 'items']) {
            const input = form.elements.namedItem(field);
            input.setCustomValidity(String(fields.get(field)).trim() ? '' : 'Please fill in this field.');
        }
        const phoneDigits = String(fields.get('phone')).replace(/\D/g, '');
        if (phoneDigits.length < 7 || phoneDigits.length > 15) {
            form.elements.namedItem('phone').setCustomValidity('Enter a phone number with 7 to 15 digits.');
        }
        if (!form.reportValidity()) return;
        const message = [
            'Hello Selva Electricals, I would like a quotation.',
            'Name: ' + String(fields.get('name')).trim(),
            'Phone: ' + String(fields.get('phone')).trim(),
            'Project / location: ' + (String(fields.get('project')).trim() || 'Not specified'),
            '', String(fields.get('items')).trim(), '',
            'Please confirm stock, final prices, and delivery options.'
        ].join('\n');
        const submit = form.querySelector('[type="submit"]');
        const result = document.querySelector('#quote-result');
        const followUp = document.querySelector('#quote-whatsapp');
        submit.disabled = true;
        result.textContent = 'Saving your request…';
        followUp.hidden = true;
        try {
            const response = await fetch(data.quotationUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': data.csrfToken },
                body: JSON.stringify({
                    name: String(fields.get('name')).trim(),
                    phone: String(fields.get('phone')).trim(),
                    message,
                    items: data.products.filter(product => state.cart[product.id]).map(product => ({
                        product_slug: product.id, product_name: product.name, quantity: state.cart[product.id],
                    })),
                }),
            });
            const body = await response.json();
            if (!response.ok) throw new Error(Object.values(body.errors || {}).flat()[0] || 'Unable to save your request. Please try again.');
            result.textContent = 'Request #Q' + body.id + ' received. Our team will contact you.';
            followUp.href = whatsappUrl('Quotation reference #Q' + body.id + '\n' + message);
            followUp.hidden = false;
            form.reset();
        } catch (error) {
            result.textContent = error.message || 'Connection failed. Your request has not been confirmed.';
        } finally {
            submit.disabled = false;
        }
    });
    document.querySelector('#quotation-form').addEventListener('input', event => event.target.setCustomValidity?.(''));

    window.addEventListener('storage', event => {
        if (data.userId || (event.key !== storageKey && event.key !== null)) return;
        try { state = normalizeState(JSON.parse(event.newValue), data.products); } catch { state = normalizeState(null, data.products); }
        updateCounters();
        if (dialog.open && activePanel === 'cart') renderCart();
        if (dialog.open && activePanel === 'wishlist') renderWishlist();
    });
    updateCounters();
}

const navigationHeader = document.querySelector('.site-header');
if (navigationHeader) {
    const updateNavigationHeight = () => {
        const navigationBar = navigationHeader.querySelector('.navigation-bar');
        const offset = navigationBar.offsetTop;
        navigationHeader.style.setProperty('--header-offset', '-' + offset + 'px');
        document.documentElement.style.setProperty('--navigation-height', (navigationBar.offsetHeight + 16) + 'px');
    };
    new ResizeObserver(updateNavigationHeight).observe(navigationHeader);
    updateNavigationHeight();
}

const categoryDropdown = document.querySelector('.category-dropdown');
if (categoryDropdown) {
    const categoryToggle = categoryDropdown.querySelector('#browse-toggle');
    const categoryMenu = categoryDropdown.querySelector('#category-menu');
    const openCategories = () => {
        categoryMenu.hidden = false;
        categoryToggle.setAttribute('aria-expanded', 'true');
    };
    const closeCategories = () => {
        categoryMenu.hidden = true;
        categoryToggle.setAttribute('aria-expanded', 'false');
    };
    categoryDropdown.addEventListener('pointerenter', event => {
        if (event.pointerType === 'mouse') openCategories();
    });
    categoryDropdown.addEventListener('pointerleave', () => {
        if (!categoryDropdown.contains(document.activeElement)) closeCategories();
    });
    categoryDropdown.addEventListener('focusin', event => {
        if (event.target.matches(':focus-visible')) openCategories();
    });
    categoryDropdown.addEventListener('focusout', event => {
        if (!categoryDropdown.contains(event.relatedTarget)) closeCategories();
    });
}




