<?php

unset($_SESSION["id"]);
unset($_SESSION["userrol"]);

session_destroy();

header ("Location: ./index.php?content=message&alert=uitloggen");


?>