<?php if (!defined('FILE_PREFIX')) include '../../../error-forbidden.php'; ?>
<div class="js-page-view js-page-project">
    <div class="container">

        <div class="col-md-12 main">
            <h1 class="page-header">环境管理</h1>

            <div class="jumbotron">
                <div class="container">
                    <h1>创建环境</h1>
                    <p>创建环境之后，你只需要在本地添加HOSTS或者添加DNS解析，即可直接访问域名。</p>
                    <p><input type="url" class="input-domain" id="domain" placeholder="请输入一个你喜欢的域名，:D">
                        <a class="button button-3d button-box button-jumbo js-create-project-button" href="#" role="button"><i class="fa fa-plus"></i></a></p>
                </div>
            </div>

            <h2 class="sub-header">当前程序中的环境</h2>
            <div class="table-responsive">
                <table class="table table table-bordered js-table-project-list">
                    <thead>
                    <tr>
                        <th class="col-md-11">项目名称</th>
                        <th class="col-md-1">删除项目</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
