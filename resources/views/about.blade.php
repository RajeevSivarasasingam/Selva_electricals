<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Learn about Selva Electricals, Jaffna’s trusted electrical and hardware partner.">
    <title>About Us | {{ $store['name'] }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <x-navbar :store="$store" />
    <main id="main">
        <section class="about-hero"><div class="container about-hero-grid"><div><p class="eyebrow">About Selva Electricals</p><h1>Building better projects with the right materials.</h1><p>We are a Jaffna-based electrical and hardware supplier helping homeowners, electricians, plumbers, and contractors find dependable products with practical advice.</p><div class="hero-actions"><a class="button button-amber" href="{{ route('home') }}#products">Explore Products <x-store-icon file="c9874.svg" :size="16" /></a><a class="button button-white" href="{{ route('home') }}#contact">Visit Our Counter</a></div></div><div class="about-hero-image"><img src="{{ asset('images/figma/dcf44.png') }}" alt="Inside Selva Electricals store" width="800" height="600"></div></div></section>
        <section class="section about-story"><div class="container about-story-grid"><div><p class="eyebrow">Our promise</p><h2>A helpful counter for every kind of project.</h2><p>From a single replacement breaker to a complete construction bill of quantities, our team keeps the buying process clear. We stock trusted trade brands, explain the options plainly, and help you choose specifications that fit your project.</p><p>Our store serves the local Jaffna community while supporting deliveries and inquiries across Sri Lanka.</p></div><div class="about-stat-grid"><div><strong>5,000+</strong><span>Ready SKUs</span></div><div><strong>10</strong><span>Product categories</span></div><div><strong>100%</strong><span>Genuine products</span></div><div><strong>1</strong><span>Dedicated order desk</span></div></div></div></section>
        <section class="section about-values"><div class="container"><div class="center-heading"><p class="eyebrow">What guides us</p><h2>Reliable advice, genuine products, local service.</h2><p>Every part of the store is designed to make your next job easier to plan and complete.</p></div><div class="about-value-grid"><article class="feature-card"><span class="feature-icon"><x-store-icon file="7c096.svg" :size="28" /></span><h3>Quality first</h3><p>We focus on certified, manufacturer-backed products so you can build with confidence.</p></article><article class="feature-card"><span class="feature-icon"><x-store-icon file="3d1d1.svg" :size="28" /></span><h3>Technical guidance</h3><p>Our counter team helps with cable gauges, breaker ratings, pipe sizes, tools, and quantities.</p></article><article class="feature-card"><span class="feature-icon"><x-store-icon file="6d7d5.svg" :size="28" /></span><h3>Service that stays close</h3><p>Order through WhatsApp, collect from our Jaffna counter, or arrange delivery for your site.</p></article></div></div></section>
        <section class="section about-cta"><div class="container"><div class="project-banner"><div><p class="eyebrow">Let’s work on your project</p><h2>Need help choosing the right supplies?</h2><p>Share your list, drawing, or requirements with the Selva Electricals team and we’ll help you plan the next step.</p></div><div class="project-actions"><a class="button button-green" href="https://wa.me/{{ $store['whatsapp'] }}" target="_blank" rel="noopener"><x-store-icon file="49cd5.svg" /> Chat on WhatsApp</a><a class="button button-amber" href="{{ route('home') }}#contact">Find Our Store</a></div></div></div></section>
    </main>
    <x-store-footer :store="$store" />
    <x-store-dialogs :store="$store" />
</body>
</html>

