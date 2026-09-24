@if (in_array($page, ['products', 'categories']))
<dialog id="catalog-dialog" class="catalog-dialog" aria-labelledby="catalog-dialog-title" data-has-errors="{{ $errors->any() ? 'true' : 'false' }}">
    <div class="catalog-dialog-heading"><div><p class="admin-eyebrow">GROW YOUR CATALOG</p><h2 id="catalog-dialog-title">Add {{ $page === 'products' ? 'product' : 'category' }}</h2></div><button type="button" data-close-catalog aria-label="Close form">×</button></div>
    <form method="post" action="{{ route('admin.'.$page.'.store') }}" class="catalog-form">
        @csrf
        @if ($errors->any())<div class="catalog-errors" role="alert"><strong>Please check these fields:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <label for="catalog-name">Name *</label><input id="catalog-name" name="name" required maxlength="255" value="{{ old('name') }}" autofocus>
        <label for="catalog-description">Details *</label><textarea id="catalog-description" name="description" required rows="3" maxlength="{{ $page === 'products' ? 5000 : 2000 }}">{{ old('description') }}</textarea>
        @if ($page === 'products')
            <label for="catalog-category">Category *</label><select id="catalog-category" name="category_id" required><option value="">Select a category</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>
            @if ($categories->isEmpty())<p>Add a category first in the Categories tab.</p>@endif
            <div class="catalog-form-columns"><div><label for="catalog-price">Regular price (Rs.) *</label><input id="catalog-price" name="price" type="number" min="0.01" max="9999999999.99" step="0.01" value="{{ old('price') }}" required></div><div><label for="catalog-offer">Offer price (Rs.)</label><input id="catalog-offer" name="offer_price" type="number" min="0.01" step="0.01" value="{{ old('offer_price') }}" aria-describedby="offer-hint"></div></div>
            <p id="offer-hint" class="catalog-hint">Optional. Must be less than the regular price.</p>
            <label for="catalog-brand">Brand</label><input id="catalog-brand" name="brand" maxlength="255" value="{{ old('brand') }}" placeholder="e.g. Philips">
            <label for="catalog-specification">Specifications</label><input id="catalog-specification" name="specification" maxlength="2000" value="{{ old('specification') }}" placeholder="e.g. 12W | Cool daylight | 2-year warranty">
            <label for="catalog-certification">Certification / warranty</label><input id="catalog-certification" name="certification" maxlength="255" value="{{ old('certification') }}">
            <label for="catalog-badge">Product label</label><input id="catalog-badge" name="badge" maxlength="100" value="{{ old('badge') }}" placeholder="e.g. New arrival">
            <label for="catalog-stock">Availability *</label><select id="catalog-stock" name="in_stock"><option value="1" @selected(old('in_stock', '1') == '1')>In stock</option><option value="0" @selected(old('in_stock') === '0')>Out of stock</option></select>
        @endif
        <label for="catalog-image">Image URL *</label><input id="catalog-image" name="image" type="url" required maxlength="2048" value="{{ old('image') }}" placeholder="https://example.com/image.jpg" aria-describedby="image-hint">
        <p id="image-hint" class="catalog-hint">Paste a public HTTP or HTTPS image link. This image will appear on the home page.</p>
        <img id="catalog-preview" class="catalog-preview" alt="Image preview" hidden referrerpolicy="no-referrer">
        <p id="catalog-preview-status" class="catalog-hint" role="status"></p>
        <div class="catalog-form-actions"><button type="button" data-close-catalog class="button button-soft">Cancel</button><button type="submit" class="button button-amber" @disabled($page === 'products' && $categories->isEmpty())>Add {{ $page === 'products' ? 'product' : 'category' }}</button></div>
    </form>
</dialog>
<script>
    const catalogDialog = document.querySelector('#catalog-dialog');
    const imageInput = document.querySelector('#catalog-image');
    const preview = document.querySelector('#catalog-preview');
    const previewStatus = document.querySelector('#catalog-preview-status');
    document.querySelector('[data-open-catalog]').addEventListener('click', () => catalogDialog.showModal());
    document.querySelectorAll('[data-close-catalog]').forEach(button => button.addEventListener('click', () => catalogDialog.close()));
    catalogDialog.addEventListener('click', event => {
        if (event.target !== catalogDialog) return;
        const rect = catalogDialog.getBoundingClientRect();
        if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) catalogDialog.close();
    });
    const previewImage = () => {
        preview.hidden = true;
        previewStatus.textContent = '';
        try {
            const url = new URL(imageInput.value);
            if (!['http:', 'https:'].includes(url.protocol)) return;
            previewStatus.textContent = 'Loading preview…';
            preview.src = url.href;
        } catch { preview.removeAttribute('src'); }
    };
    preview.addEventListener('load', () => { preview.hidden = false; previewStatus.textContent = ''; });
    preview.addEventListener('error', () => { preview.hidden = true; previewStatus.textContent = 'Unable to preview this image. Check that the link is public and points directly to an image.'; });
    imageInput.addEventListener('input', previewImage);
    if (catalogDialog.dataset.hasErrors === 'true') { catalogDialog.showModal(); previewImage(); }
    document.querySelector('.catalog-form').addEventListener('submit', event => {
        if (!event.target.reportValidity()) return;
        const submitButton = event.target.querySelector('[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Saving…';
    });
</script>
@endif
