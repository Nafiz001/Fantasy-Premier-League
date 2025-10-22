<!-- Global CSRF Token Interceptor -->
<!-- This ensures all fetch POST/PUT/DELETE requests include the CSRF token automatically -->
<script>
    // Intercept all fetch requests to add CSRF token
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        const [resource, config] = args;

        // Only intercept POST, PUT, DELETE, PATCH requests
        if (config && ['POST', 'PUT', 'DELETE', 'PATCH'].includes(config.method?.toUpperCase())) {
            // Get CSRF token from meta tag
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (token) {
                // Initialize headers if not present
                if (!config.headers) {
                    config.headers = {};
                }

                // Add CSRF token header
                config.headers['X-CSRF-TOKEN'] = token;
            }
        }

        // Call original fetch with modified config
        return originalFetch.apply(this, args);
    };
</script>
