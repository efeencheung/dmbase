DM 项目初始化系统
========================
该系统主要是给初学者能快速开始一个稍微漂亮的Symfony项目，代码未经严格测试，谨慎使用在生产环境。由于为了清晰易懂，代码还是有一定的耦合度，日后逐渐优化。 

主要包含4个Bundle：
--------------
 * DmThemeBundle 提供一些后台模板，公用的Js库，公用样式，以及一些显示层的扩展。
 * DmSecurityBundle 主要是把Symfony的Access Control扩展，变成了一个可以从数据库读取配置的方式。
 * DmUserBundle 一个简单的用户管理模块，配合权限系统使用，可在此扩展。
 * DmGenerateBundle 从SensioGenerateBundle复制过来的，添加了中文的交互，一些中文参数，以及一些适应本系统的CRUD模板。

安装和配置：
--------------

一. 安装 [Composer](https://getcomposer.org/doc/00-intro.md)

二. 下载代码 git clone git@github.com:efeencheung/dmbase.git

三. 安装依赖包，如果在安装过程中没有正确的配置数据库信息，需手动编辑app/config/parameters.yml

```sh
cd dmbase
composer install
```
四. 导出前端公共文件

```sh
php app/console assets:install --symlink
```

五. 初始化数据库，数据初始化了三个用户，可使用normaluser/normaluser，admin/admin，superadmin/superadmin登录
```sh
php app/console doctrine:database:create
php app/console doctrine:schema:create
php app/console doctrine:fixtures:load
```

六. 配置服务器，开发环境，虚拟主机之类

七. 然后清空一下缓存，接下来就可以访问了

```sh
php app/console cache:clear
```
安装过程只在Mac下进行了测试，估计Linux下也没什么问题，有问题欢迎抛砖，Windows由于最近很少用了，有时间再写对应的安装过程

样式加载不了的童鞋配置一个虚拟主机，把本地域名直接指向到项目的web目录
