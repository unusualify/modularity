<title>{{ $cmsLayout->name ?? 'Layout Builder' }} · Preview</title>
<style>
    :root {
        font-family: 'Inter', 'Segoe UI', 'Helvetica Neue', sans-serif;
        color: #0f172a;
        background-color: #f8fafc;
    }
    * {
        box-sizing: border-box;
    }
    body {
        margin: 0;
        min-height: 100vh;
        background: #f8fafc;
    }
    .layout-default-shell {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }
    .layout-default-shell main {
        flex: 1;
    }
    .layout-hero {
        padding: 3rem clamp(1rem, 4vw, 3.5rem);
        background: radial-gradient(circle at top, rgba(59, 130, 246, 0.25), transparent 55%);
        text-align: center;
    }
    .layout-hero h1 {
        font-size: clamp(2rem, 3vw, 3rem);
        margin-bottom: 0.5rem;
    }
    .layout-hero .eyebrow {
        letter-spacing: 0.2em;
        font-size: 0.75rem;
        text-transform: uppercase;
        margin-bottom: 0.75rem;
        color: #2563eb;
    }
    .layout-hero .lead {
        margin-top: 1rem;
        color: #475569;
        font-size: 1rem;
    }
    .layout-hero p {
        color: #475569;
        margin: 0 auto;
        max-width: 640px;
    }
    .layout-panels {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
        padding: 2rem clamp(1rem, 4vw, 3.5rem);
    }
    .layout-panel {
        padding: 1.25rem;
        border-radius: 1rem;
        background: #fff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
    }
    .layout-panel h3 {
        font-size: 1rem;
        margin-bottom: 0.35rem;
        color: #1d4ed8;
    }
    .layout-panel p {
        color: #475569;
        font-size: 0.95rem;
        line-height: 1.4;
    }
    .layout-footer {
        padding: 1rem clamp(1rem, 4vw, 3.5rem);
        text-align: center;
        color: #475569;
        font-size: 0.85rem;
    }
    .layout-footer .muted {
        display: block;
        margin-top: 0.35rem;
        opacity: 0.75;
    }
</style>
