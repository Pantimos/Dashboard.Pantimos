<?php if (!defined('FILE_PREFIX')) include '../../../error-forbidden.php'; ?>
<nav class="navbar navbar-masthead navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">首页</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse" aria-expanded="false">
            <ul class="nav navbar-nav">
                <li class="active"><a href="#intro" class="application-intro"><i class="fa fa-lightbulb-o"></i>功能概览</a></li>
                <li><a href="#"><i class="fa fa-book"></i>使用方法</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-list"></i>功能列表 <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="#"><i class="fa fa-connectdevelop"></i>创建环境</a></li>
                        <li class="divider"></li>
                        <li class="dropdown-header">模拟数据</li>
                        <li><a href="#"><i class="fa fa-medium"></i>接口模拟</a></li>
                        <li><a href="#"><i class="fa fa-picture-o"></i>图片模拟</a></li>

                        <li><a href="#"><i class="fa fa-github"></i>GitHub</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-question"></i>关于程序 <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="#"><i class="fa fa-tachometer"></i>组件状态</a></li>
                        <li class="divider"></li>
                        <li class="dropdown-header">在线资源</li>
                        <li><a href="#"><i class="fa fa-github"></i>GitHub</a></li>
                    </ul>
                </li>
            </ul>
            <form class="navbar-form navbar-right">
                <div class="form-group">
                    <input type="text" placeholder="请输入要执行的命令..." class="form-control">
                </div>
                <button type="submit" class="btn btn-success">执行</button>
            </form>
        </div>
    </div>
</nav>
