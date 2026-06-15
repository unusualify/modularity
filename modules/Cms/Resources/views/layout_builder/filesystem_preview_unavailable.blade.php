<div style="font-family: system-ui,-apple-system,'Segoe UI',sans-serif; max-width: 640px; margin: 4rem auto; padding: 2rem 2.5rem; border: 1px solid rgba(0,0,0,0.1); border-radius: 0.75rem; background: #fff;">
    <p style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.75rem;">Layout preview unavailable</p>
    <p style="margin-bottom: 0.75rem; color: rgba(0,0,0,0.7);">
        The shell draft preview merges unsaved database segments (head, body, footer) so it can render edits instantly. Filesystem layouts are rendered from static Blade views and slug overrides, which do not expose those database segments, so this preview cannot run.
    </p>
    <p style="margin-bottom: 0.75rem; color: rgba(0,0,0,0.7);">
        Switch the layout builder's Blade source back to <strong>db</strong> to use the preview, or visit the published route/Blade view to verify the filesystem template output.
    </p>

    @if($bladeViewName || $filesystemSlug)
        <div style="border-top: 1px solid rgba(0,0,0,0.08); padding-top: 0.75rem; margin-top: 1rem; color: rgba(0,0,0,0.65); font-size: 0.9rem;">
            @if($bladeViewName)
                <p style="margin: 0.25rem 0;"><strong>Blade view:</strong> {{ $bladeViewName }}</p>
            @endif
            @if($filesystemSlug)
                <p style="margin: 0.25rem 0;"><strong>Filesystem slug:</strong> {{ $filesystemSlug }}</p>
            @endif
            <p style="margin: 0.25rem 0; color: rgba(0,0,0,0.55);">Blade segments are read from the database, so they cannot be drafted while the source is Filesystem.</p>
        </div>
    @endif
</div>
