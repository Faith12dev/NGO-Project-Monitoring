            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/main.js?v=<?php echo time(); ?>"></script>
    <script>
        // Verify confirmDelete function is available
        if (typeof confirmDelete === 'undefined') {
            console.error('ERROR: confirmDelete function not found!');
            // Define it as fallback
            window.confirmDelete = function(message) {
                return confirm(message || 'Are you sure?');
            };
        } else {
            console.log('✓ confirmDelete function loaded successfully');
        }
    </script>
</body>
</html>
