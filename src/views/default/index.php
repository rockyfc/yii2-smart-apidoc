<? /*=print_r($apiList,1);exit; */ ?>
<div class="container">
    <div class="row">

        <?= $this->render('menu.php', ['apiList' => $apiList, 'modelsDoc' => $modelsDoc]) ?>

        <div class="col-lg-9">
            <?= $this->render('message.php', ['errors' => $errors]) ?>

            <h1>前言</h1>
            <hr/>
            <?php
            if (!empty($smartReadmeFile)) {
                echo str_ireplace("\n", '<br/>', $smartReadmeFile);
            }
            echo "<br/>";
            echo "<br/>";
            if (!empty($readmeFileContent)) {
                echo str_ireplace("\n", '<br/>', $readmeFileContent);
            }
            ?>


            <hr/>
            <h1>接口列表</h1>
            <?php foreach ($apiList as $controllerId => $controller): ?>
                <?php if (!$controller['isDisabled']): ?>

                    <?php foreach ($controller['actionsDoc'] as $action): ?>
                        <a id="<?= md5($action['route']) ?>"></a>
                        <br/>
                        <?php if ($action != null): ?>
                            <p>
                            <h3><a id="<?= md5($action['route']) ?>">
                                    <?php if ($controller['isDeprecated'] or $action['isDeprecated']) {
                                        echo "<s>";
                                    } ?>

                                    <?= $action['title'] ?><?= !empty($controller['title']) ? "【" . $controller['title'] . "】" : null ?>

                                    <?php if ($controller['isDeprecated'] or $action['isDeprecated']) {
                                        echo "</s><small><span class='text-danger'>【已过期，不建议使用】</span></small>";
                                    } ?>
                                </a></h3>

                            说明：<?= $action['description'] ?><br/>
                            请求方式：<?= implode('，', $action['method']) ?><br/>
                            路由：<?= $action['route'] ?><br/>
                            <strong>Url参数：</strong>
                            <?php if (isset($action['queryParams'])): ?>
                                <table class="table table-bordered ">
                                    <thead>
                                    <tr>
                                        <th nowrap>&nbsp;名称</th>
                                        <th nowrap>&nbsp;类型</th>
                                        <th nowrap>&nbsp;是否必须</th>
                                        <th nowrap>&nbsp;可选值</th>
                                        <th nowrap>&nbsp;默认</th>
                                        <th nowrap style="width:40%;">&nbsp;描述</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($action['queryParams'] as $column => $rule): ?>
                                        <tr>
                                            <td><?= $column ?></td>
                                            <td><?= $rule['type'] ?></td>
                                            <td><?= ($rule['required'] ? '<span style="color:red;">true</span>' : 'false') ?></td>
                                            <td><?= is_array($rule['range']) ? implode(', ', $rule['range']) : $rule['range']; ?></td>
                                            <td><?= $rule['default'] ?></td>
                                            <td><?= $rule['comment'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>

                                    </tbody>
                                </table>
                                <br/>
                            <?php else: ?>
                                无<br/><br/>
                            <?php endif; ?>

                            <?php if (in_array('GET', $action['method']) OR in_array('POST', $action['method']) OR in_array('PUT', $action['method']) OR in_array('PATCH', $action['method'])): ?>

                                <strong><?= implode('，', $action['method']) ?>输入参数：</strong>
                                <?php if (isset($action['input'])): ?>
                                    <table class="table table-bordered ">
                                        <thead>
                                        <tr>
                                            <th nowrap>&nbsp;名称</th>
                                            <th nowrap>&nbsp;类型</th>
                                            <th nowrap>&nbsp;是否必须</th>
                                            <th nowrap>&nbsp;可选值</th>
                                            <th nowrap>&nbsp;默认</th>
                                            <th nowrap style="width:40%;">&nbsp;描述</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($action['input'] as $column => $rule): ?>
                                            <tr>
                                                <td><?= $column ?></td>
                                                <td><?= $rule['type'] ?></td>
                                                <td><?= ($rule['required'] ? '<span style="color:red;">true</span>' : 'false') ?></td>
                                                <td><?= is_array($rule['range']) ? implode(', ', $rule['range']) : $rule['range']; ?></td>
                                                <td><?= $rule['default'] ?></td>
                                                <td><?= $rule['comment'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>

                                        </tbody>
                                    </table>
                                    <br/>
                                <?php endif; ?>

                            <?php endif; ?>

                            <strong>输出参数：</strong>
                            <?php if (isset($action['output'])): ?>
                                <table class="table table-bordered ">
                                    <thead>
                                    <tr>
                                        <th>&nbsp;名称</th>
                                        <th>&nbsp;类型</th>
                                        <th>&nbsp;可选值</th>
                                        <th style="width:40%;">&nbsp;描述</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($action['output'] as $column => $rule): ?>
                                        <tr>
                                            <td>&nbsp;<?= $column ?></td>
                                            <td>&nbsp;<?= $rule['type'] ?></td>
                                            <td>
                                                &nbsp;<?= is_array($rule['range']) ? implode(', ', $rule['range']) : $rule['range']; ?></td>
                                            <td>&nbsp;<?= $rule['comment'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>

                                    </tbody>
                                </table>
                                <br/>
                            <?php endif; ?>

                            </p>
                            <hr/>
                        <?php endif; ?>

                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>


            <hr/>
            <h1>对象列表</h1>
            <?php if ($modelsDoc): ?>

                <?php foreach ($modelsDoc as $model): ?>
                    <a id="<?= md5($model['name']) ?>"></a>
                    <br/> <br/>
                    <strong>对象名称：<?= $model['name'] ?></strong>
                    <br/>
                    说明：<?= $model['title'] ?><br/>
                    <table class="table table-bordered ">
                        <thead>
                        <tr>
                            <th>&nbsp;名称</th>
                            <th>&nbsp;类型</th>
                            <th>&nbsp;可选值</th>
                            <th style="width:40%;">&nbsp;描述</th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php foreach ($model['comment'] as $column => $rule): ?>
                            <tr>
                                <td>&nbsp;<?= $column ?></td>
                                <td>&nbsp;<?= $rule['type'] ?></td>
                                <td><?= is_array($rule['range']) ? implode(',<br/> ', $rule['range']) : $rule['range']; ?></td>
                                <td>&nbsp;<?= $rule['comment'] ?></td>
                            </tr>
                        <?php endforeach; ?>

                        </tbody>
                    </table>
                    <br/>
                <?php endforeach; ?>

            <?php endif; ?>
        </div>
    </div>
</div>

