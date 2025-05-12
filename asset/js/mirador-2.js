'use strict';

jQuery(document).ready(function(){
    Object.keys(miradors).forEach(viewerId => {
        Mirador(miradors[viewerId]);
    });
});
