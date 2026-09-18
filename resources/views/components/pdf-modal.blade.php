<div class="modal-layer" data-pdf-modal hidden>
    <div class="modal-backdrop" data-modal-close></div>
    <section class="pdf-modal" role="dialog" aria-modal="true" aria-labelledby="pdf-modal-title">
        <header class="modal-header">
            <div>
                <span class="eyebrow">Vista previa del documento</span>
                <h2 id="pdf-modal-title" data-modal-title>Documento</h2>
            </div>
            <div class="modal-actions">
                <a class="btn btn-ghost btn-compact" data-modal-external href="#" target="_blank" rel="noopener">
                    Abrir en Drive
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 5h5v5M11 13l8-8M19 14v5H5V5h5"/></svg>
                </a>
                <button class="icon-btn" type="button" data-modal-close aria-label="Cerrar visor">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>
        </header>
        <div class="pdf-frame-wrap">
            <div class="frame-loader" data-frame-loader>
                <span class="loader"></span>
                <span>Cargando documento desde Drive…</span>
            </div>
            <iframe data-pdf-frame title="Visor de documento PDF" allow="autoplay" loading="lazy"></iframe>
        </div>
    </section>
</div>
