@props(['store'])
<dialog id="store-dialog" class="store-dialog" aria-labelledby="dialog-title">
    <div class="dialog-heading"><h2 id="dialog-title"></h2><button class="dialog-close" data-close aria-label="Close dialog">×</button></div>
    <div id="dialog-content"></div>
</dialog>
<dialog id="quote-dialog" class="store-dialog quote-dialog" aria-labelledby="quote-title">
    <div class="dialog-heading"><h2 id="quote-title">Request a Quotation</h2><button class="dialog-close" data-close aria-label="Close quotation">×</button></div>
    <p>Tell us what your project needs. We’ll open a prepared WhatsApp message for you to review and send to our Jaffna counter.</p>
    <form id="quotation-form">
        <label for="quote-name">Your name <span aria-hidden="true">*</span></label><input id="quote-name" name="name" autocomplete="name" required maxlength="100">
        <label for="quote-phone">Phone number <span aria-hidden="true">*</span></label><input id="quote-phone" name="phone" type="tel" autocomplete="tel" required pattern="\+?[0-9][0-9\s\(\)\-]{5,23}[0-9]" maxlength="25" title="Enter a phone number using digits, spaces, brackets, + or -">
        <label for="quote-project">Project or delivery location</label><input id="quote-project" name="project" maxlength="180" placeholder="For example, house renovation in Jaffna">
        <label for="quote-items">Products, quantities, or service needed <span aria-hidden="true">*</span></label><textarea id="quote-items" name="items" rows="5" required maxlength="2500" placeholder="List the products and quantities you need"></textarea>
        <p class="form-hint">For a BOQ document or handwritten list, attach the file in WhatsApp after opening the chat. Prices and availability are confirmed by the store.</p>
        <button class="button button-green" type="submit"><x-store-icon file="49cd5.svg" /> Continue to WhatsApp</button>
    </form>
</dialog>
<template id="cart-template"><div class="cart-lines"></div><div class="cart-bottom"><p class="cart-total">Estimated total <strong></strong></p><p class="form-hint">This is a quotation estimate. Stock, final prices, and delivery charges will be confirmed by the store.</p><button class="button button-amber" data-open="quote">Request quotation for these items</button><button class="text-link" data-clear-cart>Clear cart</button></div></template>
<template id="product-detail-template"><div class="product-detail"><img class="detail-image" width="480" height="350" alt=""><div><p class="eyebrow detail-brand"></p><p class="detail-certification"></p><p class="detail-specification"></p><p class="product-price"><strong></strong><del></del></p><p class="form-hint">Confirm current stock and final pricing with our counter.</p><div class="detail-actions"><button class="button button-navy" data-detail-add><x-store-icon file="25f90.svg" /> Add to estimate</button><a class="button button-green detail-whatsapp" target="_blank" rel="noopener"><x-store-icon file="49cd5.svg" /> Ask on WhatsApp</a></div></div></div></template>
