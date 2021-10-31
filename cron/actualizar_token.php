<?php
require_once '../clases/Token.php';
$_token = new Token;
$fecha = date('Y-m-d H:i');
echo $_token->actualizarTokens($fecha);