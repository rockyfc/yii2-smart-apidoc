<? /*=print_r($apiList,1);exit; */ ?>
<div class="container">
    <div class="row">
        <div class="col-lg-3">

            <p><h4>前言</h4></p>

            <ul id="myTab" class="nav nav-tabs" style="border:none">
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
-->                <ul class="nav ">
                    <?php foreach ($apiList as $controllerId => $controller): ?>
                        <li><a href="#<?= md5($controllerId) ?>"><?= $controllerId ?><?= !empty($controller['title'])?"【".$controller['title']."】":null ?></a>
                            <ul>
                                <?php foreach ($controller['actionList'] as $action): ?>
                                    <?php if($action != null ): ?>
                                        <li><a href="#<?= md5($action['route'])?>"><?= $action['title']?></a></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>

                            </ul>
                        </li>

                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="fc-menu hidden" id="fc-model" style="border: 1px solid #ddd">
<!--                <p><h4>对象列表</h4></p>
-->                <ul class="nav ">
                    <?php foreach ($modelsDoc as $model): ?>
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


        <div class="col-lg-9">
            <h1>前言</h1>
            <hr/>
            <?php if(!empty($readmeFileContent)){
                echo str_ireplace("\n", '<br/>', $readmeFileContent);
            }

            ?>


            <hr/>
            <h1>接口列表</h1>
            <?php foreach ($apiList as $controllerId => $controller): ?>
                <?php foreach ($controller['actionList'] as $action): ?>
                    <a id="<?= md5($action['route']) ?>"></a>
                    <br/>
                    <?php if ($action != null): ?>
                        <p>
                        <h3><a id="<?= md5($action['route']) ?>"><?= $action['title'] ?><?= !empty($controller['title'])?"【".$controller['title']."】":null ?></a></h3>

                        说明：<?= $action['description'] ?><br/>
                        请求方式：<?= implode('，',$action['method']) ?><br/>
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

                        <?php if ( in_array('POST',$action['method']) OR in_array('PUT',$action['method']) OR in_array('PATCH',$action['method']) ): ?>

                            <strong><?= implode('，',$action['method']) ?>输入参数：</strong>
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

