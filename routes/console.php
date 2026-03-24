<?php

// Auto-backup is handled by AutoBackupCheck middleware on every page load.
// The backup:auto command is available for manual use:
//   php artisan backup:auto          — creates backup if overdue
//   php artisan backup:auto --scan-only  — only scans for file changes
