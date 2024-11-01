(function($) {
    
    "use strict";



  document.addEventListener('DOMContentLoaded', function() {
        const copyButtons = document.querySelectorAll('.copy-shortcode');

        copyButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const postId = this.getAttribute('data-id');
                const inputField = document.getElementById('shortcode-input-' + postId);

                inputField.select();
                inputField.setSelectionRange(0, 99999); // For mobile devices

                document.execCommand('copy');

                // Optionally, provide visual feedback or a message to the user
                alert('Shortcode copied to clipboard');
            });
        });
    });

  })(window.jQuery);