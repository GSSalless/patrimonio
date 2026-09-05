<?php $js_ver = @filemtime(__DIR__ . '/../assets/js/main.js') ?: time(); ?>
<script src="<?= base_url('assets/js/main.js') ?>?v=<?= $js_ver ?>"></script>
</body>
</html>
