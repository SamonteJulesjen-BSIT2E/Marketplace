<?php
$dir = 'uploads';

if (is_writable($dir)) {
    echo "✅ The '$dir' folder is writable.";
} else {
    echo "❌ The '$dir' folder is NOT writable.";
}
?>