import { Autoloader } from '../../jsOMS/Autoloader.js';

Autoloader.defineNamespace('omsApp.Modules');

/* global omsApp, mermaid */
omsApp.Modules.Workflow = class {
    /**
     * @constructor
     *
     * @since 1.0.0
     */
    constructor  (app)
    {
        this.app = app;
    };

    bind (id)
    {
        const mermaidElements = document.querySelectorAll('.mermaid');
        if (mermaidElements.length === 0) return; // Exit if no .mermaid elements are found

        mermaidElements.forEach((mermaidElement) => {
            const observer = new MutationObserver((mutationsList, observer) => {
                if (mermaidElement.offsetParent !== null) {
                    initializeMermaid();
                    observer.disconnect();
                }
            });

            observer.observe(mermaidElement, {
                attributes: true,
                attributeFilter: ['style', 'class'],
            });
        });

        function initializeMermaid() {
            if (typeof mermaid !== 'undefined') {
                mermaid.run({
                    querySelector: '.mermaid',
                    postRenderCallback: (id) => {
                        const svgs = d3.selectAll('.mermaid svg');
                        svgs.each(function () {
                            const svg = d3.select(this);
                            svg.html('<g>' + svg.html() + '</g>');
                            const inner = svg.select('g');
                            const zoom = d3.zoom().on('zoom', function (event) {
                                inner.attr('transform', event.transform);
                            });
                            svg.call(zoom);
                        });
                    },
                });
            }
        }
    };

    bindElement (chart)
    {
    };
};

window.omsApp.moduleManager.get('Workflow').bind();
