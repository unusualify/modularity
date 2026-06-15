<div class="layout-modularous-default-shell">
    <main>
        <section class="layout-hero">
            <p class="eyebrow">
                Modularous Default Layout Builder · {{ $cmsLayout->slug ?? 'untitled' }}
            </p>
            <h1>{{ $cmsLayout->name ?? 'Untitled Layout' }}</h1>
            <p class="lead">
                This placeholder illustrates how head, body, and footer segments will compose inside the
                {{ $cmsLayout->blade_source === 'filesystem' ? 'filesystem slug' : 'default' }} stack.
            </p>
        </section>
        <section class="layout-panels">
            <article class="layout-panel">
                <h3>Head slot</h3>
                <p>Critical metadata, fonts, and style resets belong here so the rest of the document renders consistently.</p>
            </article>
            <article class="layout-panel">
                <h3>Body slot</h3>
                <p>The body slot receives the actual layout content. Replace this placeholder with your components or sections.</p>
            </article>
            <article class="layout-panel">
                <h3>Footer slot</h3>
                <p>Use the footer slot for legal links, attributions, or a final CTA to anchor every page.</p>
            </article>
        </section>
    </main>
</div>
