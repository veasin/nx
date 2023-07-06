```
        ___  ___
     __ \  \/  /
    /  \ \  \ /
   /  / \ \  \
  /  /\  / \  \   php.nx.cn
 /__/  \/__/\__\  vea, 2020
```

# ![nx](./logo.png) is **"a next-gen framework"**
  php 8.2+
  
    small,fast,emmmm...
  
    使用php语言中的trait实现在代码编写时的加载扩充，而不是运行时的扩充
  
    小框架，可以随意扩充

# resources

- [cache-redis](https://github.com/urn2/nx-cache-redis) 基于redis的cache简单封装
- [db-pdo](https://github.com/urn2/nx-db-pdo) 数据库访问，提供ar
- [filter-from](https://github.com/urn2/nx-filter-from) 过滤器，可书写混乱规则
- [log](https://github.com/urn2/nx-log) 日志，支持同时多个writer
- [log-cli](https://github.com/urn2/nx-log-cli) 在shell中输出，提供颜色封装
- [model](https://github.com/urn2/nx-model) mvc的m封装
- [network-context](https://github.com/urn2/nx-network-context) 网络请求封装
- [queue-amqp](https://github.com/urn2/nx-queue-amqp) 简单的amqp队列封装

# feature
- [x] trait,trait,trait!!!
- [x] 支持命名空间
- [x] 支持composer
- [x] 尽可能支持psr
- [x] 尽可能兼容next
- [x] 支持调度 使用route并闭包
  - [x] 同时多route匹配
  - [x] 改造route数组为对象
  - [x] 修改调度方式
- [x] 支持php8，支持php新功能
- [x] 脚手架
  - [x] 项目预制
  - [ ] 编译（打包phar）
- [ ] PHPUnit
  - [x] filter\throw
  - [ ] db\sql
  - [ ] router\uri



# code
```
class app extends \nx\app{
  use \nx\log\file,
      \nx\control\mvc,
      \nx\router\ca,
      \nx\config\ini,
      \nx\db\pdo;
}
(new app)->run();
```