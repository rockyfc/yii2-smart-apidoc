

<div id="w22-error-0" class="alert-danger alert fade in">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>

    <?php foreach ($errors as $k=>$error): $i=$k+1;?>
        <p><?=$i?>. <?=$error?></p>
    <?php endforeach;?>


</div>