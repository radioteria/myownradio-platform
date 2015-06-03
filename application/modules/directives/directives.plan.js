/**
 * Created by Roman on 03.06.15.
 */
(function () {
    var module = angular.module("application");

    module.directive("morUpgrade", [function () {
        return {
            scope: {
                morUpgrade: "="
            },
            template: '<a target="_blank" class="submit" href="/subscribe?plan_id={{ morUpgrade }}"><translate>LABEL_APPLY</translate></a>',
            restrict: "A"
        }
    }]);

})();