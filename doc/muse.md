
input : callable 变成解析型
- 允许修改 __call 或 __get
- 通用，注入
- 区分cli 和 http等
- input 和 output本身是容器


当前实例？
- \nx\app::$instance 味道不好
- 都应该从$this 出发？
- 额外的对象怎样处理？
- app本身应该为容器
- 配置应该为容器，注入app？

容器初始化
- 现在是从文件
  - 统一管理，静态化，定时更新
- 如果是容器，如何初始化？
  - 代码builder 相当于固化，本质等同本地文件
- 构建容器时从文件中加载
  - 支持多文件加载，可覆盖
  - 也可以诸如从redis中加载等等
    - 固死写法 否则先有鸡或先有蛋
  - 代码和文件（或其他）不冲突，可以提供代码助手

空架子？
- 所有部分都是容器
  - 依赖注入解决问题，如注入 input-http input-cli output-rest
  - 通过 trait 解决代码提示
    - 通过初始化trait注入对象
  - 通过 变量 解决注入依赖

事件监听机制
- cors
- error
- response
- request
- log
- control
- route
- 这些都可以有事件相应和回调
  - 或者本身来讲都是事件

支持 .phpstorm.meta.php
https://www.jetbrains.com/help/phpstorm/ide-advanced-metadata.html

所有方法都应该是对象的自身被()
即，每个方法都应该是同名对象