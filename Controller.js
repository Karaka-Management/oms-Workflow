import { Autoloader } from '../../jsOMS/Autoloader.js';

Autoloader.defineNamespace('jsOMS.Modules');

jsOMS.Modules.Workflow = class {
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
        mermaid.initialize({startOnLoad:true});
    };

    bindElement (chart)
    {
    };
};

window.omsApp.moduleManager.get('Workflow').bind();
