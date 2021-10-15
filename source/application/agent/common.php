<?php

function encrypt_pwd($pwd){
    return md5(sha1(md5($pwd . '168tkss') . '168'));
}