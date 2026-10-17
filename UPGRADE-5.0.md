# 5.0 升级与验证记录

## 依赖与兼容边界

| 版本 | Phinx | 依赖方式 |
| --- | --- | --- |
| 4.0.* | 0.13.4 | 内置 bundle |
| 4.1.* | 0.14.0 | 内置 bundle |
| 5.0 | ~0.16.12 | 普通 Composer 依赖 |

- PHP / php-64bit 最低 8.1，Composer runtime API 最低 2.1。
- Phinx 使用 `~0.16.12`（`>=0.16.12 <0.17.0`），允许 0.16 系列补丁更新，恢复普通 Composer 依赖；移除内置 vendor、scoper 构建、Composer replace 和独立工具 vendor-bin。
- 保留命令名、配置结构、`Zxin\Think\Phinx` 与旧 `HZEX\Phinx\Schema` 入口。
- 模板通过 `InstalledVersions::getInstallPath('robmorgan/phinx')` 定位，不再依赖本包安装路径。
- `topthink/think-helper` 最低 3.1.2，修正上游 ORM 允许解析到不兼容 helper 3.1.0/3.1.1 的依赖边界。
- `zxin/phinx-schema` 最低 2.1.1，桥接库明确引用 `dev-fix/symfony-7-compat` 开发分支。
- Config/Console 允许 `^6.4 || ^7.0`；PHP 8.1 使用 Symfony 6.4，Symfony 7 需要 PHP 8.2+。已发布 bridge 3.1.0 在 Console 7 下存在 `InputBridge::getParameterOption` 返回类型不兼容，因此本轮使用远程桥接修复分支，不能换回 3.1 后宣称支持 Symfony 7。
- 替换 Phinx 0.16.6 起弃用的 `Util::mapClassNameToFileName()`；使用等价的时间戳 + `Util::toSnakeCase()`，避免 ThinkPHP 将弃用通知转为异常而导致创建迁移失败。

## 开发分支联合测试路线

无需等桥接库发布稳定 tag 即可测试：

```sh
composer require nhzex/think-phinx:5.0.x-dev zxin/think-symfony-bridge:dev-fix/symfony-7-compat
```

消费者根项目必须显式要求桥接开发分支；库内的 dev 要求不会替消费者放宽稳定性门槛。不要将全局 `minimum-stability` 改为 `dev`，也无需 inline alias。Packagist 已能解析桥接分支，2026-10-17 安装的 source/dist reference 为 `5587fd9b063dd97f3a1ac48d852928c16172b02c`，没有用本地 path 依赖替代桥接库，也没有添加 VCS repository。开发分支会移动，请保留应用的 composer.lock 以固定实际测试版本；正式发布前再评估稳定版本依赖。

升级 CI 明确覆盖 PHP 8.1 + Symfony 6.4 的 latest / lowest、PHP 8.2 / 8.5 + Symfony 6.4，以及 PHP 8.2–8.5 + Symfony 7。每组以 `--with` 限定 Config/Console 系列，执行显式审计、两模式 autoload 和 MySQL 8 完整套件（含生成列），不能将本地 MariaDB 子集视为该 CI 的替代。

2026-10-17 本地 PHP 8.4.24 联调（桥接均为上述远程 reference）：

| Config / Console | 安装、审计、两模式 autoload | SQLite 生命周期 | MariaDB 11.8 子集 |
| --- | --- | --- | --- |
| 6.4.44 / 6.4.48 | 通过 | 1 测试 / 5 断言 | 排除生成列 schema 后，8 测试 / 69 断言通过 |
| 7.4.17 / 7.4.21 | 通过 | 1 测试 / 5 断言 | 排除生成列 schema 后，8 测试 / 69 断言通过 |

独立干净消费者保持默认 stable，并在根项目同时要求两个开发版本，安装与审计通过；除这两个明确要求的包外没有其他 dev 依赖。负向验证仅要求 think-phinx 时，Composer 因桥接分支不满足根项目 minimum-stability 而拒绝，证明上面的双依赖命令是必要的。预提交消费者验证使用本地 VCS 的 think-phinx 候选提交，桥接仍直接从 Packagist 下载远程 zip；它验证依赖元数据，不冒充尚未推送的新 think-phinx 的公开安装结果。新提交的远程消费者与 MySQL 8 CI 结果需在推送后另行记录。下文历史数据保留对应提交与依赖范围。

## 安全约束

Phinx 0.16.12 的宽松 CakePHP Database 依赖允许历史漏洞版本。此包显式要求 `cakephp/database:^5.2.15`，并排除 `>=5.3 <5.3.7`，避免已知受影响区间：

- [GHSA-vjqc-q4mp-2rvf / CVE-2026-79752](https://github.com/advisories/GHSA-vjqc-q4mp-2rvf)
- CVE-2026-77635 / Packagist PKSA-xyyq-wf7k-89mv（5.2.15 修复）

没有关闭 Composer 安全检查。安全审计无公告匹配仅说明当前已知公告结果，不代表完整代码安全审计。

## 已执行验证（原稳定依赖基线）

验证日期：2026-10-17。所有数据库操作均针对独立临时测试库。

| 检查 | 环境 / 结果 |
| --- | --- |
| PHP 源码与 fixture 语法检查 | PHP 8.4.24，通过 |
| `composer validate --strict` | 通过 |
| 最新依赖安装 | PHP 8.4.24，Phinx 0.16.12、ThinkPHP 8.1.4、ORM 4.0.51、schema 2.1.1、bridge 3.1.0，通过 |
| `composer test:autoload` | 新进程正常启动、预定义 `env()` 两种模式均通过 |
| PHP 8.1 platform 最低依赖求解 | 通过；Database 5.2.15、ThinkPHP 8.1.0、ORM 3.0.3、helper 3.1.2、Console/Config 6.4.0；增加 ORM 类加载后的独立进程 autoload 通过。修正后的真实 PHP 8.1 最低依赖 CI 已通过 9 测试 / 102 断言，显式审计无公告匹配 |
| 最终代码完整 CI 矩阵 | 提交 `339bff007bb3fe7dcdf0878cea4939dbc3028f46`：[run 38997087040](https://github.com/NHZEX/think-phinx/actions/runs/38997087040)，PHP 8.1–8.4 latest 与 PHP 8.1 lowest 五组全部通过 |
| 最终代码覆盖率 CI | 同一代码提交：[codecov run 38997086996](https://github.com/NHZEX/think-phinx/actions/runs/38997086996)，通过 |
| bridge 4.0 候选 + Console 7.4.21 联合验证 | 独立 path 镜像：autoload 两模式通过，8 测试 / 69 断言通过（排除 MariaDB 生成列不兼容项） |
| SQLite migrate → seed → rollback | 通过，验证表结构、记录值和迁移版本日志 |
| GitHub Actions：PHP 8.1.34 + MySQL 8.0.46 | [codecov run 38996737772](https://github.com/NHZEX/think-phinx/actions/runs/38996737772)，完整 9 测试 / 102 断言通过；autoload 两模式通过，包含生成列 schema 回归 |
| MariaDB 11.8 完整套件 | 9 测试 / 70 断言，1 项 schema 测试失败；其余通过 |

MariaDB 失败源于生成列的 `AS (...) VIRTUAL NOT NULL` / `STORED NOT NULL` SQL：MariaDB 不接受此语法，现有 schema fixture 本身也使用非空生成列。因此没有为了适配测试服务器修改业务 SQL，也没有将 MariaDB 结果等同于 MySQL 结果。真实 MySQL 8.0.46 的完整 CI 已通过 9 测试 / 102 断言，包括这项生成列回归。

生成列回归新增了 virtual/stored 两种列的插入计算值、更新计算值和存储类型断言；同时强化了模板内容、seed 写入记录和回滚清理断言。

### 最低依赖修正

[升级矩阵 run 38996899795](https://github.com/NHZEX/think-phinx/actions/runs/38996899795) 的 PHP 8.1–8.4 最新依赖组通过，但 PHP 8.1 最低组暴露上游契约不匹配：ORM 3.0.3 的 `toJson(int $options): string` 与 helper 3.1.0 的无类型 `Jsonable::toJson($options = 0)` 不兼容。

核对官方源码，[helper v3.1.1](https://github.com/top-think/think-helper/blob/v3.1.1/src/contract/Jsonable.php) 仍是旧签名，[v3.1.2](https://github.com/top-think/think-helper/blob/v3.1.2/src/contract/Jsonable.php) 首次提供匹配的类型签名。因此只收紧已存在的 helper 依赖为 `^3.1.2`，并在独立 autoload 回归中显式加载 `think\Model`。没有跳过最低测试或修改第三方 vendor。

同时核对 ORM 2.0.30、2.0.62 的 `toJson(int ...): string` 和 `toArray(): array` 签名，与 helper 3.1.2 契约匹配；这只是相关接口核对，不代表已对所有历史框架组合完成运行认证。

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
3. 普通 Composer 安装必须提供符合 `~0.16.12` 的稳定版 Phinx 与旧 Schema 入口。

CakePHP 5 仍定义 `DS`、`CAKE_DATE_RFC7231` 两个全局常量；测试会报告它们，不声称完全没有全局符号变化。CakePHP Utility 自身的 Inflector 初始化也仍存在，恢复普通依赖不意味着所有静态初始化都消失。

## 历史 CI 与未验证范围

CI 已在代码提交 `339bff007bb3fe7dcdf0878cea4939dbc3028f46` 完成 PHP 8.1–8.4 最新依赖、PHP 8.1 最低依赖五组验证，均使用 MySQL 8；对应升级矩阵 run 38997087040 和覆盖率 run 38997086996 均成功。这些历史运行只证明当时 bridge 3.1 / Symfony 6.4 基线，不代表当前开发分支联调已通过 CI。

早期 codecov 运行的 setup-php 环境设置了 `COMPOSER_NO_AUDIT=1`，其安装成功不能单独证明审计执行。升级验证工作流随后加入了显式 `composer audit`，最终五组矩阵全部通过；[PHP 8.1 lowest job 117291913920](https://github.com/NHZEX/think-phinx/actions/runs/38997087040/job/117291913920) 明确记录审计无公告匹配、两种 autoload 模式通过、9 测试 / 102 断言通过。

当前实际运行认证覆盖 ThinkPHP 8.1。独立强制求解 ThinkPHP `~6.1` 已被 Composer 安全公告拦截，未关闭安全检查；因此没有将其放入常规 CI 的成功矩阵。原有框架版本约束保留不等同于安全认证。ThinkPHP 8.0 尚未单独认证。

尚未验证 PostgreSQL、SQL Server、实际业务项目已有的所有迁移、32 位 PHP（明确不支持）、已发布 bridge 3.1 下的 Symfony 7，以及 Symfony Config/Console 8（当前约束不允许；其余 Symfony 组件可能由上游合法解析到 8）。不发布 release，也不改 master。

## 本地复现

```sh
composer install
composer validate --strict
composer test:autoload
composer test -- --filter SqliteLifecycleTest
# 设置 TESTS_DB_MYSQL_* 指向专用测试库后执行：
composer test
```

测试需要 `pdo_sqlite`；完整套件还需要 MySQL 和 `pdo_mysql`，通过 `TESTS_DB_MYSQL_*` 环境变量配置连接（见 `phpunit.xml.dist`）。完整套件会删除并重建测试数据库，禁止使用生产数据库连接。
