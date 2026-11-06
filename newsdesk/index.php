<?php
// Visitors who open the site root are forwarded to the public folder.
header('Location: public/', true, 302);
exit;
