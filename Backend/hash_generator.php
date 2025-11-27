<?php
echo "Admin Password Hash (adminpass): " . password_hash('adminpass', PASSWORD_DEFAULT) . "<br><br>";
echo "Professor Password Hash (profpass): " . password_hash('profpass', PASSWORD_DEFAULT) . "<br><br>";
echo "Student Password Hash (studentpass): " . password_hash('studentpass', PASSWORD_DEFAULT) . "<br><br>";
?>