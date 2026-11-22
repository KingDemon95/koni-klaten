@php
    $copy = $websitePageSections[$sectionKey] ?? [];
    $eyebrow = $copy['eyebrow'] ?? ($fallbackEyebrow ?? '');
    $title = $copy['title'] ?? ($fallbackTitle ?? '');
    $description = $copy['description'] ?? ($fallbackDescription ?? '');
    if (($title === '' || $title === null) && isset($dynamicTitle)) $title = $dynamicTitle;
    if (($description === '' || $description === null) && isset($dynamicDescription)) $description = $dynamicDescription;
    $headingTag = $headingTag ?? 'h2';
    $wrapperClass = $wrapperClass ?? '';
    $showDescription = $showDescription ?? true;
@endphp
<div class="{{ $wrapperClass }}">
    @if($eyebrow !== '')
        <p class="{{ $headingTag === 'h1' ? 'website-hero-eyebrow' : 'website-section-eyebrow' }} text-xs font-black tracking-[0.2em]">{{ $eyebrow }}</p>
    @endif
    @if($title !== '')
        @if($headingTag === 'h1')
            <h1 class="website-hero-title mt-3 text-3xl font-black sm:text-5xl">{{ $title }}</h1>
        @else
            <h2 class="website-section-title mt-2 text-2xl font-black sm:text-3xl">{{ $title }}</h2>
        @endif
    @endif
    @if($showDescription && $description !== '')
        <p class="{{ $headingTag === 'h1' ? 'website-hero-description mt-4 max-w-2xl text-sm leading-7 sm:text-base' : 'website-section-description mt-2 max-w-2xl text-sm leading-7' }}">{{ $description }}</p>
    @endif
</div>
