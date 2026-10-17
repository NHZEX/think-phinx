# 升级到 5.0

## 安装与兼容范围

Think-Phinx 5.0 尚未发布稳定 tag，试用命令为：

```sh
composer require nhzex/think-phinx:5.0.x-dev
```

桥接库自动解析为稳定版 `^4.0`。仅本包需要显式使用开发版本，无需降低项目的 `minimum-stability`。应用应提交 `composer.lock`，固定实际使用的依赖版本。

| 依赖 | 5.0 要求 |
| --- | --- |
| PHP | 64 位，8.1+ |
| Composer | 2.1+ |
| Phinx | `~0.16.12`，允许 0.16 系列补丁更新 |
| Symfony Config / Console | `^6.4 \|\| ^7.0`；PHP 8.1 使用 6.4 |
| Symfony 7 | PHP 8.2+、桥接库 4.0+ |
| ThinkPHP | 8.0 / 8.1；已验证 8.1 |
| think-orm | `^3.0 \| ^4.0` |
| think-helper / phinx-schema | `^3.1.2` / `^2.1.1` |

5.0 不再支持 ThinkPHP 6 和 ORM 2，升级前请先迁移至受支持的版本。ThinkPHP 8.0 尚未完成独立运行验证。PostgreSQL、SQL Server 和项目自有迁移仍需自行测试。32 位 PHP、Symfony Config/Console 8 不在支持范围内。

## 从 4.x 升级

4.0 / 4.1 分别内置 Phinx 0.13.4 / 0.14.0；5.0 使用普通 Composer 依赖，移除内置 vendor、scoper 构建和 Composer replace，依赖由应用统一解析。

1. 备份数据库，在测试环境更新 Composer 依赖。
2. 检查自有迁移对 Phinx 0.16 API 的兼容性，验证 migrate、seed 和 rollback。
3. 命令未出现时运行 `php think service:discover`。

命令名、配置结构、`Zxin\Think\Phinx` 和旧 `HZEX\Phinx\Schema` 入口保持可用。迁移模板从已安装的 Phinx 包定位。

独立消费者使用 ThinkPHP 8.1、Symfony 7、桥接 4.0 时，生产安装共 32 个包，不包含 PHPUnit 等开发工具；只引入所需 CakePHP 组件，不安装完整框架。

### 安全依赖边界

CakePHP Database 要求 `^5.2.15`，并排除 `>=5.3 <5.3.7`，避开 [CVE-2026-79752](https://github.com/advisories/GHSA-vjqc-q4mp-2rvf) 和 CVE-2026-77635 / PKSA-xyyq-wf7k-89mv 的已知受影响版本。保持 Composer 安全检查开启，升级后运行 `composer audit`；无公告匹配仅反映当前已知漏洞信息。

### Autoload 与全局符号

独立进程测试覆盖普通启动和预定义 `env()` 两种情况：ThinkPHP 的 helper 保持可用，已有的 `env()` 不被替换，Phinx / CakePHP 不额外引入全局函数。

CakePHP 5 仍定义 `DS`、`CAKE_DATE_RFC7231` 两个全局常量，也保留 Inflector 的静态初始化。

### 数据库差异

MySQL 8 完整测试包含 virtual / stored 生成列的计算值和存储类型验证。当前生成列 fixture 使用 `AS (...) VIRTUAL NOT NULL` / `STORED NOT NULL`，MariaDB 11.8 不接受该语法；MariaDB 子集测试不包含此项。使用 MariaDB 的项目应单独检查生成列定义。

## 验证范围

升级 CI 共八组：PHP 8.1 + Symfony 6.4 的 latest / lowest、PHP 8.2 / 8.5 + Symfony 6.4，以及 PHP 8.2–8.5 + Symfony 7。每组执行依赖安装、审计、两模式 autoload 和 MySQL 8 完整套件。

本地 PHP 8.4 + 桥接 4.0 验证覆盖 Symfony 6.4 / 7：审计、两模式 autoload、SQLite 生命周期（1 测试 / 5 断言）及 MariaDB 子集（8 测试 / 69 断言）。独立消费者仅允许 Think-Phinx 开发版本，稳定桥接安装及接口检查通过。

桥接 4.0 稳定版的 [八组 CI 矩阵](https://github.com/NHZEX/think-phinx/actions/runs/39000161548) 与 [覆盖率检查](https://github.com/NHZEX/think-phinx/actions/runs/39000161563) 均已通过，覆盖 PHP 8.1–8.5、Symfony 6.4 / 7 和 MySQL 8。

## 本地验证

```sh
composer install
composer validate --strict
composer audit
composer test:autoload
composer test -- --filter SqliteLifecycleTest
# 设置 TESTS_DB_MYSQL_* 指向专用测试库后执行：
composer test
```

SQLite 测试需要 `pdo_sqlite`；完整套件还需要 MySQL 和 `pdo_mysql`。连接通过 `TESTS_DB_MYSQL_*` 环境变量配置，参见 `phpunit.xml.dist`。完整套件会删除并重建测试数据库，禁止使用生产数据库连接。
