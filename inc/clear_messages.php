<?php
function clear_messages() 
{
    unset($_SESSION['message']);
    unset($_SESSION['type']);
}
?>