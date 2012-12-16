<?php 
    $link = ROOT_DOMAIN . "/reg/forgotpassword?uid={$this->uid}&token={$this->token}";
?>
<h3>Hi, <?= $this->name ?></h3>
<p>Someone has requested a password change on your email address, <?= $this->address ?></p>
<p>Click on this link <a href="<?= $link ?>"><?= $link ?></a> and change your password, or ignore this email if it was not you.</p>

<p>
<div>Regards</div>
<div>IKNOWU.com</div>
<a href="<?= ROOT_DOMAIN ?>"><?= ROOT_DOMAIN ?></a>
</p>