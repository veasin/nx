```
        ___  ___
     __ \  \/  /
    /  \ \  \ /
   /  / \ \  \
  /  /\  / \  \   
 /__/  \/__/\__\  vea, 2025
```

# ![nx](./logo.png) is **"a next-gen framework"**
  php 8.1+
  
    small,fast,emmmm...
  
    使用php语言中的trait实现在代码编写时的加载扩充，而不是运行时的扩充
  
    小框架，可以随意扩充

# resources

- [cache-redis](https://github.com/veasin/nx-cache-redis) 基于redis的cache简单封装
- [db-pdo](https://github.com/veasin/nx-db-pdo) 数据库访问，提供ar
- [filter-from](https://github.com/veasin/nx-filter-from) 过滤器，可书写混乱规则
- [log](https://github.com/veasin/nx-log) 日志，支持同时多个writer
- [log-cli](https://github.com/veasin/nx-log-cli) 在shell中输出，提供颜色封装
- [log-ws](https://github.com/veasin/nx-log-ws) 通过websocket输出日志和调试
- [model](https://github.com/veasin/nx-model) mvc的m封装
- [controller-model](https://github.com/veasin/nx-controller-model) mvc的c封装
- [network-context](https://github.com/veasin/nx-network-context) 网络请求封装
- [queue-amqp](https://github.com/veasin/nx-queue-amqp) 简单的amqp队列封装
- [router-annotation](https://github.com/veasin/nx-router-annotation) 注解生成路由

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
      \nx\db\pdo;
}
(new app)->run();
```