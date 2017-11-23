<?php
$id = 19176;
?>

<a href="documents.php?doc_id=<?=$id;?>">Get This document</a>
<?php
session_start();
echo "Your name is " . $_SESSION['name'];