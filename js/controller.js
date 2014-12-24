/**
 * Loader: Stage 2
 */

(function () {

    mor.stage2 = function () {
        // Render skeleton
        mor.loadSkeleton();

    };

    mor.loadSkeleton = function () {
        document.body.innerHTML = mor.resources.template['mor.skeleton'];
    };

    // Load templates
    mor.loadTemplates();

})();