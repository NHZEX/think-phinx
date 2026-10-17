# 5.0 升级与验证记录

## 依赖与兼容边界

- PHP / php-64bit 最低 8.1，Composer runtime API 最低 2.1。
- Phinx 精确使用 0.16.12，恢复普通 Composer 依赖；移除内置 vendor、scoper 构建、Composer replace 和独立工具 vendor-bin。
- 保留命令名、配置结构、`Zxin\Think\Phinx` 与旧 `HZEX\Phinx\Schema` 入口。
- 模板通过 `InstalledVersions::getInstallPath('robmorgan/phinx')` 定位，不再依赖本包安装路径。
- `zxin/phinx-schema` 最低 2.1.1，`zxin/think-symfony-bridge` 最低 3.1。
- 目前 Config/Console 限定 Symfony 6.4。实测 bridge 3.1.0 在 Console 7.4.21 下发生 `InputBridge::getParameterOption ... must be compatible with ... : mixed` 致命错误，虽其 Composer 声明允许 7.x，仍不能直接开放。桥接库 4.0 候选已在独立 path 依赖环境完成联合验证；因尚未发布 4.0 稳定版，此包继续依赖已发布的 bridge 3.1，不引用不存在的 tag。
- 替换 Phinx 0.16.6 起弃用的 `Util::mapClassNameToFileName()`；使用等价的时间戳 + `Util::toSnakeCase()`，避免 ThinkPHP 将弃用通知转为异常而导致创建迁移失败。

## 安全约束

Phinx 0.16.12 的宽松 CakePHP Database 依赖允许历史漏洞版本。此包显式要求 `cakephp/database:^5.2.15`，并排除 `>=5.3 <5.3.7`，避免已知受影响区间：

- [GHSA-vjqc-q4mp-2rvf / CVE-2026-79752](https://github.com/advisories/GHSA-vjqc-q4mp-2rvf)
- CVE-2026-77635 / Packagist PKSA-xyyq-wf7k-89mv（5.2.15 修复）

没有关闭 Composer 安全检查。安全审计无公告匹配仅说明当前已知公告结果，不代表完整代码安全审计。

## 已执行验证

验证日期：2026-10-17。所有数据库操作均针对独立临时测试库。

| 检查 | 环境 / 结果 |
| --- | --- |
| PHP 源码与 fixture 语法检查 | PHP 8.4.24，通过 |
| `composer validate --strict` | 通过 |
| 最新依赖安装 | PHP 8.4.24，Phinx 0.16.12、ThinkPHP 8.1.4、ORM 4.0.51、schema 2.1.1、bridge 3.1.0，通过 |
| `composer test:autoload` | 新进程正常启动、预定义 `env()` 两种模式均通过 |
| PHP 8.1 platform 最低依赖求解 | 通过；Database 5.2.15、ThinkPHP 8.1.0、ORM 3.0.3、Console/Config 6.4.0；独立进程 autoload 通过。最低依赖组合的实际运行等待专用矩阵结果 |
| bridge 4.0 候选 + Console 7.4.21 联合验证 | 独立 path 镜像：autoload 两模式通过，8 测试 / 69 断言通过（排除 MariaDB 生成列不兼容项） |
| SQLite migrate → seed → rollback | 通过，验证表结构、记录值和迁移版本日志 |
| GitHub Actions：PHP 8.1.34 + MySQL 8.0.46 | [codecov run 38996737772](https://github.com/NHZEX/think-phinx/actions/runs/38996737772)，完整 9 测试 / 102 断言通过；autoload 两模式通过，包含生成列 schema 回归 |
| MariaDB 11.8 完整套件 | 9 测试 / 70 断言，1 项 schema 测试失败；其余通过 |

MariaDB 失败源于生成列的 `AS (...) VIRTUAL NOT NULL` / `STORED NOT NULL` SQL：MariaDB 不接受此语法，现有 schema fixture 本身也使用非空生成列。因此没有为了适配测试服务器修改业务 SQL，也没有将 MariaDB 结果等同于 MySQL 结果。真实 MySQL 8.0.46 的完整 CI 已通过 9 测试 / 102 断言，包括这项生成列回归。

生成列回归新增了 virtual/stored 两种列的插入计算值、更新计算值和存储类型断言；同时强化了模板内容、seed 写入记录和回滚清理断言。

## 独立消费者依赖成本

在 PHP 8.4.24、ThinkPHP 8.1.4 上创建独立消费者，使用 `--no-dev` 测量（不含 PHPUnit 等开发工具）。字节数是文件逻辑大小，分配大小受文件系统影响。

| 指标 | 仅 ThinkPHP 基线 | 加入 think-phinx 5.0 | 增量 |
| --- | ---: | ---: | ---: |
| Composer 安装包数 | 9 | 32 | +23 |
| vendor 文件数 | 386 | 1,149 | +763 |
| vendor 文件逻辑字节 | 2,134,309 | 6,454,690 | +4,320,381（约 4.12 MiB） |
| 文件系统分配字节 | 3,080,192 | 9,289,728 | +6,209,536 |

完整生产 vendor 的逻辑大小约 6.16 MiB；没有安装完整 CakePHP 框架，仅所需组件。旧 4.1 的正常安装被已知安全公告阻断，因此没有关闭安全检查来取得一个误导性的旧/新对照。上述增量是相对于纯 ThinkPHP 基线，不能解读为相对于 4.1 的增长。

## Autoload 副作用

`tests/check-autoload.php` 在独立 PHP 进程中检查：

1. ThinkPHP 启动 helper 后，`env()` 必须来自已安装的 ThinkPHP；预定义的 `env()` 必须保持不变。
2. Composer 加载及公开 Phinx / Schema 类加载不得引入来自 Phinx/CakePHP 包目录的全局函数。
3. 普通 Composer 安装必须提供 Phinx 0.16.12 与旧 Schema 入口。

CakePHP 5 仍定义 `DS`、`CAKE_DATE_RFC7231` 两个全局常量；测试会报告它们，不声称完全没有全局符号变化。CakePHP Utility 自身的 Inflector 初始化也仍存在，恢复普通依赖不意味着所有静态初始化都消失。

## CI 与未验证范围

CI 配置 PHP 8.1–8.4 的最新依赖、PHP 8.1 的最低依赖及 MySQL 8。PHP 8.1.34 + MySQL 8.0.46 已由上述 codecov CI 实际验证；其余专用矩阵结果应与具体提交对应。

该次 codecov 运行的 setup-php 环境设置了 `COMPOSER_NO_AUDIT=1`，不能把其安装成功解读为 CI 已执行安全审计。新的升级验证工作流加入显式 `composer audit` 步骤；本地 Composer 审计结果与 CI 功能测试结果分别记录。

当前实际运行认证覆盖 ThinkPHP 8.1。独立强制求解 ThinkPHP `~6.1` 已被 Composer 安全公告拦截，未关闭安全检查；因此没有将其放入常规 CI 的成功矩阵。原有框架版本约束保留不等同于安全认证。ThinkPHP 8.0 尚未单独认证。

尚未验证 PostgreSQL、SQL Server、实际业务项目已有的所有迁移、32 位 PHP（明确不支持）、已发布 bridge 3.1 下的 Symfony 7/8（当前 Console 约束不允许；候选桥接的 7.x 联调如上）。不发布 release，也不改 master。

## 本地复现

```sh
composer install
composer validate --strict
composer test:autoload
composer test -- --filter SqliteLifecycleTest
# 设置 TESTS_DB_MYSQL_* 指向专用测试库后执行：
composer test
```

完整套件会删除并重建测试数据库，禁止使用生产数据库连接。
