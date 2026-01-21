<?php
echo "✅ PHP funciona correctamente!<br><br>";
echo "<strong>Versión PHP:</strong> " . phpversion() . "<br>";
echo "<strong>Directorio actual:</strong> " . __DIR__ . "<br>";
echo "<strong>Usuario PHP:</strong> " . get_current_user() . "<br>";
echo "<strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
?>
