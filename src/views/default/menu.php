<div class="col-lg-3">

    <ul id="myTab" class="nav nav-tabs" style="border:none;">
        </li>
        <li class="active" onClick="menutab('#fc-api')">
            <a href="#aa" data-toggle="tab">
                接口列表
            </a>
        </li>

        <li onClick="menutab('#fc-model')">
            <a href="#bb" data-toggle="tab">
                对象列表
            </a>
        </li>
    </ul>

    <div class="fc-menu border" id="fc-api" style="border: 1px solid #ddd">
        <!--                <p><h4>接口列表</h4></p>
        -->
        <ul class="nav ">
            <?php foreach ($apiList as $controllerId => $controller): ?>
                <?php if (!$controller['isDisabled']): ?>
                    <li>
                        <a href="#<?= md5($controllerId) ?>">
                            <?php if ($controller['isDeprecated']) {
                                echo "<s>";
                            } ?>

                            <h5><?= !empty($controller['title']) ? "【" . $controller['title'] . "】" : null ?></h5>

                            <?php if ($controller['isDeprecated']) {
                                echo "</s>";
                            } ?>

                        </a>
                        <ul>
                            <?php foreach ($controller['actionsDoc'] as $action): ?>
                                <?php if ($action != null): ?>
                                    <li><a href="#<?= md5($action['route']) ?>">
                                    <?php if ($controller['isDeprecated'] or $action['isDeprecated']): ?>
                                        <s><?= $action['title'] ?></s>
                                    <?php else: ?>
                                    <?= $action['title'] ?>
                                <?php endif; ?>
                                </a></li>
                                <?php endif; ?>
                            <?php endforeach; ?>

                        </ul>
                    </li>

                <?php endif;endforeach; ?>
        </ul>
    </div>

    <div class="fc-menu hidden" id="fc-model" style="border: 1px solid #ddd">
        <!--                <p><h4>对象列表</h4></p>
        -->
        <ul class="nav ">
            <?php if ($modelsDoc) foreach ($modelsDoc as $model): ?>
                <li>
                    <a href="#<?= md5($model['name']) ?>"><?= $model['name'] ?></a>
                </li>

            <?php endforeach; ?>
        </ul>
    </div>


    <script>
        function menutab(id) {
            $('.fc-menu').addClass('hidden')
            $(id).removeClass('hidden');
        }
    </script>
</div>